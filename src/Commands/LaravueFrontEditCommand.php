<?php

namespace Mpmg\Laravue\Commands;

class LaravueFrontEditCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:frontedit {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação do frontend Edit.vue';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setStub('/front/edit');
        $model = trim($this->argument('model'));
        $date = now();

        $path = $this->getFrontPath($model, "Edit");
        $this->files->put($path, $this->buildModel($model));

        $this->info("$date - [ $model ] >> Edit.vue");
    }
}
