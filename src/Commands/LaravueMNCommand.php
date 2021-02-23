<?php

namespace Mpmg\Laravue\Commands;

class LaravueMNCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:mxn {model* : The model to be builded} 
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

        $this->backend( $fields );
        $this->frontend( $fields );

    }

    /**
     * Cria o backend para o modelo.
     *
     * @return void
     */
    protected function backend( $pivots )
    {
        $this->call('laravue:mxnapi', [
            'model' => $this->argument('model'),
            '--pivots' =>  $pivots,
        ]);
    }

    /**
     * Cria o frontend para o modelo.
     *
     * @return void
     */
    protected function frontend( $pivots )
    {
        $this->call('laravue:mxnfront', [
            'model' => $this->argument('model'),
            '--pivots' =>  $pivots,
            '--outdocker' =>  $this->option('outdocker'),
        ]);
    }
}
