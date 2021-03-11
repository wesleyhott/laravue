<?php

namespace Mpmg\Laravue\Commands;

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
        {--o|outdocker : Indicates running outside docker}';

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
        $models = [];
        foreach ($this->argument('model') as $model){
            array_push( $models,  Str::studly( $model ) );
        }

        $this->backend( $models );
        $this->frontend( $models );

        if( $this->option('backward') || $this->option('bw') ){
            $this->backward();
        } else if( $this->option('forward') || $this->option('fw') ){
            $this->forward();
        } 
    }

    /**
     * Cria o backend para o modelo.
     *
     * @return void
     */
    protected function backend( $models )
    {
        $this->call('laravue:api', [
            'model' => $models,
            '--fields' =>  $this->option('fields'),
        ]);
    }

    /**
     * Cria o frontend para o modelo.
     *
     * @return void
     */
    protected function frontend( $models )
    {
        $this->call('laravue:front', [
            'model' => $models,
            '--fields' =>  $this->option('fields'),
            '--outdocker' =>  $this->option('outdocker'),
        ]);
    }

    /**
     * Recria o banco de dados.
     *
     * @return void
     */
    protected function backward() {
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
    protected function forward() {
        $date = now();

        $this->info("$date - [ artisan ] >> migrate");
        $this->call('migrate');

        $this->info("$date - [ composer ] >> dump-autoload");
        $this->composer->dumpAutoloads();

        $permissionName = $this->pluralize( 2, strtolower( $this->argument('model') ) );
        $this->info("$date - [ spatie ] >> permission:create-permission");
        
        $this->call('permission:create-permission', [
            'name' =>  "ver $permissionName",
        ]);

        $this->call('permission:create-permission', [
            'name' =>  "editar $permissionName",
        ]);

        $this->call('permission:create-permission', [
            'name' =>  "apagar $permissionName",
        ]);

        $this->call('permission:create-permission', [
            'name' =>  "imprimir $permissionName",
        ]);
    }
}
