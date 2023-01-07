<?php

namespace wesleyhott\Laravue\Commands;

use Illuminate\Support\Str;

class LaravueControllerCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:controller {model*} {--x|mxn} {--i|view} {--s|schema= : determine a schema for model (postgres)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It makes a new Controller in Laravue standart';

    /**
     * Command type for path generation.
     *
     * @var string
     */
    protected $type = 'controller';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('mxn')) {
            return $this->info('MxN not implemented at model controller');
        } else if ($this->option('view')) {
            return $this->info('view not implemented at model controller');
        } else {
            $this->setStub('/controller');
        }

        $model = $this->option('mxn') ? $this->argument('model')[0] . $this->argument('model')[1] : $this->argument('model');
        $parsedModel = is_array($model) ? $model[0] : trim($model);

        $date = now();

        $path = $this->getPath(model: $parsedModel, schema: $this->option('schema'));
        $this->files->put($path, $this->buildRequest($parsedModel, $this->option('schema')));

        if ($this->option('mxn')) {
            $this->info("$date - [ {$model} ] >> {$model}Controller.php");
        } else {
            $stringModel = is_array($parsedModel) ? trim($parsedModel[0]) : trim($parsedModel);
            $this->info("{$date} - [ {$stringModel} ] >> {$stringModel}Controller.php");
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
        $modelStub = $this->replaceModel($stub, $parsedModel);

        return $this->replaceSchemaNamespace($modelStub, $schema);
    }
}
