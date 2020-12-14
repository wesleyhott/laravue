<?php

namespace Mpmg\Laravue\Commands;

use Illuminate\Support\Str;

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
        $this->files->put( $path, $this->buildController( $model ) );

        $this->info("$date - [ $model ] >> $model"."Controller.php");
    }

    protected function buildController($model, $fields = null)
    {
        $stub = $this->files->get($this->getStub());
        $uniqueMessages = $this->replaceUniqueMessages($stub, $model);
        $field = $this->replaceField($uniqueMessages, $model);
        $beforeIndex = $this->replaceBeforeIndex($field, $model);

        return $this->replaceModel($beforeIndex, $model);
    }

    protected function replaceUniqueMessages($stub, $model)
    {
        if(!$this->option('fields')){
            return str_replace( '{{ unique:messages }}', '' , $stub );
        }
        $fields = $this->getFieldsArray( $this->option('fields') );

        $messageFields = '';
        $uniqueArray = [];
        $first = true;
        foreach ($fields as $key => $value) {
            $isUniqueArray = $this->isUniqueArray($value);
            if( $first && $isUniqueArray ) {
                $first = false;
                $messageFields .= "'$key.unique' => 'Já existe cadastro de $model com ";
                array_push( $uniqueArray, $key );
                continue;
            } 
            if ( $isUniqueArray ) {
                array_push( $uniqueArray, $key );
            }
        }
        $uniqueArrayTitle = [];
        foreach ( $uniqueArray as $unique ) {
            array_push( $uniqueArrayTitle, $this->getTitle( str_replace( "_id", "", $unique ) ) );
        }

        $messageFields .= implode(", ",$uniqueArrayTitle) . " fornecidos.'" . PHP_EOL;
        $message = '';
        if (count( $uniqueArray ) > 0 ) {
            $message .= PHP_EOL . $this->tabs(3);
            $message .= "'messages' => [" . PHP_EOL;
            $message .= $this->tabs(4) . $messageFields;
            $message .= $this->tabs(3) . "]";
        }
        return str_replace( '{{ unique:messages }}', $message , $stub );
    }

    protected function replaceField($stub, $model)
    {
        if(!$this->option('fields')){
            $fieldsParsed = str_replace( '{{ fields }}', "//$"."model->field = $"."request->input('field');" , $stub );
            return str_replace( '{{ rules }}', "// Insira regras aqui" , $fieldsParsed );
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
                $returnFields .= $this->tabs(2);
            } 
            $returnFields .= "$"."model->$key = $"."request->input('$key');"; 
        }
        $returnFields .= $this->tabs(2);
        $parsedfFields = str_replace( '{{ fields }}', $returnFields , $stub );

        //rules
        $returnRules = "";
        $first = true;
        $firstUniqueArray = true;
        foreach ($fields as $key => $value) {
            $type = $this->getType($value);
            // Nullable
            $nullable = $this->hasNullable($value);
            $required = $nullable ? '' : '|required';
            // String Size
            $maxSize = '';
            if( $type == 'string' ) {
                $isNumbers = $this->hasNumber($value);
                if( $isNumbers !== false ) {
                    $maxSize = "|max:" . $isNumbers[0];
                }
            }
            // Unsigned Integer
            $unsigned = '';
            if( $type == 'integer' ) {
                $isUnsigned = $this->isUnsigned($value);
                if( $isUnsigned !== false ) {
                    $unsigned = "|min:0";
                }
            }
            // Unique 
            $isUnique = $this->isUnique($value);
            $table = $this->pluralize( 2, Str::snake( $model ) );
            $unique = $isUnique ? "|unique:$table,$key,' . \$data['id']" : '';
            // Unique array 
            $uniqueArray = '';
            $isUniqueArray = $this->isUniqueArray($value);
            $skipEndingApostrophe = false;
            if( $firstUniqueArray && $isUniqueArray ) {
                $fieldsUnique = $this->getFieldsArray( $this->option('fields') );
                foreach ($fieldsUnique as $k => $v) {
                    $isUniqueInternalArray = $this->isUniqueArray($v);
                    if( $firstUniqueArray && $isUniqueInternalArray ) {
                        $firstUniqueArray = false;
                        $uniqueArray .= "|unique:$table,$k,'" . PHP_EOL;
                        $uniqueArray .= $this->tabs(5) . ". \$data['id'] . ',id,'";
                        continue;
                    } 
                    if ( $isUniqueInternalArray ) {
                        $uniqueArray .= PHP_EOL . $this->tabs(5) . ". '$k,' . \$data['$k'],";
                        $skipEndingApostrophe = true;
                    }
                }
            }

            if( $first ) {
                $first = false;
            } else {
                $returnRules .= PHP_EOL;
                $returnRules .= $this->tabs(4);
            } 

            // ending line rules
            $ending = $isUnique ? ',' : "',";
            if( $isUniqueArray  && !$skipEndingApostrophe ) {
                $ending = "',";
            }
            if( $isUniqueArray  && $skipEndingApostrophe ) {
                $ending = "";
            }

            $returnRules .= "'$key' => '${type}${required}${maxSize}${unique}${uniqueArray}${unsigned}"; 
            $returnRules .= "${ending}"; 
        }

        return str_replace( '{{ rules }}', $returnRules , $parsedfFields );
    }

    protected function replaceBeforeIndex( $stub, $model ) {
        if(!$this->option('fields')){
            return str_replace( '{{ beforeIndex }}', "// public function beforeIndex(\$data) { return \$data; }" , $stub );
        }
        $booleanArray = array();
        $fields = $this->getFieldsArray( $this->option('fields') );
        foreach ($fields as $key => $value) {
            $type = $this->getType($value);
            if( $type === 'boolean' ) {
                array_push( $booleanArray, $key );
            }
        }

        if( count( $booleanArray ) == 0 ){
            return str_replace( '{{ beforeIndex }}', "// public function beforeIndex(\$data) { return \$data; }" , $stub );
        }

        $beforeIndex = "public function beforeIndex(\$data) { " . PHP_EOL;
        $beforeIndex .= $this->tabs(2) . "foreach(\$data as \$item){" . PHP_EOL;
        foreach ( $booleanArray as $field ) {
            $beforeIndex .= $this->tabs(3) . "\$item->$field = \$item->$field == 1 ? \"Sim\" : \"Não\";" . PHP_EOL;
        }
        $beforeIndex .= $this->tabs(2) . "}" . PHP_EOL;
        $beforeIndex .= $this->tabs(2) . "return \$data; " . PHP_EOL;
        $beforeIndex .= $this->tabs(1) . "}" . PHP_EOL;

        return str_replace( '{{ beforeIndex }}', $beforeIndex, $stub );
    }
}
