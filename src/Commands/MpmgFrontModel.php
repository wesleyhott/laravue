<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;

class MpmgFrontModel extends MpmgCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mpmg:frontmodel {model} {--f|fields=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação do frontend froms/Model.vue';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setStub('/front/forms/model');
        $model = trim($this->argument('model'));
        $date = now();

        $path = $this->getFrontFormsPath($model, "Model");
        $this->files->put($path, $this->buildModel($model));

        $this->info("$date - [ $model ] >> forms/Model.vue");
    }

    protected function replaceField($stub, $model)
    {
        $default = "<div class=\"row formSpace\">" . PHP_EOL;
        $default .= $this->tabs(5) .  "<div class=\"col-sm-12\">" . PHP_EOL;
        $default .= $this->tabs(6) .  "<!-- Insira código aqui. -->" . PHP_EOL;
        $default .= $this->tabs(5) .  "</div>" . PHP_EOL;
        $default .= $this->tabs(4) .  "</div>";

        if(!$this->option('fields')){
            $toComment = str_replace( 
                [
                    '{{ fields:model }}' , 
                    '{{ fields:model2 }}', 
                    '{{ fields:validation }}',
                    '{{ fields:computed }}',
                    '{{ fields:data-selects }}',
                    '{{ fields:data-submit }}',
                    '{{ fields:method }}',
                ], "// Insira código aqui.", $stub );
            $toBlank = str_replace( 
                [
                    '{{ fields:report }}'
                ], "", $toComment );
            
            return str_replace( '{{ fields }}', $default , $toBlank );
        }

        $fields = $this->getFieldsArray( $this->option('fields') );

        $returnFields = "";
        $first = true;
        foreach ($fields as $key => $value) {
            if( $this->isFk( $key ) ) {
                $returnFields .= $this->getSelect($key);
                continue;
            }
            switch($value) {
                case 'b':
                    $returnFields .= $this->getCheckBox($key);
                    break;
                case 'd':
                case 'dt':
                    $returnFields .= $this->getDate($key);
                    break;
                case 'i':
                case 's': 
                    $returnFields .= $this->getInput($key);
                    break;
            }
        }

        $parsedDataSelect = $this->replaceDataSelect($stub, $model);
        $parsedModel = $this->replaceFieldModel($parsedDataSelect, $model);
        $parsedModel2 = $this->replaceFieldModel2($parsedModel, $model);
        $parsedReport = $this->replaceFieldReport($parsedModel2, $model);
        $parsedValidation = $this->replaceFieldValidation($parsedReport, $model);
        $parsedComputed = $this->replaceFieldComputed($parsedValidation, $model);
        $parsedMethod = $this->replaceFieldMethod($parsedComputed, $model);
        $parsedSubmit = $this->replaceFieldSubmit($parsedMethod, $model);

        return str_replace( '{{ fields }}', $returnFields , $parsedSubmit );
    }

    protected function getInput($key) {
        $field = Str::snake($key);
        $label = $this->getTitle( $key );

        $input = "";
        $input .= "<div class=\"row formSpace\">"  . PHP_EOL;
        $input .= $this->tabs(5) . "<div class=\"col-sm-12\">"  . PHP_EOL;
        $input .= $this->tabs(6) . "<fg-input "  . PHP_EOL;
        $input .= $this->tabs(7) . ":placeholder=\"relatorio ? 'Não filtrar' :'Digite $label'\" "  . PHP_EOL;
        $input .= $this->tabs(7) . "label=\"$label\""  . PHP_EOL;
        $input .= $this->tabs(7) . "v-model=\"model.$field\">"  . PHP_EOL;
        $input .= $this->tabs(6) . "</fg-input>"  . PHP_EOL;
        $input .= $this->tabs(5) . "</div>"  . PHP_EOL;
        $input .= $this->tabs(4) . "</div>"  . PHP_EOL;
        $input .= $this->tabs(4) . "";
        
        return $input;
    }

    protected function getCheckbox($key) {
        $field = Str::snake($key);
        $label = $this->getTitle( $key );

        $input = "";
        $input .= "<div class=\"row formSpace\">"  . PHP_EOL;
        $input .= $this->tabs(5) .  "<div v-if=\"relatorio\" class=\"col-sm-12\">"  . PHP_EOL;
        $input .= $this->tabs(6) .  "<div style=\"margin-bottom: 5px; color: #9A9A9A; font-size: .8571em;\">$label</div>"  . PHP_EOL;
        $input .= $this->tabs(6) .  "<el-select  v-model=\"model.$field\">"  . PHP_EOL;
        $input .= $this->tabs(7) .  "<el-option label=\"Não filtrar\" value=\"\"></el-option>"  . PHP_EOL;
        $input .= $this->tabs(7) .  "<el-option label=\"Sim\" value=\"1\"></el-option>"  . PHP_EOL;
        $input .= $this->tabs(7) .  "<el-option label=\"Não\" value=\"0\"></el-option>"  . PHP_EOL;
        $input .= $this->tabs(6) .  "</el-select>"  . PHP_EOL;
        $input .= $this->tabs(5) .  "</div>"  . PHP_EOL;
        $input .= $this->tabs(5) .  "<div v-else class=\"col-sm-12\">"  . PHP_EOL;
        $input .= $this->tabs(6) .  "<div style=\"margin-bottom: 5px; color: #9A9A9A; font-size: .9971em;\">&nbsp;⠀⠀</div>"  . PHP_EOL;
        $input .= $this->tabs(6) .  "<p-checkbox v-model=\"$field\">$label</p-checkbox>"  . PHP_EOL;
        $input .= $this->tabs(5) .  "</div>"  . PHP_EOL;
        $input .= $this->tabs(4) .  "</div>"  . PHP_EOL;
        $input .= $this->tabs(4) .  "";
        
        return $input;
    }

    protected function getDate( $key ) {
        $field = Str::snake( $key );
        $label = $this->getTitle( $key );

        $input = "";
        $input .= "<div class=\"row formSpace\">"  . PHP_EOL;
        $input .= $this->tabs(5) .  "<div class=\"col-sm-12\">"  . PHP_EOL;
        $input .= $this->tabs(6) .  "<div style=\"margin-bottom: 5px; color: #9A9A9A; font-size: .8571em;\">$label</div>"  . PHP_EOL;
        $input .= $this->tabs(6) .  "<el-date-picker"  . PHP_EOL;
        $input .= $this->tabs(7) .  "v-model=\"model.$field\""  . PHP_EOL;
        $input .= $this->tabs(7) .  "type=\"date\""  . PHP_EOL;
        $input .= $this->tabs(7) .  "format=\"dd/MM/yyyy\""  . PHP_EOL;
        $input .= $this->tabs(7) .  "value-format=\"yyyy-MM-dd\""  . PHP_EOL;
        $input .= $this->tabs(7) .  ":placeholder=\"relatorio ? 'Não filtrar' : 'Selecione data'\">"  . PHP_EOL;
        $input .= $this->tabs(6) .  "</el-date-picker>"  . PHP_EOL;
        $input .= $this->tabs(5) .  "</div>"  . PHP_EOL;
        $input .= $this->tabs(4) .  "</div>"  . PHP_EOL;
        $input .= $this->tabs(4) .  "";
        
        return $input;
    }

    protected function getSelect($key) {
        $field = Str::snake($key);
        $pluralField = lcfirst( Str::studly( $this->pluralize( 2, str_replace("_id", "", $field) ) ) );
        $label = $this->getTitle( $key );
        $label = str_replace(" Id", "", $label);

        $input = "";
        $input .= "<div class=\"row formSpace\">  <!-- TODO: [build] Change label field -->"  . PHP_EOL;
        $input .= $this->tabs(5) .  "<div class=\"col-sm-12\">"  . PHP_EOL;
        $input .= $this->tabs(6) .  "<div style=\"margin-bottom: 5px; color: #9A9A9A; font-size: .8571em;\">$label</div>"  . PHP_EOL;
        $input .= $this->tabs(6) .  "<el-select"  . PHP_EOL;
        $input .= $this->tabs(7) .  "filterable"  . PHP_EOL;
        $input .= $this->tabs(7) .  "class=\"baseSelect\""  . PHP_EOL;
        $input .= $this->tabs(7) .  "size=\"large\"" . PHP_EOL;
        $input .= $this->tabs(7) .  ":placeholder=\"relatorio ? 'Não filtrar' : '$label'\"" . PHP_EOL;
        $input .= $this->tabs(7) .  "v-model=\"model.$key\" >" . PHP_EOL;
        $input .= $this->tabs(8) .  "<el-option v-if=\"relatorio\" label=\"Não filtrar\" value=\"\"></el-option>"  . PHP_EOL;
        $input .= $this->tabs(8) .  "<el-option"  . PHP_EOL;
        $input .= $this->tabs(9) .  "v-for=\"item in selects.$pluralField\""  . PHP_EOL;
        $input .= $this->tabs(9) .  "class=\"select-danger\""  . PHP_EOL;
        $input .= $this->tabs(9) .  ":value=\"item.id\""  . PHP_EOL;
        $input .= $this->tabs(9) .  ":label=\"item.id\""  . PHP_EOL;
        $input .= $this->tabs(9) .  ":key=\"item.id\" >"  . PHP_EOL;
        $input .= $this->tabs(8) .  "</el-option>"  . PHP_EOL;
        $input .= $this->tabs(6) .  "</el-select>"  . PHP_EOL;
        $input .= $this->tabs(5) .  "</div>"  . PHP_EOL;
        $input .= $this->tabs(4) .  "</div>"  . PHP_EOL;
        $input .= $this->tabs(4) .  "";
        
        return $input;
    }

    protected function replaceDataSelect($stub, $model)
    {
        $fields = $this->getFieldsArray( $this->option('fields') );
        $fks = array_filter($fields, function ( $k ) {
            return $this->isFk( $k );
        }, ARRAY_FILTER_USE_KEY);

        $return = "";
        foreach ($fks as $key => $value) {
            $selecField = lcfirst( Str::studly( $this->pluralize( 2, str_replace( "_id", "",  $key ) ) ) );
            $return .= PHP_EOL;
            $return .= $this->tabs(4) . "$selecField: [],";
        }

        return str_replace( '{{ fields:data-selects }}', $return , $stub );
    }

    protected function replaceFieldModel($stub, $model)
    {
        $fields = $this->getFieldsArray( $this->option('fields') );

        $return = "";
        $index = 0;
        $size = count($fields);
        foreach ($fields as $key => $value) {
            $index++;
            switch($value) {
                case 'b':
                    $return .= "$key: this.relatorio ? '' : false,";
                    $return .= $this->ending($index, $size);
                break;
                default:
                    $return .= "$key: '',";
                    $return .= $this->ending($index, $size);
            }
        }

        return str_replace( '{{ fields:model }}', $return , $stub );
    }

    protected function replaceFieldModel2($stub, $model)
    {
        $fields = $this->getFieldsArray( $this->option('fields') );

        $return = "";
        $index = 0;
        $size = count($fields);
        foreach ($fields as $key => $value) {
            $index++;
            switch($value) {
                case 'b':
                    $return .= "$key: this.relatorio ? '' : false,";
                    $return .= $this->ending2($index, $size);
                break;
                default:
                    $return .= "$key: '',";
                    $return .= $this->ending2($index, $size);
            }
        }

        return str_replace( '{{ fields:model2 }}', $return , $stub );
    }

    protected function ending($index, $size){
        return $index == $size ? "" : PHP_EOL . $this->tabs(4);
    }

    protected function ending2($index, $size){
        return $index == $size ? "" : PHP_EOL . $this->tabs(9);
    }

    protected function ending3($index, $size){
        return $index == $size ? "" : PHP_EOL;
    }

    protected function replaceFieldReport($stub, $model)
    {
        $fields = $this->getFieldsArray( $this->option('fields') );

        $return = "";
        $first = true;
        $index = 0;
        $size = count( $fields );
        foreach ($fields as $key => $value) {
            $index++;
            if( $first ){
                $first = false;
                $return .= "?";
            } else {
                $return .= $this->tabs(7) . "&";
            }
            $return .= "$key=\${this.model.$key}" . $this->ending3( $index, $size );
        }

        return str_replace( '{{ fields:report }}', $return , $stub );
    }

    protected function replaceFieldValidation($stub, $model)
    {
        $fields = $this->getFieldsArray( $this->option('fields') );

        $return = "";
        $index = 0;
        $size = count($fields);
        $first = true;
        foreach ($fields as $key => $value) {
            $index++;
            $first ? $first = false : $return .= $this->tabs(4);
            switch($value) {
                case 's':
                    $return .= "$key: {";
                    $return .= " required: true,";
                    $return .= " min: 3";
                    $return .= " },";
                    $return .= $this->ending3($index, $size);
                break;
                default:
                    $return .= "$key: {";
                    $return .= " required: true";
                    $return .= " },";
                    $return .= $this->ending3($index, $size);
            }
        }

        return str_replace( '{{ fields:validation }}', $return , $stub );
    }

    protected function replaceFieldComputed($stub, $model)
    {
        $fields = $this->getFieldsArray( $this->option('fields') );

        $return = PHP_EOL;
        foreach ($fields as $key => $value) {
            switch($value) {
                case 'b':
                    $return .= $this->tabs(2) . "$key: {" . PHP_EOL;
                    $return .= $this->tabs(3) . "get: function() {" . PHP_EOL;
                    $return .= $this->tabs(4) . "return this.model.$key >= 1 ? true : false;" . PHP_EOL;
                    $return .= $this->tabs(3) . "}," . PHP_EOL;
                    $return .= $this->tabs(3) . "set: function(newValue) {" . PHP_EOL;
                    $return .= $this->tabs(4) . "this.model.$key = newValue ? 1 : 0;" . PHP_EOL;
                    $return .= $this->tabs(3) . "}," . PHP_EOL;
                    $return .= $this->tabs(2) . "},";
                break;
            }
        }

        return str_replace( '{{ fields:computed }}', $return , $stub );
    }

    protected function replaceFieldMethod($stub, $model)
    {
        $fields = $this->getFieldsArray( $this->option('fields') );
        $fks = array_filter($fields, function ( $k ) {
            return $this->isFk( $k );
        }, ARRAY_FILTER_USE_KEY);
        
        $return = $this->buildLoadSelects( $fks );
        $return .= $this->buildSelects( $fks );

        return str_replace( '{{ fields:method }}', $return , $stub );
    }

    protected function buildLoadSelects( $fks )
    {
        if( count( $fks ) == 0 ) {
            $methodName = "loadModel";
        } else {
            $methodName = "load" . Str::studly( $this->pluralize( 2, str_replace("_id", "", array_key_first( $fks ) ) ) );
        }

        $loadSelect = "loadSelects() {" . PHP_EOL;
        $loadSelect .= $this->tabs(3) . "this.setLoading(true, \"carregando\")" . PHP_EOL;
        $loadSelect .= $this->tabs(3) . "this.$methodName() // cascade calls" . PHP_EOL;
        $loadSelect .= $this->tabs(2) . "}," . PHP_EOL;

        return $loadSelect;
    }

    protected function buildSelects( $fks ) 
    {
        $size = count( $fks );
        if( $size == 0 ) {
            return "";
        } 

        $selects = $this->tabs(2) ."{{ methodname }}() {" . PHP_EOL;
        $selects .= $this->tabs(3) . "this.setLoading(true, \"{{ title }}\")" . PHP_EOL;
        $selects .= $this->tabs(3) . "this.\$http" . PHP_EOL;
        $selects .= $this->tabs(4) . ".get(`{{ route }}?per_page=-1`)" . PHP_EOL;
        $selects .= $this->tabs(4) . ".then(response => {" . PHP_EOL;
        $selects .= $this->tabs(5) . "this.setLoading(false)" . PHP_EOL;
        $selects .= $this->tabs(5) . "this.selects.{{ selectField }} = response.data.data.data" . PHP_EOL;
        $selects .= $this->tabs(5) . "this.{{ nextMethod }}()" . PHP_EOL;
        $selects .= $this->tabs(4) . "})" . PHP_EOL;
        $selects .= $this->tabs(4) . ".catch(e => {" . PHP_EOL;
        $selects .= $this->tabs(5) . "this.setLoading(false)" . PHP_EOL;
        $selects .= $this->tabs(5) . "mpmgNotify.failure(this, e)" . PHP_EOL;
        $selects .= $this->tabs(4) . "})" . PHP_EOL;
        $selects .= $this->tabs(2) . "}, {{ last }}";

        $return = "";
        $i = 0;
        foreach ($fks as $key => $value) {
            $methodName = "load" . Str::studly( $this->pluralize( 2, str_replace("_id", "", $key ) ) );
            $title = $this->getTitle( str_replace( "_id", "",  $key ) );
            $route = $this->pluralize( 2, str_replace( "_", "",  str_replace( "_id", "",  $key ) ) );
            $selectField = lcfirst( Str::studly( $this->pluralize( 2, str_replace( "_id", "",  $key ) ) ) );
            $nextMethod = $this->getNext( $fks, $i++, $size );
            if( $nextMethod != 'loadModel') {
                $nextMethod = 'load' . Str::studly( $this->pluralize( 2, str_replace( "_id", "", $nextMethod ) ) );
            }
            
            $methodname = str_replace( '{{ methodname }}', $methodName , $selects );
            $title = str_replace( '{{ title }}', $title , $methodname );
            $route = str_replace( '{{ route }}', $route , $title );
            $selectField = str_replace( '{{ selectField }}', $selectField , $route );
            $nextMethod = str_replace( '{{ nextMethod }}', $nextMethod , $selectField );
            $last = $i == $size ? "" : PHP_EOL;
            $lastSelect = str_replace( '{{ last }}', $last , $nextMethod );

            $return .= $lastSelect;
        }
        
        return $return;
    }

    protected function replaceFieldSubmit($stub, $model)
    {
        $fields = $this->getFieldsArray( $this->option('fields') );

        $return = "";
        foreach ($fields as $key => $value) {
            $selecField = lcfirst( Str::studly( $this->pluralize( 2, str_replace( "_id", "",  $key ) ) ) );
            $return .= PHP_EOL;
            switch($value) {
                case 'i':
                case 'bi':
                    $return .= $this->tabs(7) . "$key: parseInt(this.model.$key),";
                break;
                default:
                    $return .= $this->tabs(7) . "$key: this.model.$key,";
            }
        }

        return str_replace( '{{ fields:data-submit }}', $return , $stub );
    }

    function getNext($array, $i, $size) {
        $values = array_keys($array);
        if( ($i + 1) == $size) {
            return 'loadModel';
        } 
        return $values[$i + 1];
    }
}
