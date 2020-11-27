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
    protected $signature = 'laravue:frontindex {model} {--f|fields=}';

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
        $model = trim($this->argument('model'));
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
        $default .=  $this->tabs(3) . "minWidth: {{ fieldSize }}" . PHP_EOL;
        $default .=  $this->tabs(2) . "},";

        if(!$this->option('fields')){
            $default = str_replace( '{{ fieldSize }}', "330" , $default );
            return str_replace( '{{ fields }}', $default , $stub );
        }

        $fields = $this->getFieldsArray( $this->option('fields') );
        if( count($fields) > 0 ) {
            $rest = $fieldSize % ( count($fields) + 1);
            $fieldSize = floor( $fieldSize / ( count($fields) + 1 ) );
            $default = str_replace( '{{ fieldSize }}', $fieldSize + $rest , $default );
        } 

        $returnFields = $default;
        foreach ($fields as $key => $value) {
            $label = $this->isFk($key) ? $this->getTitle( str_replace( "_id", "", $key ) ) : $this->getTitle( $key );
            $returnFields .= PHP_EOL;
            $returnFields .= $this->tabs(2) . "{" . PHP_EOL;
            $returnFields .= $this->tabs(3) . "prop: \"$key\"," . PHP_EOL;
            $returnFields .= $this->tabs(3) . "label: \"$label\"," . PHP_EOL;
            $returnFields .= $this->tabs(3) . "minWidth: $fieldSize," . PHP_EOL;
            if( $this->isBoolean( $value ) ) {
                $returnFields .= $this->tabs(3) . "type: \"bit\"," . PHP_EOL;
            }
            $returnFields .= $this->tabs(2) . "},";
        }

        return str_replace( '{{ fields }}', $returnFields , $stub );
    }
}
