<?php

namespace App\Console\Commands;

class MpmgFrontReport extends MpmgCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mpmg:frontreport {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação do frontend Report.vue';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setStub('/front/report');
        $model = trim($this->argument('model'));
        $date = now();

        $path = $this->getFrontPath($model, "Report");
        $this->files->put($path, $this->buildModel($model));

        $this->info("$date - [ $model ] >> Report.vue");
    }
}
