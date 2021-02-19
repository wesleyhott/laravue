<?php

namespace Mpmg\Laravue\Commands;

use Illuminate\Support\Str;

class LaravueMNFrontCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:mxnfront {model* : The model to be builded} 
        {--k|keys= : custom foreing keys that belongs to relationship}
        {--p|pivots= : Feilds that belongs to relationship}
        {--o|outdocker : indicates the origin of command}';

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

        $this->createModel( $fields );
    }

    /**
     * Create a migration file for the model.
     *
     * @return void
     */
    protected function createModel( $fields )
    {
        print_r('creating model..');
    }
}
