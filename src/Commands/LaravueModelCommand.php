<?php

namespace wesleyhott\Laravue\Commands;

use Illuminate\Support\Str;

class LaravueModelCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:model {model*} 
                                {--f|fields=} 
                                {--x|mxn} 
                                {--i|view : build a model based on view, not table}
                                {--s|schema= : determine a schema for model (postgres)}';

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
        if ($this->option('mxn')) {
            $this->mxnRelation($this->argument('model')[0], $this->argument('model')[1]);
            $this->mxnRelation($this->argument('model')[1], $this->argument('model')[0]);
            return;
        }

        if ($this->option('view')) {
            $this->setStub('/model-view');
        } else {
            $this->setStub('/model');
        }

        $argumentModel = $this->argument('model');
        $model = is_array($argumentModel) ? trim($argumentModel[0]) : trim($argumentModel);
        $date = now();

        $path = $this->getPath($model);
        $fields =  $this->option('fields') ? $this->getFieldsArray($this->option('fields')) : [];
        $this->files->put($path, $this->buildModel($model, $fields, $this->option('schema')));

        $this->info("$date - [ $model ] >> $model.php");
    }

    /**
     * ReplaceField is setting the $casts attirbute for fields for model.
     */
    protected function replaceField($stub, $model)
    {
        if (!$this->option('fields')) {
            return str_replace('{{ fields }}', "", $stub);
        }

        $fields = $this->getFieldsArray($this->option('fields'));
        $returnFields = '';
        $first = true;
        foreach ($fields as $key => $value) {
            switch ($this->getType($value)) {
                case 'boolean':
                case 'cpf':
                case 'cpfcnpj':
                case 'cnpj':
                    $type = 'string';
                    break;
                case 'monetario':
                    $type = 'decimal:2';
                    break;
                default:
                    $type = $this->getType($value);
            }
            if ($first) {
                $first = false;
                $returnFields .=  "* @property $type \$$key";
            } else {
                $returnFields .=  PHP_EOL . " * @property $type \$$key";
            }
        }

        return str_replace('{{ fields }}', $returnFields, $stub);
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
        $parsedWith = $modelFile;

        foreach ($fields as $key => $value) {
            if ($this->isFk($key)) {
                $fieldRelationModel = Str::studly(str_replace("_id", "", $key));
                $relationName = lcfirst($fieldRelationModel);

                $newRelation = "/**" . PHP_EOL;
                $newRelation .= $this->tabs(1) . " * Retorna $fieldRelationModel que $model contém." . PHP_EOL;
                $newRelation .= $this->tabs(1) . " */" . PHP_EOL;
                $newRelation .= $this->tabs(1) . "public function $relationName()" . PHP_EOL;
                $newRelation .= $this->tabs(1) . "{" . PHP_EOL;
                $newRelation .= $this->tabs(2) . "return \$this->belongsTo('App\Models\\$fieldRelationModel');" . PHP_EOL;
                $newRelation .= $this->tabs(1) . "}" . PHP_EOL;
                $newRelation .= PHP_EOL;
                $newRelation .= $this->tabs(1) . "// {{ laravue-insert:relationship }}";

                $newRelations = str_replace('// {{ laravue-insert:relationship }}', $newRelation, $modelFile);

                $parsedWith = $this->makeWith($newRelations, $relationName);

                $this->reverseRelation($fieldRelationModel, $model);
            }
        }

        return $parsedWith;
    }

    protected function reverseRelation($reverseModel, $model)
    {
        $currentDirectory =  getcwd();
        $path = "$currentDirectory/app/Models/$reverseModel.php";
        $modelFile = $this->files->get($path);

        $relationName = lcfirst($this->pluralize($model));

        $newRelation = "/**" . PHP_EOL;
        $newRelation .= $this->tabs(1) . " * Retorna os $relationName de $reverseModel." . PHP_EOL;
        $newRelation .= $this->tabs(1) . " */" . PHP_EOL;
        $newRelation .= $this->tabs(1) . "public function $relationName()" . PHP_EOL;
        $newRelation .= $this->tabs(1) . "{" . PHP_EOL;
        $newRelation .= $this->tabs(2) . "return \$this->hasMany('App\Models\\$model');" . PHP_EOL;
        $newRelation .= $this->tabs(1) . "}" . PHP_EOL;
        $newRelation .= PHP_EOL;
        $newRelation .= $this->tabs(1) . "// {{ laravue-insert:relationship }}";

        $parsedRelation = str_replace('// {{ laravue-insert:relationship }}', $newRelation, $modelFile);

        $parsedWith = $this->makeWith($parsedRelation, $relationName);

        $this->files->put($path, $parsedWith);
    }

    protected function mxnRelation($modelM, $modelN)
    {
        $currentDirectory =  getcwd();
        $path = "$currentDirectory/app/Models/$modelM.php";
        $modelFile = "";
        try {
            $modelFile = $this->files->get($path);
        } catch (\Exception $e) {
            $this->error("Arquivo - $currentDirectory/app/Models/$modelM.php - não encontrado.");
        }

        $relationName = lcfirst($this->pluralize($modelN));

        $newRelation = "/**" . PHP_EOL;
        $newRelation .= $this->tabs(1) . " * Retorna os $relationName de $modelM." . PHP_EOL;
        $newRelation .= $this->tabs(1) . " */" . PHP_EOL;
        $newRelation .= $this->tabs(1) . "public function $relationName()" . PHP_EOL;
        $newRelation .= $this->tabs(1) . "{" . PHP_EOL;
        $newRelation .= $this->tabs(2) . "return \$this->belongsToMany('App\Models\\$modelN');" . PHP_EOL;
        $newRelation .= $this->tabs(1) . "}" . PHP_EOL;
        $newRelation .= PHP_EOL;
        $newRelation .= $this->tabs(1) . "// {{ laravue-insert:relationship }}";

        $parsedNewRelation = str_replace('// {{ laravue-insert:relationship }}', $newRelation, $modelFile);

        $parsedWith = $this->makeWith($parsedNewRelation, $relationName);

        $this->files->put($path, $parsedWith);
    }

    protected function makeWith($parsedNewRelation, $relationName)
    {
        if (strpos($parsedNewRelation, "protected \$with = [") === false) {
            $with = "protected \$with = ['$relationName'];" . PHP_EOL;
            $with .= $this->tabs(1) . "// {{ laravue-insert:with }}";

            return str_replace('// {{ laravue-insert:with }}', $with, $parsedNewRelation);
        } else {
            $with = "protected \$with = ['$relationName', ";

            return str_replace("protected \$with = [", $with, $parsedNewRelation);
        }
    }
}
