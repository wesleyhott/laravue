<?php

namespace App\Console\Commands;

class MpmgReport extends MpmgCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mpmg:report {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria um novo controlador de relatório nos padrões do MPMG.';

    /**
     * Tipo de modelo que está sendo criado.
     *
     * @var string
     */
    protected $type = 'report';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setStub('/report');
        $model = trim($this->argument('model'));
        $date = now();

        $path = $this->getPath($model);
        $this->files->put( $path, $this->buildModel( $model ) );

        $this->info("$date - [ $model ] >> $model"."ReportController.php");
    }
}
