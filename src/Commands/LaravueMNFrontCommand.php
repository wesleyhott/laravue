<?php

namespace Mpmg\Laravue\Commands;

use Illuminate\Support\Str;

class LaravueMNFrontCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:mxnfront {model* : The model to be builded} 
        {--k|keys= : custom foreing keys that belongs to relationship}
        {--p|pivots= : Feilds that belongs to relationship}
        {--o|outdocker : indicates the origin of command}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria os aruquivos para um modelo';

    /**
     * The model fields.
     *
     * @var string
     */
    protected $fields = [];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $fields = "";
        if( $this->option('keys') !== null ) {
            $fields = $this->option('keys');
        }
        
        $virgula = $fields == "" ? "" : ",";

        if( $this->option('pivots') !== null ) {
            $fields .= $virgula . $this->option('pivots');
        }

        $this->fields = $this->getFieldsArray( $fields );

        $this->createModel();
        $this->createModal();
    }

    /**
     * Create a model file for the model.
     *
     * @return void
     */
    protected function createModel()
    {
        $date = now();
        $projectName_m = $projectName_n = $this->projectName;
        $model_m = trim($this->argument('model')[0]);
        $model_n = trim($this->argument('model')[1]);

        if( $model_m == "Monitor" || $model_m == "Permission" || $model_m == "Role" || $model_m == "Task" || $model_m == "User" ) {
            $projectName_m = "ProjetoBase";
        }

        if( $model_n == "Monitor" || $model_n == "Permission" || $model_n == "Role" || $model_n == "Task" || $model_n == "User" ) {
            $projectName_n = "ProjetoBase";
        }

        $path_m = $this->fileBuildPath( 'frontend', 'src', 'components', $projectName_m, 'Views', 'Pages', $model_m, 'forms', 'Model.vue' );
        $path_n = $this->fileBuildPath( 'frontend', 'src', 'components', $projectName_n, 'Views', 'Pages', $model_n, 'forms', 'Model.vue' );
        
        $stub_m = $this->files->get( $path_m );
        $this->files->put( $path_m, $this->buildMnModel( $model_n, $stub_m ) );
        $this->info("$date - [ $model_m ] >> forms/Model.vue");
        
        $stub_n = $this->files->get( $path_n );
        $this->files->put( $path_n, $this->buildMnModel( $model_m, $stub_n ) );
        $this->info("$date - [ $model_n ] >> forms/Model.vue");
    }

    protected function buildMnModel( $model, $path ) {
        $parsedField = $this->getField( $model, $path );
        $parsedDataSelect = $this->getDataSelect( $model, $parsedField );
        $parsedDataModel = $this->getDataModel( $model, $parsedDataSelect );
        $parsedSubmit = $this->getSubmit( $model, $parsedDataModel );
        $parsedLoadModelMethod = $this->getLoadModelMethod( $model, $parsedSubmit );
        $parsedLoadModelResponse = $this->getLoadModelResponse( $model, $parsedLoadModelMethod );
        $parsedMethods = $this->getMethods( $model, $parsedLoadModelResponse );

        return $parsedMethods;
    }

    protected function getField( $model, $path ) {
        $title = $this->getTitle($model, true ); // true: plural.
        $plural = $this->pluralize( 2, $model );
        $lowcasePlural = lcfirst( $plural );
        $lowcase = lcfirst( $model );

        $field = "<div class=\"row formSpace\">" . PHP_EOL;
        $field .= $this->tabs(8) ."<div class=\"col-sm-12 \">" . PHP_EOL;
        $field .= $this->tabs(9) . "<ValidationProvider name=\"$model\" rules=\"\" v-slot=\"{ errors }\">" . PHP_EOL;
        $field .= $this->tabs(10) . "<div style=\"margin-bottom: 5px; color: #9A9A9A; font-size: .8571em;\">$title</div>" . PHP_EOL;
        $field .= $this->tabs(10) . "<el-select" . PHP_EOL;
        $field .= $this->tabs(11) . "multiple" . PHP_EOL;
        $field .= $this->tabs(11) . "filterable" . PHP_EOL;
        $field .= $this->tabs(11) . "class=\"baseSelect\"" . PHP_EOL;
        $field .= $this->tabs(11) . "size=\"large\"" . PHP_EOL;
        $field .= $this->tabs(11) . "style=\"width: 100%;\"" . PHP_EOL;
        $field .= $this->tabs(11) . ":placeholder=\"relatorio ? 'Não filtrar' : 'User'\"" . PHP_EOL;
        $field .= $this->tabs(11) . "v-model=\"model.${lowcase}_ids\" >" . PHP_EOL;
        $field .= $this->tabs(12) . "<el-option v-if=\"relatorio\" label=\"Não filtrar\" value=\"\"></el-option>" . PHP_EOL;
        $field .= $this->tabs(12) . "<el-option" . PHP_EOL;
        $field .= $this->tabs(13) . "v-for=\"item in selects.$lowcasePlural\"" . PHP_EOL;
        $field .= $this->tabs(13) . "class=\"select-danger\"" . PHP_EOL;
        $field .= $this->tabs(13) . ":value=\"item.id\"" . PHP_EOL;
        $field .= $this->tabs(13) . ":label=\"item.id\"" . PHP_EOL;
        $field .= $this->tabs(13) . ":key=\"item.id\" >" . PHP_EOL;
        $field .= $this->tabs(12) . "</el-option>" . PHP_EOL;
        $field .= $this->tabs(10) . "</el-select>" . PHP_EOL;
        $field .= $this->tabs(10) . "<div v-if=\"!relatorio\" class=\"text-danger\" style=\"font-size: .8271em; margin-top: 4px;\">{{ errors[0] }}</div>" . PHP_EOL;
        $field .= $this->tabs(9) . "</ValidationProvider>" . PHP_EOL;
        $field .= $this->tabs(8) . "</div>" . PHP_EOL;
        $field .= $this->tabs(7) . "</div>" . PHP_EOL;
        $field .= $this->tabs(7) . "<!-- {{ laravue-insert:field }} -->";

        return str_replace( '<!-- {{ laravue-insert:field }} -->', $field, $path  );
    }

    protected function getDataSelect( $model, $path ) {
        $item = $this->pluralize(2, lcfirst( $model ) );

        $dataSelect = "${item}: []," . PHP_EOL;
        $dataSelect .= $this->tabs(4) . "// {{ laravue-insert:dataSelects }}";

        return str_replace( '// {{ laravue-insert:dataSelects }}', $dataSelect, $path  );
    }

    protected function getDataModel( $model, $path ) {
        $item = lcfirst( $model );

        $dataModel = "${item}_ids: []," . PHP_EOL;
        $dataModel .= $this->tabs(4) . "// {{ laravue-insert:dataModel }}";

        return str_replace( '// {{ laravue-insert:dataModel }}', $dataModel, $path  );
    }

    protected function getSubmit( $model, $path ) {
        $item = lcfirst( $model );

        $submit = "${item}_ids: this.model.${item}_ids, " . PHP_EOL;
        $submit .= $this->tabs(6) . "// {{ laravue-insert:submit }}";

        return str_replace( '// {{ laravue-insert:submit }}', $submit, $path  );
    }

    protected function getLoadModelMethod( $model, $path ) {
        $item = $this->pluralize( 2, $model );

        $method = "this.load${item}()" . PHP_EOL;
        $method .= $this->tabs(3) . "// {{ laravue-insert:loadModelMethod }}";

        return str_replace( '// {{ laravue-insert:loadModelMethod }}', $method, $path  );
    }

    protected function getLoadModelResponse( $model, $path ) {
        $item = lcfirst( $model );
        $items = $this->pluralize( 2, $item );

        $response = "this.model.${item}_ids = []" . PHP_EOL;
        $response .= $this->tabs(6) . "this.model.${items}.forEach(element => {" . PHP_EOL;
        $response .= $this->tabs(7) . "this.model.${items}_ids.push( element.id )" . PHP_EOL;
        $response .= $this->tabs(6) . "})" . PHP_EOL;
        $response .= $this->tabs(6) . "// {{ laravue-insert:loadModelResponse }}";

        return str_replace( '// {{ laravue-insert:loadModelResponse }}', $response, $path  );
    }

    protected function getMethods( $model, $path ) {
        $item = $this->pluralize( 2, $model );
        $itemLcFrist = lcfirst( $item );
        $itemLc = strtolower( $item );

        $method = "load${item}() {" . PHP_EOL;
        $method .= $this->tabs(3) . "return this.\$http" . PHP_EOL;
        $method .= $this->tabs(4) . ".get('${itemLc}?per_page=-1')" . PHP_EOL;
        $method .= $this->tabs(4) . ".then(response => {" . PHP_EOL;
        $method .= $this->tabs(5) . "this.selects.${itemLcFrist} = response.data.data.data" . PHP_EOL;
        $method .= $this->tabs(3) . "})" . PHP_EOL;
        $method .= $this->tabs(3) . ".catch(e => {" . PHP_EOL;
        $method .= $this->tabs(4) . "laravueNotify.failure(this, e)" . PHP_EOL;
        $method .= $this->tabs(3) . "})" . PHP_EOL;
        $method .= $this->tabs(2) . "}," . PHP_EOL;
        $method .= $this->tabs(2) . "// {{ laravue-insert:methods }}";

        return str_replace( '// {{ laravue-insert:methods }}', $method, $path  );
    }

    /**
     * Create a model file for the model.
     *
     * @return void
     */
    protected function createModal()
    {
        $date = now();
        $projectName_m = $projectName_n = $this->projectName;
        $model_m = trim($this->argument('model')[0]);
        $model_n = trim($this->argument('model')[1]);

        if( $model_m == "Monitor" || $model_m == "Permission" || $model_m == "Role" || $model_m == "Task" || $model_m == "User" ) {
            $projectName_m = "ProjetoBase";
        }

        if( $model_n == "Monitor" || $model_n == "Permission" || $model_n == "Role" || $model_n == "Task" || $model_n == "User" ) {
            $projectName_n = "ProjetoBase";
        }

        $path_m = $this->fileBuildPath( 'frontend', 'src', 'components', $projectName_m, 'Views', 'Pages', $model_m, 'forms', 'Modal.vue' );
        $path_n = $this->fileBuildPath( 'frontend', 'src', 'components', $projectName_n, 'Views', 'Pages', $model_n, 'forms', 'Modal.vue' );
        
        $stub_m = $this->files->get( $path_m );
        $this->files->put( $path_m, $this->buildMnModal( $model_n, $stub_m ) );
        $this->info("$date - [ $model_m ] >> forms/Modal.vue");
        
        $stub_n = $this->files->get( $path_n );
        $this->files->put( $path_n, $this->buildMnModal( $model_m, $stub_n ) );
        $this->info("$date - [ $model_n ] >> forms/Modal.vue");
    }

    protected function buildMnModal( $model, $path ) {
        $parsedModalField = $this->getModalField( $model, $path );

        return $parsedModalField;
    }

    public function getModalField( $model, $path ) {
        $label = $this->getLabel( $this->fields );
        $title = $this->getTitle( $model, true);
        $plural = $this->pluralize( 2, $model );
        $lowerSingular = lcfirst( $model );
        $lowerPlural = lcfirst( $plural );

        $modalField = "<div v-if=\"model.$lowerPlural.length > 0\" class=\"row\">" . PHP_EOL;
        $modalField .= $this->tabs(2) . "<div class=\"col-sm-12\">" . PHP_EOL;
        $modalField .= $this->tabs(3) . "<p>" . PHP_EOL;
        $modalField .= $this->tabs(4) . "<b>$title</b>" . PHP_EOL;
        $modalField .= $this->tabs(4) . "<br/>" . PHP_EOL;
        $modalField .= $this->tabs(4) . "<ul>" . PHP_EOL;
        $modalField .= $this->tabs(5) . "<li v-for=\"($lowerSingular, key) in model.$lowerPlural\" :key=\"key\">{{ $lowerSingular.$label }}</li>" . PHP_EOL;
        $modalField .= $this->tabs(4) . "</ul>" . PHP_EOL;
        $modalField .= $this->tabs(3) . "</p>" . PHP_EOL;
        $modalField .= $this->tabs(2) . "</div>" . PHP_EOL;
        $modalField .= $this->tabs(1) . "</div>" . PHP_EOL;
        $modalField .= $this->tabs(1) . "<!-- {{ laravue-insert:field }} -->";

        return str_replace( '<!-- {{ laravue-insert:field }} -->', $modalField, $path  );
    }
}
