<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MpmgFrontIndex extends MpmgCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mpmg:index {model} {--f|fields=}';

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
        $default .= "\t\t\t\t\tprop: \"id\"," . PHP_EOL;
        $default .= "\t\t\t\t\tlabel: \"ID\"," . PHP_EOL;
        $default .= "\t\t\t\t\tminWidth: {{ fieldSize }}" . PHP_EOL;
        $default .= "\t\t\t\t},";

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

        $returnFields = $default . PHP_EOL;
        foreach ($fields as $key => $value) {
            $label = $this->isFk($key) ? $this->getTitle( str_replace( "_id", "", $key ) ) : $this->getTitle( $key );
            $returnFields .= "\t\t\t\t{" . PHP_EOL;
            $returnFields .= "\t\t\t\t\tprop: \"$key\"," . PHP_EOL;
            $returnFields .= "\t\t\t\t\tlabel: \"$label\"," . PHP_EOL;
            $returnFields .= "\t\t\t\t\tminWidth: $fieldSize" . PHP_EOL;
            $returnFields .= "\t\t\t\t},";
        }

        return str_replace( '{{ fields }}', $returnFields , $stub );
    }
}
