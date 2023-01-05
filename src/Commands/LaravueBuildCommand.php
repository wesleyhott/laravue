<?php

namespace wesleyhott\Laravue\Commands;

use Illuminate\Support\Str;

class LaravueBuildCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:build {model* : The model to be builded} 
        {--f|fields= : Feilds that belongs to model} 
        {--b|backward : Indicates to rebuild entire database}
        {--bw : Indicates to rebuild entire database}
        {--w|forward : Indicates to entry new data on database}
        {--fw : Indicates to entry new data on database}
        {--o|outdocker : Indicates running outside docker}
        {--k|keys= : custom foreing keys that belongs to relationship}
        {--p|pivots= : Feilds that belongs to relationship}
        {--i|view : build a model based on view, not table}
        {--s|schema : build a model based on view, not table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria os aruquivos para um modelo';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $argumentModel = $this->argument('model');
        if (gettype($this->argument('model')) == 'string') {
            $argumentModel = array($this->argument('model'));
        }

        $models = [];
        foreach ($argumentModel as $model) {
            array_push($models, Str::studly($model));
        }

        $this->backend($models, $this->option('schema'));
        // $this->frontend($models, $this->option('schema'));

        if ($this->option('backward') || $this->option('bw')) {
            $this->backward();
        } else if ($this->option('forward') || $this->option('fw')) {
            $this->forward();
        }
    }

    /**
     * Cria o backend para o modelo.
     *
     * @return void
     */
    protected function backend($models, $schema)
    {
        if (count($models) == 1) {
            $this->call('laravue:api', [
                'model' => $models,
                '--schema' => $schema,
                '--fields' =>  $this->option('fields'),
                '--view' =>  $this->option('view'),
            ]);
        } else {
            $this->call('laravue:mxn', [
                'model' => $models,
                '--keys' =>  $this->option('keys'),
                '--pivots' =>  $this->option('pivots'),
            ]);
        }
    }

    /**
     * Cria o frontend para o modelo.
     *
     * @return void
     */
    protected function frontend($models, $schema)
    {
        if (count($models) == 1) {
            $this->call('laravue:front', [
                'model' => $models,
                '--fields' =>  $this->option('fields'),
                '--outdocker' =>  $this->option('outdocker'),
            ]);
        }
    }

    /**
     * Recria o banco de dados.
     *
     * @return void
     */
    protected function backward()
    {
        $date = now();
        $this->info("$date - [ composer ] >> dump-autoload");
        $this->composer->dumpAutoloads();
        $this->info("$date - [ artisan ] >> migrate:fresh --seed");
        $this->call('migrate:fresh', [
            '--seed' =>  true,
        ]);
    }

    /**
     * Insere novos dados no banco
     *
     * @return void
     */
    protected function forward()
    {
        $date = now();

        $this->info("$date - [ artisan ] >> migrate");
        $this->call('migrate');

        $this->info("$date - [ composer ] >> dump-autoload");
        $this->composer->dumpAutoloads();

        $argumentModel = $this->argument('model');
        $model = is_array($argumentModel) ? trim($argumentModel[0]) : trim($argumentModel);
        $permissionName = $this->pluralize(strtolower($model));
        $this->info("$date - [ spatie ] >> permission:create-permission");

        $this->call('permission:create-permission', [
            'name' =>  "c-{$permissionName}",
        ]);

        $this->call('permission:create-permission', [
            'name' =>  "r-{$permissionName}",
        ]);

        $this->call('permission:create-permission', [
            'name' =>  "u-{$permissionName}",
        ]);

        $this->call('permission:create-permission', [
            'name' =>  "d-{$permissionName}",
        ]);

        $this->call('permission:create-permission', [
            'name' =>  "p-{$permissionName}",
        ]);
    }
}
