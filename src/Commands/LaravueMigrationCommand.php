<?php

namespace Mpmg\Laravue\Commands;

use Illuminate\Support\Str;

class LaravueMigrationCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:migration {model} {--f|fields=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação de migration nos padrões do Laravue.';

    /**
     * Tipo de modelo que está sendo criado.
     *
     * @var string
     */
    protected $type = 'migration';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setStub('/migration');
        $model = trim($this->argument('model'));
        $date = now();

        $path = $this->getPath($model);
        $this->files->put( $path, $this->buildMigration( $model ) );

        $prefix = date('Y_m_d_His');
        $name = Str::snake( $this->pluralize( 2, trim($this->argument('model') ) ) );
        $this->info("$date - [ $model ] >> $prefix"."_create_$name"."_table.php");
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
            $type = $this->getType($value);
            // Nullable
            $isNullable = $this->hasNullable($value);
            $nullable = $isNullable ? '->nullable()' : '';
            // String Size
            $size = '';
            if( $type == 'string' ) {
                $isNumbers = $this->hasNumber($value);
                if( $isNumbers !== false ) {
                    $size = ", " . $isNumbers[0];
                }
            }
            if( $first ) {
                $first = false;
            } else {
                $returnFields .= PHP_EOL;
                $returnFields .= $this->tabs(3);
            }

            if( $this->isFk( $key ) ) {
                $referenced_table = $this->pluralize( 2, str_replace( "_id", "", $key ) );

                $returnFields .= "$"."table->$type('$key')" . PHP_EOL;
                $returnFields .= $this->tabs(4) . "// ->nullable()" . PHP_EOL;
                $returnFields .= $this->tabs(4) . "->unsigned();" . PHP_EOL;
                $returnFields .= $this->tabs(3) . "\$table->foreign('$key')" . PHP_EOL;
                $returnFields .= $this->tabs(4) . "->references('id')" . PHP_EOL;
                $returnFields .= $this->tabs(4) . "->on('$referenced_table')" . PHP_EOL;
                $returnFields .= $this->tabs(4) . "->onDelete('cascade'); // 'set null' if nullable";
            } else {
                $returnFields .= "$"."table->$type('$key'$size)$nullable;";
            }
        }

        return str_replace( '{{ fields }}', $returnFields , $stub );
    }
}
