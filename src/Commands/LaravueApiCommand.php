<?php

namespace wesleyhott\Laravue\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class LaravueApiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:api {model*} 
                                {--f|fields=} 
                                {--i|view : build a model based on view, not table}
                                {--s|schema= : determine a schema for model (postgres)}';

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
        $this->createDataSeeder();
        $this->createModel();
        $this->createStoreRequest();
        $this->createUpdateRequest();
        $this->createResource();
        $this->createService();
        $this->createController();
        // $this->createReport();
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
            '--schema' =>  $this->option('schema'),
            '--fields' =>  $this->option('fields'),
            '--view' =>  $this->option('view'),
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
            '--schema' =>  $this->option('schema'),
            '--fields' =>  $this->option('fields'),
            '--view' =>  $this->option('view'),
        ]);
    }

    /**
     * Create a database seeder entry for the model.
     *
     * @return void
     */
    protected function createDataSeeder()
    {
        $this->call('laravue:dbseeder', [
            'model' => $this->argument('model'),
        ]);
    }

    /**
     * Create the entry model.
     *
     * @return void
     */
    protected function createModel()
    {
        $this->call('laravue:model', [
            'model' => $this->argument('model'),
            '--schema' =>  $this->option('schema'),
            '--fields' =>  $this->option('fields'),
            '--view' =>  $this->option('view'),
        ]);
    }

    /**
     * Creates a Store Request for the entry model.
     *
     * @return void
     */
    protected function createStoreRequest()
    {
        $this->call('laravue:request', [
            'model' => $this->argument('model'),
            '--schema' =>  $this->option('schema'),
            '--fields' =>  $this->option('fields'),
            '--store' =>  true,
        ]);
    }

    /**
     * Creates a Update Request for the entry model.
     *
     * @return void
     */
    protected function createUpdateRequest()
    {
        $this->call('laravue:request', [
            'model' => $this->argument('model'),
            '--schema' =>  $this->option('schema'),
            '--fields' =>  $this->option('fields'),
            '--update' =>  true,
        ]);
    }

    /**
     * Creates a Resource for the entry model.
     *
     * @return void
     */
    protected function createResource()
    {
        $this->call('laravue:resource', [
            'model' => $this->argument('model'),
            '--schema' =>  $this->option('schema'),
            '--fields' =>  $this->option('fields'),
        ]);
    }

    /**
     * Creates a Resource for the entry model.
     *
     * @return void
     */
    protected function createService()
    {
        $this->call('laravue:service', [
            'model' => $this->argument('model'),
            '--schema' =>  $this->option('schema'),
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
            '--schema' =>  $this->option('schema'),
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
            '--fields' =>  $this->option('fields'),
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
            '--view' =>  $this->option('view'),
        ]);
    }
}
