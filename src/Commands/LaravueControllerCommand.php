<?php

namespace Mpmg\Laravue\Commands;

class LaravueControllerCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:controller {model} {--f|fields=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria um novo controlador nos padrões do Laravue.';

    /**
     * Tipo de modelo que está sendo criado.
     *
     * @var string
     */
    protected $type = 'controller';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setStub('/controller');
        $model = trim($this->argument('model'));
        $date = now();

        $path = $this->getPath($model);
        $this->files->put( $path, $this->buildModel( $model ) );

        $this->info("$date - [ $model ] >> $model"."Controller.php");
    }

    protected function replaceField($stub, $model)
    {
        if(!$this->option('fields')){
            return str_replace( '{{ fields }}', "//$"."model->field = $"."request->input('field');" , $stub );
        }

        $fields = $this->getFieldsArray( $this->option('fields') );

        //fields
        $returnFields = "";
        $first = true;
        foreach ($fields as $key => $value) {
            if( $first ) {
                $first = false;
            } else {
                $returnFields .= PHP_EOL;
                $returnFields .= "\t\t";
            } 
            $returnFields .= "$"."model->$key = $"."request->input('$key');"; 
        }
        $returnFields .= "\t\t";
        $parsedfFields = str_replace( '{{ fields }}', $returnFields , $stub );

        //rules
        $returnRules = "";
        $first = true;
        foreach ($fields as $key => $value) {
            $type = $this->getType($value);
            if( $first ) {
                $first = false;
            } else {
                $returnRules .= PHP_EOL;
                $returnRules .= "\t\t\t\t";
            } 
            $returnRules .= "'$key' => '$type',"; 
        }
        // $returnRules .= "\t\t";

        return str_replace( '{{ rules }}', $returnRules , $parsedfFields );
    }
}
