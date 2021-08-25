<?php

namespace Mpmg\Laravue\Commands;

use Illuminate\Console\Command;

class LaravueFrontIndexCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:frontindex {model*} {--f|fields=} {--o|outdocker}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação do frontend Index.vue';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setStub('/front/index');
        $argumentModel = $this->argument('model');
        $model = is_array( $argumentModel ) ? trim( $argumentModel[0] ) : trim( $argumentModel ); 
        $date = now();

        $path = $this->getFrontPath($model, "Index");
        $this->files->put( $path, $this->buildModel($model) );

        $this->info("$date - [ $model ] >> Index.vue");
    }

    protected function replaceField($stub, $model)
    {
        $fieldSize = 330;
        $default = "{" . PHP_EOL;
        $default .=  $this->tabs(3) . "prop: \"id\"," . PHP_EOL;
        $default .=  $this->tabs(3) . "label: \"ID\"," . PHP_EOL;
        $default .=  $this->tabs(3) . "minWidth: $fieldSize" . PHP_EOL;
        $default .=  $this->tabs(2) . "},";

        if(!$this->option('fields')){
            return str_replace( '{{ fields }}', $default , $stub );
        }

        $fields = $this->getFieldsArray( $this->option('fields') );
        if( count($fields) > 0 ) {
            $rest = $fieldSize % ( count($fields) );
            $fieldSize = floor( $fieldSize / ( count($fields) ) );
            $default = '';
        } 

        $returnFields = '';
        $first = true;
        foreach ($fields as $key => $value) {
            $label = $this->getTitle( $key );
            $minWidth = $fieldSize + $rest;
            if( $first ) {
                $first = false;
            } else {
                $returnFields .= PHP_EOL . $this->tabs(4);
            }
            $returnFields .= "{" . PHP_EOL;
            $returnFields .= $this->tabs(5) . "prop: \"$key\"," . PHP_EOL;
            $returnFields .= $this->tabs(5) . "label: \"$label\"," . PHP_EOL;
            $returnFields .= $this->tabs(5) . "minWidth: $minWidth," . PHP_EOL;
            if( $this->isBoolean( $value ) ) {
                $returnFields .= $this->tabs(5) . "type: \"bit\"," . PHP_EOL;
            }
            $returnFields .= $this->tabs(4) . "},";
            $rest = 0;
        }

        return str_replace( '{{ fields }}', $returnFields , $stub );
    }
}
