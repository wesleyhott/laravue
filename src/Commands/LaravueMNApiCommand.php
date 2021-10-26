<?php

namespace wesleyhott\Laravue\Commands;

use Illuminate\Support\Str;

class LaravueMNApiCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:mxnapi {model* : The model to be builded} 
        {--k|keys= : custom foreing keys that belongs to relationship}
        {--p|pivots= : Feilds that belongs to relationship}
        {--x|mxn : Determines if is a mxn relationship}';

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
        $fields = "";
        if( $this->option('keys') !== null ) {
            $fields = $this->option('keys');
        }
        
        $virgula = $fields == "" ? "" : ",";

        if( $this->option('pivots') !== null ) {
            $fields .= $virgula . $this->option('pivots');
        }

        $this->createMigration( $fields );
        $this->createSeeder( $fields );
        $this->createDataSeeder();
        $this->createModel();
        $this->createPermission();
        $this->createController();
    }

    /**
     * Create a migration file for the model.
     *
     * @return void
     */
    protected function createMigration( $fields )
    {
        $this->call('laravue:migration', [
            'model' => $this->argument('model'),
            '--fields' => $fields,
            '--mxn' =>  true,
        ]);
    }

    /**
     * Create a seeder file for the model.
     *
     * @return void
     */
    protected function createSeeder( $fields )
    {
        $this->call('laravue:seed', [
            'model' => $this->argument('model'),
            '--fields' =>  $fields,
            '--mxn' =>  true,
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
            '--mxn' =>  true,
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
            '--mxn' =>  true,
        ]);
    }

    /**
     * Cria as rotas para o modelo.
     *
     * @return void
     */
    protected function createPermission()
    {
        $model = array( $this->argument('model')[0] . $this->argument('model')[1] );
        $this->call('laravue:permission', [
            'model' => $model,
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
            '--mxn' =>  true,
        ]);
    }
}
