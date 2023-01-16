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
                                {--s|schema= : determines a schema for model (postgres)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Makes a model in Laravue Pattern.';

    /**
     * Model type that is being created.
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

        $path = $this->getPath($model, 'php', $this->option('schema'));
        $fields =  $this->option('fields') ? $this->getFieldsArray($this->option('fields')) : [];
        $this->files->put($path, $this->buildModel($model, $fields, $this->option('schema')));

        $this->info("$date - [ $model ] >> $model.php");
    }

    /**
     * ReplaceField is setting the $casts attirbute for fields for model.
     */
    protected function replaceField($stub, $model = null, $shema = null)
    {
        if (!$this->option('fields')) {
            return str_replace('{{ fields }}', "", $stub);
        }

        $fields = $this->getFieldsArray($this->option('fields'));
        $returnFields = '';
        $returnMethods = '';
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
                case 'monetary':
                    $type = 'float';
                    break;
                case 'bigInteger':
                case 'mediumInteger':
                case 'smallInteger':
                case 'tinyInteger':
                    $type = 'int';
                    break;
                case 'decimal':
                    $type = 'float';
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
     * Build the model.
     *
     * @param  string  $model
     * @param  string  $fields = null
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildModel($model, $fields = null, $schema = null)
    {
        $stub = $this->files->get($this->getStub());

        if (is_array($model)) {
            return $this->replaceRelation($stub, $model, $fields, $schema);
        }

        $fieldStub = $this->replaceField($stub, $model);
        $parsed_schema = empty($schema) ? '' : strtolower("{$schema}.");
        $parsed_model = Str::snake($model);
        $tableStub = $this->replaceTable($fieldStub, "{$parsed_schema}{$parsed_model}");
        $relationStub = $this->replaceRelation($tableStub, $model, $fields, $schema);
        $schemaStub = $this->replaceSchemaNamespace($relationStub, $schema);

        return $this->replaceModel($schemaStub, $model);
    }

    /**
     * Replace the plural for class in the given stub.
     * Override because here snake cannot be applied.
     *
     * @param  string  $stub
     * @param  string  $model
     * @return string
     */
    protected function replaceTable($stub, $model, $plural = true)
    {
        if ($plural) {
            $model = $this->pluralize($model);
        }
        return str_replace('{{ table }}', $model, $stub);
    }

    /**
     * Replace the relationship for the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @param  array  $fields
     * @return string $stub
     */
    protected function replaceRelation($modelFile, $model, $fields, $schema)
    {
        $newRelations = $modelFile;

        foreach ($fields as $key => $value) {
            if ($this->isFk($key)) {
                $fieldRelationModel = Str::studly(str_replace("_id", "", $key));
                $relationName = lcfirst($fieldRelationModel);
                $title = $this->getTitle($model);

                $newRelation = "/**" . PHP_EOL;
                $newRelation .= $this->tabs(1) . " * Returns $fieldRelationModel that $title contains." . PHP_EOL;
                $newRelation .= $this->tabs(1) . " */" . PHP_EOL;
                $newRelation .= $this->tabs(1) . "public function $relationName(): BelongsTo" . PHP_EOL;
                $newRelation .= $this->tabs(1) . "{" . PHP_EOL;
                $newRelation .= $this->tabs(2) . "return \$this->belongsTo({$fieldRelationModel}::class);" . PHP_EOL;
                $newRelation .= $this->tabs(1) . "}" . PHP_EOL;
                $newRelation .= PHP_EOL;
                $newRelation .= $this->tabs(1) . "// {{ laravue-insert:relationship }}";

                $newRelations = str_replace('// {{ laravue-insert:relationship }}', $newRelation, $newRelations);

                $newProperty = '';
                $newProperty .= " * @property {$fieldRelationModel} \${$relationName}";
                $newProperty .= PHP_EOL;
                $newProperty .= " * {{ laravue-insert:property }}";

                $newRelations = str_replace(' * {{ laravue-insert:property }}', $newProperty, $newRelations);

                // $newMethod = '';
                // $newMethod .= " * @method {$relationName}()";
                // $newMethod .= PHP_EOL;
                // $newMethod .= " * {{ laravue-insert:method }}";

                // $newRelations = str_replace(' * {{ laravue-insert:method }}', $newMethod, $newRelations);

                $this->reverseRelation($fieldRelationModel, $model, $schema);
            }
        }

        return $newRelations;
    }

    protected function reverseRelation($reverseModel, $model, $schema)
    {
        $currentDirectory =  getcwd();
        $parsedSchema = empty($schema) ? '' : "/{$schema}";
        $path = "$currentDirectory/app/Models{$parsedSchema}/{$reverseModel}.php";
        $modelFile = $this->files->get($path);

        $relationName = lcfirst($this->pluralize($model));

        $newRelation = "/**" . PHP_EOL;
        $newRelation .= $this->tabs(1) . " * Returns $relationName of $reverseModel." . PHP_EOL;
        $newRelation .= $this->tabs(1) . " */" . PHP_EOL;
        $newRelation .= $this->tabs(1) . "public function $relationName(): HasMany" . PHP_EOL;
        $newRelation .= $this->tabs(1) . "{" . PHP_EOL;
        $newRelation .= $this->tabs(2) . "return \$this->hasMany({$model}::class);" . PHP_EOL;
        $newRelation .= $this->tabs(1) . "}" . PHP_EOL;
        $newRelation .= PHP_EOL;
        $newRelation .= $this->tabs(1) . "// {{ laravue-insert:relationship }}";

        $parsedRelation = str_replace('// {{ laravue-insert:relationship }}', $newRelation, $modelFile);

        $newProperty = '';
        $newProperty .= " * @property {$model}[] \${$relationName}";
        $newProperty .= PHP_EOL;
        $newProperty .= " * {{ laravue-insert:property }}";

        $parsedProperty = str_replace(' * {{ laravue-insert:property }}', $newProperty, $parsedRelation);

        // $newMethod = '';
        // $newMethod .= " * @method {$relationName}()";
        // $newMethod .= PHP_EOL;
        // $newMethod .= " * {{ laravue-insert:method }}";

        // $parsedMethod = str_replace(' * {{ laravue-insert:method }}', $newMethod, $parsedProperty);

        $this->files->put($path, $parsedProperty);
    }

    protected function mxnRelation($modelM, $modelN)
    {
        $currentDirectory =  getcwd();
        $path = "$currentDirectory/app/Models/$modelM.php";
        $modelFile = "";
        try {
            $modelFile = $this->files->get($path);
        } catch (\Exception $e) {
            $this->error("File - $currentDirectory/app/Models/$modelM.php - not found.");
        }

        $relationName = lcfirst($this->pluralize($modelN));

        $newRelation = "/**" . PHP_EOL;
        $newRelation .= $this->tabs(1) . " * Returns$relationName of $modelM." . PHP_EOL;
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
}
