<?php

namespace wesleyhott\Laravue\Commands;

use Illuminate\Support\Str;

class LaravueRequestCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:request {model*} 
                                {--f|fields=} 
                                {--x|mxn} 
                                {--i|view : build a model based on view, not table}
                                {--s|schema= : determine a schema for model (postgres)}
                                {--t|store : makes a store request}
                                {--u|update : makes a update request}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Makes a Request in Laravue standart.';

    /**
     * Command type for path generation.
     *
     * @var string
     */
    protected $type = 'request';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('mxn')) {
            return $this->info('MxN not implemented at form request');
        } else if ($this->option('view')) {
            return $this->info('view not implemented at form request');
        } else {
            $this->setStub('/request');
        }

        $model = $this->option('mxn') ? $this->argument('model')[0] . $this->argument('model')[1] : $this->argument('model');
        $parsedModel = is_array($model) ? $model : trim($model);

        $date = now();

        $path = $this->getPath(model: $parsedModel, schema: $this->option('schema'));
        $this->files->put($path, $this->buildRequest($parsedModel, $this->option('schema')));

        if ($this->option('mxn')) {
            $this->info("$date - [ ${model} ] >> ${model}" . "Seeder.php");
        } else {
            $stringModel = is_array($parsedModel) ? trim($parsedModel[0]) : trim($parsedModel);
            $this->info("$date - [ $stringModel ] >> ${stringModel}Seeder.php");
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
    protected function buildRequest($model, $schema)
    {
        $stub = $this->files->get($this->getStub());

        // if ($this->option('mxn')) {
        //     $parsedModel =  is_array($model) ? $model[0] . $model[1] : $model;
        //     $class = $this->replaceClass($stub, $parsedModel);
        //     $table = $this->replaceTable($class, $parsedModel, $plural = false);
        //     return $this->replaceField($table, $model);
        // }

        $parsedModel =  is_array($model) ? $model[0] : $model;
        $classStub = $this->replaceClass($stub, $parsedModel);
        $schemaStub = $this->replaceSchemaNamespace($classStub, $schema);

        return $this->replaceField($schemaStub, $parsedModel);
    }

    protected function replaceField($stub, $model = null, $shema = null)
    {
        if (!$this->option('fields') && !is_array($model)) {
            return str_replace(['{{ rules }}', '{{ messages }}'], '', $stub);
        }

        $fields = $this->buildFields($model);

        $rulesStub = $this->replaceRules($stub, $model, $fields);
        $messagesStub = $this->replaceMessages($rulesStub, $model, $fields);

        return $messagesStub;
    }

    public function replaceRules(string $stub, string $model, array $fields): string
    {
        $rules = '';
        $firstUniqueArray = true;
        foreach ($fields as $key => $value) {
            $type = $this->getType($value);
            // Nullable
            $required = $this->hasNullable($value) ? '|nullable' : '|required';
            // String Size
            $max_size = '';
            if ($type == 'string') {
                $isNumbers = $this->hasNumber($value);
                if ($isNumbers !== false) {
                    $max_size = "|max:" . $isNumbers[0];
                }
            }
            // Unsigned Integer
            $unsigned = '';
            if ($type == 'integer') {
                $isUnsigned = $this->isUnsigned($value);
                if ($isUnsigned !== false) {
                    $unsigned = "|min:0";
                }
            }
            // Monetary Value
            if ($type == 'monetario' || $type == 'monetary') {
                $type = 'decimal:2';
            }
            // Small integer
            if ($type == 'bigInteger' || $type == 'smallInteger' || $type == 'tinyInteger') {
                $type = 'integer';
                $isUnsigned = $this->isUnsigned($value);
                if ($isUnsigned !== false) {
                    $unsigned = "|min:0";
                }
            }
            // Char
            if ($type == 'char') {
                $type = 'string';
            }
            // Datetime
            if ($type == 'dateTime') {
                $type = 'date';
            }
            // Decimal
            if ($type == 'decimal') {
                $type = 'numeric';
                $max_size = '|max:' . $this->decimalMaxSize($value);
                $isUnsigned = $this->isUnsigned($value);
                if ($isUnsigned !== false) {
                    $unsigned = "|min:0";
                }
            }
            // Binary
            if ($type == 'binary') {
                $type = 'string';
            }
            // Unique 
            $use_soft_deletes = config('laravue.use_soft_deletes');
            $connection_name = config('laravue.form_request_connection');
            $conn = $connection_name == '' ? '' : "{$connection_name}.";
            $schema = empty($this->option('schema')) ? '' : strtolower($this->option('schema')) . ".";

            $soft_delete = $use_soft_deletes ? ',deleted_at,NULL' : '';
            $isUnique = $this->isUnique($value);
            $snaked_model = Str::snake($model);
            $table = $this->pluralize($snaked_model);
            $id_value = $this->option('store') ? 'NULL' : "{\$this->{$snaked_model}}";
            $unique = $isUnique ? "|unique:{$conn}{$schema}{$table},{$key},{$id_value},id{$soft_delete}" : '';
            // Unique array 
            $uniqueArray = '';
            $isUniqueArray = $this->isUniqueArray($value);
            if ($firstUniqueArray && $isUniqueArray) {
                $fieldsUnique = array_filter($fields, fn ($unique_key) => str_contains($unique_key, 'u*'));
                foreach ($fieldsUnique as $k => $v) {
                    //point to end of the array
                    end($fieldsUnique);
                    $lastElementKey = key($fieldsUnique);
                    $double_quote = $k == $lastElementKey ? '' : '"';
                    $isUniqueInternalArray = $this->isUniqueArray($v);
                    if ($firstUniqueArray && $isUniqueInternalArray) {
                        $firstUniqueArray = false;
                        $uniqueArray .= "|unique:{$conn}{$schema}{$table},{$k},\"" . PHP_EOL;
                        $uniqueArray .= $this->tabs(4) . ". \"{$id_value},id\"";
                        continue;
                    }
                    if ($isUniqueInternalArray) {
                        $uniqueArray .= PHP_EOL . $this->tabs(4) . ". \",$k,{\$this->$k}{$double_quote}";
                    }
                }
                if ($use_soft_deletes) {
                    $uniqueArray .= "\"" . PHP_EOL . $this->tabs(4) . ". \"{$soft_delete}";
                }
            }

            $rules .= PHP_EOL . $this->tabs(3) . "'{$key}' => \"{$type}{$required}{$unsigned}{$max_size}{$unique}{$uniqueArray}\",";
        }
        $rules .= PHP_EOL . $this->tabs(2);
        return str_replace('{{ rules }}', $rules, $stub);
    }

    public function replaceMessages(string $stub, string $model, array $fields): string
    {
        $messages = '';
        $language = config('laravue.language');
        $firstUniqueArray = true;
        foreach ($fields as $key => $value) {
            // Foreing Key
            if ($this->isFk($key)) {
            }
            $type = $this->getType($value);
            // Required
            if (!$this->hasNullable($value) && !$this->isFk($key)) {
                $text = $language == 'en' ? 'cannot be empty' : 'é obrigatório';
                $parsedModel = $this->getTitle($model);
                $label = $this->getTitle($key);
                $messages .= PHP_EOL . $this->tabs(3) . "'{$key}.required' => '{$parsedModel} {$label} {$text}.',";
            }
            // String Size
            if ($type == 'string') {
                $isNumbers = $this->hasNumber($value);
                if ($isNumbers !== false) {
                    $text = $language == 'en'
                        ? "cannot have more than {$isNumbers[0]} characters"
                        : "não pode ter mais que {$isNumbers[0]} caracteres";
                    $messages .= PHP_EOL . $this->tabs(3) . "'{$key}.max' => '{$parsedModel} {$label} {$text}.',";
                }
            }
            // Unsigned
            $isUnsigned = $this->isUnsigned($value);
            if ($isUnsigned !== false) {
                $text = $language == 'en'
                    ? "must be greater than zero"
                    : "deve ser maior que zero";
                $messages .= PHP_EOL . $this->tabs(3) . "'{$key}.min' => '{$parsedModel} {$label} {$text}.',";
            }
            // Unique
            $isUnique = $this->isUnique($value);
            if ($isUnique) {
                $text = $language == 'en'
                    ? "already exists"
                    : "já existe";
                $messages .= PHP_EOL . $this->tabs(3) . "'{$key}.unique' => '{$parsedModel} {$label} {$text}.',";
            }
            // Unique array 
            $isUniqueArray = $this->isUniqueArray($value);
            if ($firstUniqueArray && $isUniqueArray) {
                $fieldsUnique = array_filter($fields, fn ($unique_key) => str_contains($unique_key, 'u*'));
                $unique_fields = '';
                foreach ($fieldsUnique as $k => $v) {
                    $isUniqueInternalArray = $this->isUniqueArray($v);
                    if ($isUniqueInternalArray) {
                        if ($firstUniqueArray) {
                            $firstUniqueArray = false;
                            continue;
                        }
                        $and = $unique_fields == '' ? '' : ' and ';
                        if ($and == ' and ' && $language != 'en') {
                            $and = ' e ';
                        }
                        $unique_fields .= "{$and}{$this->getTitle($k)}";
                    }
                }
                $text = $language == 'en'
                    ? "with {$unique_fields} already exists"
                    : "e {$unique_fields} já está cadastrado";
                $messages .= PHP_EOL . $this->tabs(3) . "'{$key}.unique' => '{$parsedModel} {$label} {$text}.',";
            }
        }
        $messages .= PHP_EOL . $this->tabs(2);
        return str_replace('{{ messages }}', $messages, $stub);;
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
