<?php

namespace wesleyhott\Laravue\Commands;

class LaravueFrontReportCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:frontreport {model*} {--o|outdocker}';

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
        $argumentModel = $this->argument('model');
        $model = is_array( $argumentModel ) ? trim( $argumentModel[0] ) : trim( $argumentModel ); 
        $date = now();

        $path = $this->getFrontPath($model, "Report");
        $this->files->put($path, $this->buildModel($model));

        $this->info("$date - [ $model ] >> Report.vue");
    }
}
