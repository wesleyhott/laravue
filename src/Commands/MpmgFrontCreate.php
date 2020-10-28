<?php

namespace App\Console\Commands;

class MpmgFrontCreate extends MpmgCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mpmg:frontcreate {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação do frontend Create.vue';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setStub('/front/create');
        $model = trim($this->argument('model'));
        $date = now();

        $path = $this->getFrontPath($model, "Create");
        $this->files->put($path, $this->sortImports($this->buildModel($model)));

        $this->info("$date - [ $model ] >> Create.vue");
    }
}
