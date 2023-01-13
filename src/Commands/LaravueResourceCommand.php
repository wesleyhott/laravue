<?php

namespace wesleyhott\Laravue\Commands;

use Illuminate\Support\Str;

class LaravueResourceCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:resource {model*} 
                                {--f|fields=} 
                                {--x|mxn} 
                                {--i|view : build a model based on view, not table}
                                {--s|schema= : determine a schema for model (postgres)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Makes a Model Resource in Laravue standart.';

    /**
     * Command type for path generation.
     *
     * @var string
     */
    protected $type = 'resource';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('mxn')) {
            return $this->info('MxN not implemented at model resource');
        } else if ($this->option('view')) {
            return $this->info('view not implemented at model resource');
        } else {
            $this->setStub('/resource');
        }

        $model = $this->option('mxn') ? $this->argument('model')[0] . $this->argument('model')[1] : $this->argument('model');
        $parsedModel = is_array($model) ? $model : trim($model);

        $date = now();

        $path = $this->getPath(model: $parsedModel, schema: $this->option('schema'));
        $this->files->put($path, $this->buildRequest($parsedModel, $this->option('schema')));

        if ($this->option('mxn')) {
            $this->info("$date - [ {$model} ] >> {$model}Resource.php");
        } else {
            $stringModel = is_array($parsedModel) ? trim($parsedModel[0]) : trim($parsedModel);
            $this->info("{$date} - [ {$stringModel} ] >> {$stringModel}Resource.php");
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

    protected function replaceField($stub, $model = null, $schema = null)
    {
        if (!$this->option('fields') && !is_array($model)) {
            return str_replace(['{{ properties_doc }}', '{{ properties }}', '{{ relations }}'], '', $stub);
        }

        $fields = $this->buildFields($model);

        $use = $this->replaceUse($stub, $fields);
        $propertiesDocStub = $this->replacePropertiesDoc($use, $fields);
        $propertiesStub = $this->replaceProperties($propertiesDocStub, $fields);
        $relationsStub = $this->replaceRelations($propertiesStub, $model, $fields);

        return $relationsStub;
    }

    public function replaceUse(string $stub, array $fields): string
    {
        $use = '';

        foreach ($fields as $key => $value) {
            if ($this->isFk($key)) {
                $property = str_replace('_id', '', $key);
                $schema = empty($this->option('schema')) ? '' : '\\' . Str::ucfirst($this->option('schema'));
                $relatedModel = Str::studly($property);
                $use .= PHP_EOL . "use App\Models{$schema}\\{$relatedModel};";
            }
        }

        return str_replace('{{ use }}', $use, $stub);
    }

    public function replacePropertiesDoc(string $stub, array $fields): string
    {
        $properties_doc = '';

        foreach ($fields as $key => $value) {
            if ($this->isFk($key)) {
                $properties_doc .= PHP_EOL . " * @property integer \${$key}";
            }
        }

        return str_replace('{{ properties_doc }}', $properties_doc, $stub);
    }

    public function replaceProperties(string $stub, array $fields): string
    {
        $properties = '';

        foreach ($fields as $key => $value) {
            if (!$this->isFk($key)) {
                $properties .= PHP_EOL . $this->tabs(3) . "'{$key}',";
            }
        }

        return str_replace('{{ properties }}', $properties, $stub);
    }

    public function replaceRelations(string $stub, string $model, array $fields): string
    {
        $relations = '';

        foreach ($fields as $key => $value) {
            if ($this->isFk($key)) {
                $property = str_replace('_id', '', $key);
                $relation = Str::camel($property);
                $relatedModel = Str::studly($property);
                $relations .= PHP_EOL . $this->tabs(3) . "'{$relation}' => new {$relatedModel}Resource({$relatedModel}::find(\$this->{$key})),";

                // Make Reverse Relation (Collection)
                $this->makeReverseRelations($relatedModel, $model, $this->option('schema'));
            }
        }

        return str_replace('{{ relations }}', $relations, $stub);
    }

    public function makeReverseRelations(string $reverseModel, string $model, ?string $schema)
    {

        $currentDirectory =  getcwd();
        $parsedSchema = empty($schema) ? '' : "/{$schema}";
        $path = "$currentDirectory/app/Http/Resources{$parsedSchema}/{$reverseModel}Resource.php";
        try {
            $modelFile = $this->files->get($path);
        } catch (\Exception $e) {
            $this->error("File - {$path} - not found.");
        }

        $relation = $this->pluralize(Str::camel($model));
        $newRelation = "'{$relation}' =>  {$model}Resource::collection(\$this->$relation),";
        $newRelation .= PHP_EOL . $this->tabs(3) . '// {{ laravue-insert:relations }}';

        $parsedRelation = str_replace('// {{ laravue-insert:relations }}', $newRelation, $modelFile);

        $this->files->put($path, $parsedRelation);
    }

    public function buildFields($model)
    {
        $model1 = $model2 = "";
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
}
