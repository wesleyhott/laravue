<?php

namespace Mpmg\Laravue\Commands;

use Illuminate\Support\Str;

class LaravueMNCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:mxn {model* : The model to be builded} 
        {--k|keys= : custom foreing keys that belongs to relationship}
        {--p|pivots= : Feilds that belongs to relationship}';

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
}
