<?php

namespace wesleyhott\Laravue\Commands;

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
        $argumentModel = $this->argument('model');
        $model = is_array($argumentModel) ? trim($argumentModel[0]) : trim($argumentModel);
        $date = now();

        $path = $this->getPath($model);
        $this->files->put($path, $this->buildModel($model));

        $this->info("$date - [ $model ] >> $model" . "ReportController.php");
    }

    protected function replaceField($stub, $model)
    {
        return $this->replaceBeforeIndex($stub, $model);
    }

    protected function replaceBeforeIndex($stub, $model)
    {
        if (!$this->option('fields')) {
            return str_replace('{{ beforeIndex }}', 'return $data;', $stub);
        }

        $maskaredArray = array();
        $maskared = '';

        $beforeIndex = '$reportData = new \Illuminate\Support\Collection();  ' . PHP_EOL;
        $beforeIndex .= PHP_EOL . $this->tabs(2) . 'foreach ( $data as $item ) {' . PHP_EOL;
        $beforeIndex .= '{{ maskared }}' . PHP_EOL;
        $beforeIndex .= $this->tabs(3) . '$linha = array(' . PHP_EOL;

        $fields = $this->getFieldsArray($this->option('fields'));
        foreach ($fields as $key => $value) {
            $title = $this->getTitle($key);
            $type = $this->getType($value);
            if ($type === 'boolean') {
                $beforeIndex .= $this->tabs(4) . "'$title' => \$item->$key == 1 ? 'Sim' : 'Não'," . PHP_EOL;
            } else if ($type === 'date') {
                $beforeIndex .= $this->tabs(4) . "'$title' => date( 'd/m/Y', strtotime( \$item->$key ) )," . PHP_EOL;
            } else if ($type === 'monetario' || $type === 'monetary') {
                $beforeIndex .= $this->tabs(4) . "'$title' => number_format( \$item->$key, 2, ',', '.')," . PHP_EOL;
            } else if ($type === 'cpf') {
                $beforeIndex .= $this->tabs(4) . "'$title' => \$this->mask( \$item->$key, '###.###.###-##' )," . PHP_EOL;
            } else if ($type === 'cnpj') {
                $beforeIndex .= $this->tabs(4) . "'$title' => \$this->mask( \$item->$key, '##.###.###/####-##' )," . PHP_EOL;
            } else if ($type === 'cpfcnpj') {
                array_push($maskaredArray, $key);
                $beforeIndex .= $this->tabs(4) . "'$title' => \$${key}Maskared," . PHP_EOL;
            } else if ($this->isFk($key)) {
                $relation = str_replace('_id', '', $key);
                $keyFields = $this->getModelFieldsFromKey($key);
                $modelField = $this->getSelectLabel($keyFields);
                $beforeIndex .= $this->tabs(4) . "'$title' => isset( \$item->${relation}->${modelField} ) ? \$item->${relation}->${modelField} : '---'," . PHP_EOL;
            } else {
                $beforeIndex .= $this->tabs(4) . "'$title' => \$item->$key," . PHP_EOL;
            }
        }

        $beforeIndex .= $this->tabs(3) . ');' . PHP_EOL;
        $beforeIndex .= $this->tabs(3) . '$reportData->push( $linha );' . PHP_EOL;
        $beforeIndex .= $this->tabs(2) . '}' . PHP_EOL;
        $beforeIndex .= $this->tabs(2) . 'return $reportData;';

        foreach ($maskaredArray as $field) {
            $maskared .= $this->tabs(3) . "\$${field}Maskared = strlen( \$item->$field ) == 11" . PHP_EOL;
            $maskared .= $this->tabs(4) . "? \$this->mask( \$item->$field, '###.###.###-##' )" . PHP_EOL;
            $maskared .= $this->tabs(4) . ": \$this->mask( \$item->$field, '##.###.###/####-##' );" . PHP_EOL;
        }

        $parsed = str_replace('{{ maskared }}', $maskared, $beforeIndex);

        return str_replace('{{ beforeIndex }}', $parsed, $stub);
    }
}
