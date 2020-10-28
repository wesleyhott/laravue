<?php

namespace App\Console\Commands;

class MpmgSeed extends MpmgCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mpmg:seed {model} {--f|fields=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação de seeder nos padrões do MPMG.';

    /**
     * Tipo de modelo que está sendo criado.
     *
     * @var string
     */
    protected $type = 'seed';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setStub('/seed');
        $model = trim($this->argument('model'));
        $date = now();

        $path = $this->getPath($model);
        $this->files->put($path, $this->sortImports($this->buildSeed($model)));

        $plural = $this->pluralize( 2, $model );
        $this->info("$date - [ $model ] >> $plural"."TableSeeder.php");
    }

    protected function replaceField($stub, $model)
    {
        if(!$this->option('fields')){
            return str_replace( '{{ fields }}', "// insira código aqui." , $stub );
        }

        $fields = $this->getFieldsArray( $this->option('fields') );

        $returnFields = "";
        
        $first = true;
        foreach ($fields as $key => $value) {
            if( $first ) {
                $first = false;
            } else {
                $returnFields .= PHP_EOL;
                $returnFields .= $this->tabs(2);
            }
            $returnFields .= "// ". $this->tabs(2) . "\"$key\" => '',";
        }

        return str_replace( '{{ fields }}', $returnFields , $stub );
    }
}
