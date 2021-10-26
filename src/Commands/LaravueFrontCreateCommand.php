<?php

namespace wesleyhott\Laravue\Commands;

class LaravueFrontCreateCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:frontcreate {model*} {--o|outdocker}';

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
        $argumentModel = $this->argument('model');
        $model = is_array( $argumentModel ) ? trim( $argumentModel[0] ) : trim( $argumentModel ); 
        $date = now();

        $path = $this->getFrontPath($model, "Create");
        $this->files->put($path, $this->sortImports($this->buildModel($model)));

        $this->info("$date - [ $model ] >> Create.vue");
    }
}
