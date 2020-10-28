<?php

namespace App\Console\Commands;

class MpmgFrontShow extends MpmgCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mpmg:frontshow {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação do frontend Show.vue';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setStub('/front/show');
        $model = trim($this->argument('model'));
        $date = now();

        $path = $this->getFrontPath($model, "Show");
        $this->files->put($path, $this->buildModel($model));

        $this->info("$date - [ $model ] >> Show.vue");
    }
}
