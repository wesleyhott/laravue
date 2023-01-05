<?php

namespace wesleyhott\Laravue\Commands;

use Illuminate\Support\Str;

class LaravueSeedCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:seed {model*} 
                                {--f|fields=} 
                                {--x|mxn} 
                                {--i|view : build a model based on view, not table}
                                {--s|schema}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação de seeder nos padrões do Laravue.';

    /**
     * Tipo de modelo que está sendo criado.
     *
     * @var string
     */
    protected $type = 'seed';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('mxn')) {
            $this->setStub('/seed-mxn');
        } else if ($this->option('view')) {
            $this->setStub('/seed-view');
        } else {
            $this->setStub('/seed');
        }

        $model = $this->option('mxn') ? $this->argument('model')[0] . $this->argument('model')[1] : $this->argument('model');
        $parsedModel = is_array($model) ? $model : trim($model);

        $date = now();

        $path = $this->getPath(model: $parsedModel, schema: $this->option('schema'));
        $this->files->put($path, $this->buildSeed($parsedModel, $this->option('schema')));

        if ($this->option('mxn')) {
            $this->info("$date - [ {$model} ] >> {$model}" . "Seeder.php");
        } else {
            $stringModel = is_array($parsedModel) ? trim($parsedModel[0]) : trim($parsedModel);
            $this->info("$date - [ $stringModel ] >> {$stringModel}Seeder.php");
        }
    }

    /**
     * Build the class with the given model.
     *
     * @param  string  $model
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildSeed($model, $schema)
    {
        $stub = $this->files->get($this->getStub());

        if ($this->option('mxn')) {
            $parsedModel =  is_array($model) ? $model[0] . $model[1] : $model;
            $class = $this->replaceClass($stub, $parsedModel);
            $table = $this->replaceTable($class, $parsedModel, $plural = false);
            return $this->replaceField($table, $model);
        }

        $parsedModel =  is_array($model) ? $model[0] : $model;
        $classStub = $this->replaceClass($stub, $parsedModel);
        $tableStub = $this->replaceTable($classStub, $parsedModel);
        $schemaStub = $this->replaceSchemaNamespace($tableStub, $schema);
        $tableSchemaStub = $this->replaceSchemaTable($schemaStub, $schema);

        return $this->replaceField($tableSchemaStub, $parsedModel);
    }

    protected function replaceField($stub, $model = null, $shema = null)
    {
        if (!$this->option('fields') && !is_array($model)) {
            return str_replace('{{ fields }}', "// insert code here.", $stub);
        }

        $fields = $this->buildFields($model);

        $returnFields = "";

        $first = true;
        foreach ($fields as $key => $value) {
            if ($first) {
                $first = false;
            } else {
                $returnFields .= PHP_EOL;
                $returnFields .= $this->tabs(2);
            }
            switch ($this->getType($value)) {
                case 'boolean':
                    $parsedValue = 'true';
                    break;
                case 'bigIncrements':
                case 'bigInteger':
                case 'integer':
                case 'mediumInteger':
                case 'tinyInteger':
                    $parsedValue = 1;
                    break;
                case 'double':
                case 'decimal':
                case 'float':
                case 'monetario':
                    $parsedValue = '1.0';
                    break;
                case 'date':
                case 'timestamp':
                    $parsedValue = '\'' . date("Y-m-d H:i:s") . '\'';
                    break;
                case 'time':
                    $parsedValue = '\'' . date("H:i:s") . '\'';
                    break;
                default:
                    $parsedValue = '\'\'';
            }
            if ($this->isFk($key)) {
                $parsedValue = 1;
            }
            $returnFields .= "// " . $this->tabs(1) . "\"$key\" => {$parsedValue},";
        }

        return str_replace('{{ fields }}', $returnFields, $stub);
    }

    public function buildFields($model)
    {
        $model1 = $model2 = "";
        $keys = array();
        $allFields = $fields = $this->getFieldsArray($this->option('fields'));

        if (is_array($model)) {
            $key1 = Str::snake($model[0]) . "_id";
            $model1 = array($key1 => 'i');
            $key2 = Str::snake($model[1]) . "_id";
            $model2 = array($key2 => 'i');

            if (!array_key_exists($key1, $fields) && !array_key_exists($key2, $fields)) {
                $allFields = $model1 + $model2 + $fields;
            } else if (!array_key_exists($key1, $fields)) {
                $allFields = $model1 + $fields;
            } else if (!array_key_exists($key2, $fields)) {
                $allFields = $model2 + $fields;
            }
        }

        return $allFields;
    }
}
