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
    protected $signature = 'laravue:build {model : The model to be builded} 
        {--f|fields= : Feilds that belongs to model} 
        {--b|backward : Indicates to rebuild entire database}
        {--bw : Indicates to rebuild entire database}
        {--w|forward : Indicates to entry new data on database}
        {--fw : Indicates to entry new data on database}';

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
        $this->backend();
        $this->frontend();

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
    protected function backend()
    {
        $this->call('laravue:api', [
            'model' => Str::studly( $this->argument('model') ),
            '--fields' =>  $this->option('fields'),
        ]);
    }

    /**
     * Cria o frontend para o modelo.
     *
     * @return void
     */
    protected function frontend()
    {
        $this->call('laravue:front', [
            'model' => Str::studly( $this->argument('model') ),
            '--fields' =>  $this->option('fields'),
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
