<?php

namespace Mpmg\Laravue\Commands;

class LaravueReportCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:report {model*} {--f|fields=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria um novo controlador de relatório nos padrões do Laravue.';

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
        $model = trim($this->argument('model')[0]);
        $date = now();

        $path = $this->getPath($model);
        $this->files->put( $path, $this->buildModel( $model ) );

        $this->info("$date - [ $model ] >> $model"."ReportController.php");
    }

    protected function replaceField($stub, $model)
    {
        return $this->replaceBeforeIndex($stub, $model);
    }

    protected function replaceBeforeIndex( $stub, $model ) {
        $beforeIndex = PHP_EOL;
        $beforeIndex .= $this->tabs(2) . "// os nomes são os definidos em mapColumns() do modelo." . PHP_EOL;
        $beforeIndex .= $this->tabs(2) . "// Para remover colunas use: unset(\$item[\"CampoDesnecessario\"]);" . PHP_EOL;
        $beforeIndex .= $this->tabs(2) . "// foreach(\$data as \$item) {" . PHP_EOL;
        $beforeIndex .= $this->tabs(3) . "// Transformando colunas" . PHP_EOL;
        $beforeIndex .= $this->tabs(3) . "// \$item[\"pk_id\"] = ( \App\Models\ModelPk::find( \$item[\"pk_id\"] ) )->name;" . PHP_EOL;
        $beforeIndex .= $this->tabs(3) . "// \$item[\"created_at\"] = date( 'd/m/Y', strtotime( \$item[\"created_at\"] ) );" . PHP_EOL;
        $beforeIndex .= $this->tabs(3) . "// \$item[\"booleanFiled\"] = \$item[\"booleanFiled\"] == 1 ? \"Sim\" : \"Não\";" . PHP_EOL;
        $beforeIndex .= $this->tabs(2) . "// }" . PHP_EOL;

        if(!$this->option('fields')){
            return str_replace( '{{ beforeIndex }}', $beforeIndex , $stub );
        }

        $booleanArray = array();
        $dateArray = array();
        $fields = $this->getFieldsArray( $this->option('fields') );
        foreach ($fields as $key => $value) {
            $type = $this->getType($value);
            if( $type === 'boolean' ) {
                array_push( $booleanArray, $key );
            }
            if( $type === 'date' ) {
                array_push( $dateArray, $key );
            }
        }

        if( count( $booleanArray ) == 0 ){
            return str_replace( '{{ beforeIndex }}', '' , $stub );
        }

        $beforeIndex = PHP_EOL;
        $beforeIndex .= $this->tabs(2) . "// os nomes são os definidos em mapColumns() do modelo." . PHP_EOL;
        $beforeIndex .= $this->tabs(2) . "// Para remover colunas use: unset(\$item[\"CampoDesnecessario\"]);" . PHP_EOL;
        $beforeIndex .= $this->tabs(2) . "foreach(\$data as \$item) {" . PHP_EOL;
        foreach ( $booleanArray as $field ) {
            $title = ucwords( $field );
            $beforeIndex .= $this->tabs(3) . "\$item[\"$title\"] = \$item[\"$title\"] == 1 ? \"Sim\" : \"Não\";" . PHP_EOL;
        }
        foreach ( $dateArray as $field ) {
            $title = ucwords( $field );
            $beforeIndex .= $this->tabs(3) . "\$item[\"$title\"] = date( 'd/m/Y', strtotime( \$item[\"$title\"] ) );" . PHP_EOL;
        }
        $beforeIndex .= $this->tabs(2) . "}" . PHP_EOL;

        return str_replace( '{{ beforeIndex }}', $beforeIndex, $stub );
    }
}
