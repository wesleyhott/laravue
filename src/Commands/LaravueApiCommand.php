<?php

namespace Mpmg\Laravue\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class LaravueApiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:api {model} {--f|fields=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação do backend para o modelo.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->createMigration();
        $this->createSeeder();
        $this->createDataTableSeeder();
        $this->createModel();
        $this->createController();
        $this->createReport();
        $this->createRoute();
        $this->createPermission();
    }

    /**
     * Create a migration file for the model.
     *
     * @return void
     */
    protected function createMigration()
    {
        $this->call('laravue:migration', [
            'model' => $this->argument('model'),
            '--fields' =>  $this->option('fields'),
        ]);
    }

    /**
     * Create a seeder file for the model.
     *
     * @return void
     */
    protected function createSeeder()
    {
        $this->call('laravue:seed', [
            'model' => $this->argument('model'),
            '--fields' =>  $this->option('fields'),
        ]);
    }

    /**
     * Create a database seeder entry for the model.
     *
     * @return void
     */
    protected function createDataTableSeeder()
    {
        $this->call('laravue:dbseeder', [
            'model' => $this->argument('model'),
        ]);
    }

    /**
     * Cria o controller para o modelo.
     *
     * @return void
     */
    protected function createController()
    {
        $this->call('laravue:controller', [
            'model' => $this->argument('model'),
            '--fields' =>  $this->option('fields'),
        ]);
    }

    /**
     * Cria o controller para o modelo.
     *
     * @return void
     */
    protected function createModel()
    {
        $this->call('laravue:model', [
            'model' => $this->argument('model'),
            '--fields' =>  $this->option('fields'),
        ]);
    }

    /**
     * Cria o report controller para o modelo.
     *
     * @return void
     */
    protected function createReport()
    {
        $this->call('laravue:report', [
            'model' => $this->argument('model'),
        ]);
    }

    /**
     * Cria as rotas para o modelo.
     *
     * @return void
     */
    protected function createRoute()
    {
        $this->call('laravue:route', [
            'model' => $this->argument('model'),
        ]);
    }

    /**
     * Cria as rotas para o modelo.
     *
     * @return void
     */
    protected function createPermission()
    {
        $this->call('laravue:permission', [
            'model' => $this->argument('model'),
        ]);
    }
}
