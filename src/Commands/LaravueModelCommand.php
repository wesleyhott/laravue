<?php

namespace Mpmg\Laravue\Commands;

class LaravueModelCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:model {model} {--f|fields=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação de modelo de negócio nos padrões do Laravue.';

    /**
     * Tipo de modelo que está sendo criado.
     *
     * @var string
     */
    protected $type = 'model';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setStub('/model');
        $model = trim($this->argument('model'));
        $date = now();

        $path = $this->getPath($model);
        $this->files->put($path, $this->sortImports($this->buildModel($model)));

        $this->info("$date - [ $model ] >> $model.php");
    }

    protected function replaceField($stub, $model)
    {
        if(!$this->option('fields')){
            return str_replace( '{{ fields }}', "" , $stub );
        }

        $fields = $this->getFieldsArray( $this->option('fields') );

        $returnFields = PHP_EOL . "\t\t\t";
        $first = true;
        foreach ($fields as $key => $value) {
            if( $first ) {
                $first = false;
                $returnFields .= "'$key' => '".ucfirst($key)."',". PHP_EOL;
            } else {
                $returnFields .= "\t\t\t'$key' => '".ucfirst($key)."'," . PHP_EOL;
            }  
        }
        $returnFields .= "\t\t";

        return str_replace( '{{ fields }}', $returnFields , $stub );
    }
}
