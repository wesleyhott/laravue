<?php

namespace Mpmg\Laravue\Commands;

use Illuminate\Support\Str;

class LaravueSeedCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:seed {model*} {--f|fields=} {--x|mxn}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação de seeder nos padrões do Laravue.';

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
        if( $this->option('mxn') ) {
            $this->setStub('/seed-mxn');
        } else {
            $this->setStub('/seed'); 
        }

        $model = $this->option('mxn') ? $this->argument('model') : trim( $this->argument('model')[0] );
        $parsedModel = is_array( $model ) ? trim( $model[0] ) : trim( $model ); 

        $date = now();

        $path = $this->getPath( $parsedModel );
        $this->files->put( $path, $this->buildSeed( $parsedModel ) );
    
        if ( $this->option('mxn') ) {
            $model1 = $model[0];
            $model2 = $model[1];
            $this->info("$date - [ ${model1}${model2} ] >> ${model1}${model2}"."Seeder.php");
        } else {
            $this->info("$date - [ $parsedModel ] >> ${parsedModel}Seeder.php");
        }
        
    }

    protected function replaceField($stub, $model)
    {
        if(!$this->option('fields') && !is_array( $model ) ) {
            return str_replace( '{{ fields }}', "// insira código aqui." , $stub );
        }

        $fields = $this->buildFields( $model );

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

    public function buildFields( $model ) {
        $model1 = $model2 = "";
        $keys = array();
        $allFields = $fields = $this->getFieldsArray( $this->option('fields') );

        if( is_array( $model ) ) {
            $key1 = Str::snake( $model[0] ) . "_id";
            $model1 = array( $key1 => 'i' );
            $key2 = Str::snake( $model[1] ) . "_id";
            $model2 = array( $key2 => 'i' );

            if( !array_key_exists( $key1, $fields ) && !array_key_exists( $key2, $fields ) ) {
                $allFields = $model1 + $model2 + $fields;
            } else if ( !array_key_exists( $key1, $fields ) ) {
                $allFields = $model1 + $fields;
            } else if ( !array_key_exists( $key2, $fields ) ) {
                $allFields = $model2 + $fields;
            }
        }

        return $allFields;
    }
}
