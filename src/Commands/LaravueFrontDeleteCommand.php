<?php

namespace Mpmg\Laravue\Commands;

class LaravueFrontDeleteCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:frontdelete {model} {--o|outdocker}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação do frontend Delete.vue';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setStub('/front/delete');
        $model = trim($this->argument('model'));
        $date = now();

        $path = $this->getFrontPath($model, "Delete");
        $this->files->put($path, $this->buildModel($model));

        $this->info("$date - [ $model ] >> Delete.vue");
    }
}
