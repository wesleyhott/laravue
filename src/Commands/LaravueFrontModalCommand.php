<?php

namespace wesleyhott\Laravue\Commands;

class LaravueFrontModalCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:frontmodal {model*} {--f|fields=} {--o|outdocker}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação do frontend forms/Modal.vue';

    /**
     * Tipo de modelo que está sendo criado.
     *
     * @var string
     */
    protected $type = 'front-modal';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setStub('/front/forms/modal');
        $argumentModel = $this->argument('model');
        $model = is_array($argumentModel) ? trim($argumentModel[0]) : trim($argumentModel);
        $date = now();

        $path = $this->getPath($model, "Show");
        $this->files->put($path, $this->buildModel($model));

        $this->info("$date - [ $model ] >> forms/Modal.vue");
    }

    protected function replaceField($stub, $model = null, $schema = null)
    {
        $default = "<div class=\"row\">" . PHP_EOL;
        $default .= $this->tabs(2) .  "<div class=\"col-sm-12\">" . PHP_EOL;
        $default .= $this->tabs(3) .  "{{ fields }}" . PHP_EOL;
        $default .= $this->tabs(2) .  "</div>" . PHP_EOL;
        $default .= $this->tabs(1) .  "</div>";

        if (!$this->option('fields')) {
            $defaultCommented = str_replace('{{ fields }}', '<!-- insert code here. -->', $default);

            return str_replace('{{ fields }}', $defaultCommented, $stub);
        }

        $fields = $this->getFieldsArray($this->option('fields'));

        $parse = "";
        $lastKey = array_key_last($fields);
        $firstKey = array_key_first($fields);
        foreach ($fields as $key => $value) {
            $label = $this->isFk($key) ? $this->getTitle(str_replace("_id", "", $key)) : $this->getTitle($key);

            if (strcmp($firstKey, $key) != 0) {
                $parse .= $this->tabs(1);
            }
            $parse .= "<div class=\"row\">" . PHP_EOL;
            $parse .= $this->tabs(2) .  "<div class=\"col-sm-12\">" . PHP_EOL;
            $parse .= $this->tabs(3) . "<p>" . PHP_EOL;
            $parse .= $this->tabs(4) . "<b>$label</b>" . PHP_EOL;
            $parse .= $this->tabs(4) . "<br/>" . PHP_EOL;
            $parse .= $this->tabs(4) . "{{ model.$key }}" . PHP_EOL;
            $parse .= $this->tabs(3) . "</p>" . PHP_EOL;
            $parse .= $this->tabs(2) .  "</div>" . PHP_EOL;
            $parse .= $this->tabs(1) .  "</div>";
            if (strcmp($lastKey, $key) != 0) {
                $parse .= PHP_EOL;
            }
        }

        return str_replace('{{ fields }}', $parse, $stub);
    }
}
