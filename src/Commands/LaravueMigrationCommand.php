<?php

namespace wesleyhott\Laravue\Commands;

use Illuminate\Support\Str;

class LaravueMigrationCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:migration {model*} 
                                {--f|fields=} 
                                {--x|mxn} 
                                {--i|view : build a model based on view, not table}
                                {--s|schema= : determine a schema for model (postgres)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build migration in Laravue standart.';

    /**
     * Model type that is been built.
     *
     * @var string
     */
    protected $type = 'migration';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('mxn')) {
            $this->setStub('/migration-mxn');
        } else if ($this->option('view')) {
            $this->setStub('/migration-view');
        } else {
            $this->setStub('/migration');
        }

        $model = $this->setViewName($this->argument('model'));
        $schema = $this->setViewName($this->option('schema'));
        $path = $this->getPath(model: $model, schema: $schema);
        $this->files->put($path, $this->buildMigration($model, $schema));

        $this->infoLog($model, $schema);
    }

    /**
     * Build the class with the given model.
     *
     * @param  string  $model
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildMigration($model, $schema)
    {
        $stub = $this->files->get($this->getStub());

        if (is_array($model) && count($model) > 1) { // mxn
            $class = $this->replaceClass($stub, $model[0] . $model[1]);
            $table = $this->replaceTable($class, $model[0] . $model[1], $plural = false);
            $parsedSchemaTable = $this->replaceSchemaTable($table, $schema);
            $parsedSchemaClass = $this->replaceSchemaClass($parsedSchemaTable, $schema);
            $parsedSoftDeletes = $this->replaceSoftDeletes($parsedSchemaClass);

            return $this->replaceField($parsedSoftDeletes, $model, $schema);
        }

        $parsedModel =  is_array($model) ? $model[0] : $model;
        $class = $this->replaceClass($stub, $parsedModel);
        $table = $this->replaceTable($class, $parsedModel);
        $parsedSchemaTable = $this->replaceSchemaTable($table, $schema);
        $parsedSchemaClass = $this->replaceSchemaClass($parsedSchemaTable, $schema);
        $parsedSoftDeletes = $this->replaceSoftDeletes($parsedSchemaClass);

        return $this->replaceField($parsedSoftDeletes, $parsedModel, $schema);
    }

    public function setViewName($model)
    {
        if (is_array($model) && count($model) > 1 && $this->option('view')) {
            dd('Err: MxN relationship whith view is not suported.');
        }

        if (is_array($model) && count($model) == 1 && $this->option('view')) {
            $model[0] = 'Vw' . $model[0];
        }

        if (!is_array($model) && $this->option('view')) {
            $model = 'Vw' . $model;
        }

        return $model;
    }

    public function infoLog(array $model, ?string $schema): void
    {
        $date = now();
        $prefix = date('Y_m_d_His');
        $parsed_schema = empty($schema) ? '' : strtolower("{$schema}_");

        if ($this->option('mxn')) {
            $model1 = Str::snake($model[0]);
            $model2 = Str::snake($model[1]);
            $this->info("{$date} - [ {$model1}_{$model2} ] >> {$prefix}_create_{$parsed_schema}{$model1}_{$model2}_table.php");
        }

        $parsedModel = is_array($model) ? trim($model[0]) : trim($model);
        $name = Str::snake($this->pluralize($parsedModel));

        $this->info("{$date} - [ {$parsedModel} ] >> {$prefix}_create_{$parsed_schema}{$name}_table.php");
    }

    protected function replaceField($stub, $model = null, $schema = null)
    {
        if (!$this->option('fields') && !is_array($model)) {
            return str_replace('{{ fields }}', "// insert code here.", $stub);
        }

        $fields = $this->buildFields($model);

        $returnFields = "";
        $uniqueArray = [];

        $first = true;
        foreach ($fields as $key => $value) {
            $type = $this->getType($value);
            // Nullable
            $isNullable = $this->hasNullable($value);
            $nullable = $isNullable ? '->nullable()' : '';
            // String, char Size
            $size = '';
            if ($type == 'string' || $type == 'char') {
                $isNumbers = $this->hasNumber($value);
                if ($isNumbers !== false) {
                    $size = ", " . $isNumbers[0];
                }
            }
            // Decimal, double precision
            if ($type == 'decimal' || $type == 'double') {
                $numbers = $this->getPrecisionNumbers($value);
                if ($numbers !== false) {
                    $size = ", " . $numbers[0] . ", " . $numbers[1];
                } else {
                    $size = ', 10, 2';
                }
            }
            // Unique 
            $isUnique = $this->isUnique($value);
            $unique = $isUnique ? '->unique()' : '';
            // Unique Array
            $isUniqueArray = $this->isUniqueArray($value);
            if ($isUniqueArray) {
                array_push($uniqueArray, $key);
            }
            // Default
            $default = $this->hasDefault($value);
            $default = $default !== false ? "->default($default)" : '';
            // Unsigned integer
            $unsigned = '';
            $isUnsigned = $this->isUnsigned($value);
            if ($type == 'integer' && $isUnsigned) {
                $unsigned = '->unsigned()';
            }
            // CPF
            if ($type == 'cpf') {
                $type = 'string';
                $size = ", 11";
            }
            // CNPJ ou CPF/CNPJ
            if ($type == 'cnpj' || $type == 'cpfcnpj') {
                $type = 'string';
                $size = ", 14";
            }
            // Monetary value
            if ($type == 'monetario' || $type == 'monetary') {
                $type = 'decimal';
                $size = ", 16, 2";
            }


            if ($first) {
                $first = false;
            } else {
                $returnFields .= PHP_EOL;
                $returnFields .= $this->tabs(3);
            }

            if ($this->isFk($key)) {
                $referenced_table = $this->pluralize(str_replace("_id", "", $key));

                $returnFields .= "\$table->foreignId('$key')" . PHP_EOL;
                if ($isNullable) {
                    $returnFields .= $this->tabs(4) . $nullable . PHP_EOL;
                } else if ($isUnique) {
                    $returnFields .= $this->tabs(4) . $unique . PHP_EOL;
                }
                $parsedSchema = empty($schema) ? '' : strtolower("{$schema}.");
                $returnFields .= $this->tabs(4) . "->constrained('{$parsedSchema}{$referenced_table}');";
            } else {
                if ($isNullable && $isUnique) {
                    $returnFields .= "\$table->$type('$key'$size)" . PHP_EOL;
                    $returnFields .= $this->tabs(4) . "{$nullable}" . PHP_EOL;
                    if ($isUnsigned) {
                        $returnFields .= $this->tabs(4) . "{$unsigned}" . PHP_EOL;
                    }
                    $returnFields .= $this->tabs(4) . "{$unique};";
                } else if ($default !== false && $isUnique) {
                    $returnFields .= "\$table->$type('$key'$size)" . PHP_EOL;
                    $returnFields .= $this->tabs(4) . "{$unique}";
                    if ($isUnsigned) {
                        $returnFields .=  PHP_EOL . $this->tabs(4) . "{$unsigned}";
                    }
                    $tabulation = $default == '' ? '' : PHP_EOL . $this->tabs(4);
                    $returnFields .=  $tabulation . "{$default};";
                } else {
                    $returnFields .= "\$table->$type('$key'$size){$nullable}{$unique}{$default}{$unsigned};";
                }
            }
        }
        if (count($uniqueArray) > 0) {
            $uniques = implode("','", $uniqueArray);
            $returnFields .= PHP_EOL . $this->tabs(3) . "\$table->unique(['$uniques']);";
        }

        if (is_array($model)) {
            $key1 = Str::snake($model[0]);
            $key2 = Str::snake($model[1]);
            $returnFields .= PHP_EOL;
            $returnFields .= $this->tabs(3) . "\$table->primary(['{$key1}_id', '{$key2}_id'], '{$key1}_{$key2}_{$key1}_id_{$key2}_id_primary');";
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
            $model1 = array($key1 => 'bi');
            $key2 = Str::snake($model[1]) . "_id";
            $model2 = array($key2 => 'bi');

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

    /**
     * Replace the Soft Deletes in the given stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function replaceSoftDeletes(string $stub): string
    {
        $use_soft_deletes = config('laravue.use_soft_deletes');
        $softDelete = $use_soft_deletes
            ? PHP_EOL . $this->tabs(3) . '$table->softDeletes();'
            : '';
        return str_replace('{{ softDeletes }}', $softDelete, $stub);
    }

    /**
     * Replace the Schema Class in the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @return string
     */
    protected function replaceSchemaClass($stub, $schema)
    {
        return str_replace('{{ schemaClass }}', strtolower($schema), $stub);
    }
}
