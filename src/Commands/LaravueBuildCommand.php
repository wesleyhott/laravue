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
        {--s|schema= : build a model based on view, not table}';

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
        $argument_model = $this->argument('model');
        if (gettype($this->argument('model')) == 'string') {
            $argument_model = array($this->argument('model'));
        }

        $models = [];
        foreach ($argument_model as $model) {
            array_push($models, Str::studly($model));
        }

        $this->backend($models, $this->option('schema'));
        $this->frontend($models, $this->option('schema'));

        if ($this->option('backward') || $this->option('bw')) {
            $this->backward();
        } else if ($this->option('forward') || $this->option('fw')) {
            $this->forward($models);
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
        $this->info("$date - [ artisan ] >> migrate:fresh --seed");
        $this->call('migrate:fresh', [
            '--seed' =>  true,
        ]);
    }

    /**
     * Insere novos dados no banco
     *
     * @param array $model
     * @return void
     */
    protected function forward(array $model): void
    {
        $parsed_model = count($model) > 1 ? "{$model[0]}{$model[1]}" : $model[0];

        $date = now();
        $this->info("{$date} - [ artisan ] >> migrate");
        $this->call('migrate');

        $date = now();
        $this->info("{$date} - [ artisan ] >> seed");
        $schema = empty($this->option('schema')) ? '' : Str::ucfirst($this->option('schema'));
        $this->call('db:seed', [
            '--class' => "{$schema}{$parsed_model}Seeder",
        ]);

        $permission_route = Str::snake($this->pluralize($parsed_model), '-');
        $permission_name = $this->getTitle($parsed_model);
        $schema_route = empty($schema) ? '' : Str::snake($this->option('schema')) . '-';
        $schema_name = empty($schema) ? '' : $this->getTitle($this->option('schema'));

        $this->call('laravue:spatie-permission', [
            'name' =>  "c-{$schema_route}{$permission_route}",
            '--label' => "Create {$schema_name} {$permission_name}",
        ]);

        $this->call('laravue:spatie-permission', [
            'name' =>  "r-{$schema_route}{$permission_route}",
            '--label' => "Read {$schema_name} {$permission_name}",
        ]);

        $this->call('laravue:spatie-permission', [
            'name' =>  "u-{$schema_route}{$permission_route}",
            '--label' => "Update {$schema_name} {$permission_name}",
        ]);

        $this->call('laravue:spatie-permission', [
            'name' =>  "d-{$schema_route}{$permission_route}",
            '--label' => "Delete {$schema_name} {$permission_name}",
        ]);

        $this->call('laravue:spatie-permission', [
            'name' =>  "p-{$schema_route}{$permission_route}",
            '--label' => "Print {$schema_name} {$permission_name}",
        ]);

        $this->call('laravue:spatie-permission', [
            'name' =>  "m-{$schema_route}{$permission_route}",
            '--label' => "Access {$schema_name} {$permission_name} Menu",
        ]);
    }
}
