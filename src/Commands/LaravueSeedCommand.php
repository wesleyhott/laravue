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
                                {--s|schema=}';

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
        $parsed_model = is_array($model) ? $model : trim($model);
        $parsed_schema = empty($this->option('schema')) ? '' : Str::ucfirst($this->option('schema'));

        $date = now();

        $path = $this->getPath(model: $parsed_model, schema: $parsed_schema);
        $this->files->put($path, $this->buildSeed($parsed_model, $parsed_schema));

        if ($this->option('mxn')) {
            $this->info("{$date} - [ {$model} ] >> {$parsed_schema}{$model}" . "Seeder.php");
        } else {
            $string_model = is_array($parsed_model) ? trim($parsed_model[0]) : trim($parsed_model);
            $this->info("{$date} - [ {$string_model} ] >> {$parsed_schema}{$string_model}Seeder.php");
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
            $parsed_model =  is_array($model) ? $model[0] . $model[1] : $model;
            $class = $this->replaceClass($stub, $parsed_model);
            $table = $this->replaceTable($class, $parsed_model, $plural = false);
            return $this->replaceField($table, $model);
        }

        $parsed_model =  is_array($model) ? $model[0] : $model;
        $class_stub = $this->replaceClass($stub, $parsed_model);
        $table_stub = $this->replaceTable($class_stub, $parsed_model);
        $table_schema_stub = $this->replaceSchemaTable($table_stub, $schema);

        return $this->replaceField($table_schema_stub, $parsed_model);
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
                    $dummy_data = $this->isEnLanguage()
                        ? "'Just some example {$key} for test.'"
                        : "'Apenas {$key} para teste.'";
                    $parsedValue = $dummy_data;
            }
            if ($this->isFk($key)) {
                $parsedValue = 1;
            }
            $snaked_key = Str::snake($key);
            $returnFields .= $this->tabs(1) . "\"$snaked_key\" => {$parsedValue},";
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
