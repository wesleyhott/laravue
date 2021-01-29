<?php

namespace Mpmg\Laravue\Commands;

use Illuminate\Support\Str;

class LaravueModelCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:model {model*} {--f|fields=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação de modelo de negócio nos padrões do Laravue.';

    /**
     * Tipo de modelo que está sendo criado.
     *
     * @var string
     */
    protected $type = 'model';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setStub('/model');
        $model = trim($this->argument('model')[0]);
        $date = now();

        $path = $this->getPath($model);
        $fields =  $this->option('fields') ? $this->getFieldsArray( $this->option('fields') ) : [];
        $this->files->put($path, $this->buildModel($model, $fields));

        $this->info("$date - [ $model ] >> $model.php");
    }

    protected function replaceField($stub, $model)
    {
        if(!$this->option('fields')){
            return str_replace( '{{ fields }}', "[]" , $stub );
        }

        $fields = $this->getFieldsArray( $this->option('fields') );

        $returnFields = '[' . PHP_EOL . $this->tabs(3);
        $first = true;
        foreach ($fields as $key => $value) {
            $title = $this->getTitle( str_replace( "_id", "", $key ) );
            if( $first ) {
                $first = false;
                $returnFields .=  "'$key' => '".$title."',". PHP_EOL;
                $returnFields .=  $this->tabs(3);
            } else {
                $returnFields .=   "'$key' => '".$title."',";
                $returnFields .=  $this->hasNext( $fields ) ? PHP_EOL . $this->tabs(3) : "";
            }  
        }
        $returnFields .= "]";

        return str_replace( '{{ fields }}', $returnFields , $stub );
    }

    /**
     * Replace the relationship for the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @return string $stub
     */
    protected function replaceRelation($modelFile, $model, $fields)
    {
        $newRelations = $modelFile;

        foreach ($fields as $key => $value) {
            if( $this->isFk( $key ) ) {
                $fieldRelationModel = Str::studly( str_replace( "_id", "", $key ) );
                $relationName = lcfirst( $fieldRelationModel );

                $newRelation = "/**" . PHP_EOL;
                $newRelation .= $this->tabs(1) . " * Retorna $fieldRelationModel que $model contém." . PHP_EOL;
                $newRelation .= $this->tabs(1) . " */" . PHP_EOL;
                $newRelation .= $this->tabs(1) . "public function $relationName()" . PHP_EOL;
                $newRelation .= $this->tabs(1) . "{" . PHP_EOL;
                $newRelation .= $this->tabs(2) . "return \$this->belongsTo('App\Models\\$fieldRelationModel');" . PHP_EOL;
                $newRelation .= $this->tabs(1) . "}" . PHP_EOL;
                $newRelation .= PHP_EOL;
                $newRelation .= $this->tabs(1) ."// {{ laravue-insert:relationship }}";

                $newRelations = str_replace( '// {{ laravue-insert:relationship }}', $newRelation, $newRelations );

                $this->reverseRelation($fieldRelationModel, $model);
            }
        }

        return $newRelations;
    }

    protected function reverseRelation($reverseModel, $model) {
        $currentDirectory =  getcwd();
        $path = "$currentDirectory/app/Models/$reverseModel.php";
        $modelFile = $this->files->get($path);
        
        $relationName = lcfirst( $this->pluralize( 2, $model ) );

        $newRelation = "/**" . PHP_EOL;
        $newRelation .= $this->tabs(1) . " * Retorna os $relationName de $reverseModel." . PHP_EOL;
        $newRelation .= $this->tabs(1) . " */" . PHP_EOL;
        $newRelation .= $this->tabs(1) . "public function $relationName()" . PHP_EOL;
        $newRelation .= $this->tabs(1) . "{" . PHP_EOL;
        $newRelation .= $this->tabs(2) . "return \$this->hasMany('App\Models\\$model');" . PHP_EOL;
        $newRelation .= $this->tabs(1) . "}" . PHP_EOL;
        $newRelation .= PHP_EOL;
        $newRelation .= $this->tabs(1) ."// {{ laravue-insert:relationship }}";

        $this->files->put($path, str_replace( '// {{ laravue-insert:relationship }}', $newRelation, $modelFile ) );
    }
}
