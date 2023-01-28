<?php

namespace wesleyhott\Laravue\Commands;

use Illuminate\Console\Command;

class LaravueFrontCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:front {model*} 
                                          {--f|fields=}
                                          {--m|module= : determine a module for model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the frontend for the given model.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Module
        $this->createModelDetailPage();
        $this->createModelIndexPage();
        $this->createModulePageRoutes();
        $this->createModulePage();
        $this->createModuleRoute();
        $this->createModuleIndex();
    }

    /**
     * Creates the pages/<<module?>>/<<model>>/forms/<<Model>>DetailForm.vue for the given model.
     *
     * @return void
     */
    protected function createModelDetailPage()
    {
        $this->call('laravue:front-model-detail', [
            'model' => $this->argument('model'),
            '--module' =>  $this->option('module'),
            '--fields' =>  $this->option('fields'),
        ]);
    }

    /**
     * Creates the pages/<<module?>>/<<model>>/<<Model>>IndexPage.vue for the given model.
     *
     * @return void
     */
    protected function createModelIndexPage()
    {
        $this->call('laravue:front-model-index', [
            'model' => $this->argument('model'),
            '--module' =>  $this->option('module'),
            '--fields' =>  $this->option('fields'),
        ]);
    }

    /**
     * Creates the router/modules/index.ts for the given model.
     *
     * @return void
     */
    protected function createModulePageRoutes()
    {
        $this->call('laravue:front-module-page-routes', [
            'model' => $this->argument('model'),
            '--module' =>  $this->option('module'),
        ]);
    }

    /**
     * Creates the router/modules/<<module>>.ts for the given model.
     *
     * @return void
     */
    protected function createModulePage()
    {
        $this->call('laravue:front-module-page', [
            'model' => $this->argument('model'),
            '--module' =>  $this->option('module'),
        ]);
    }

    /**
     * Creates the router/modules/index.ts for the given model.
     *
     * @return void
     */
    protected function createModuleIndex()
    {
        $this->call('laravue:front-module-index', [
            'model' => $this->argument('model'),
            '--module' =>  $this->option('module'),
        ]);
    }

    /**
     * Creates the router/routes.ts for the given model.
     *
     * @return void
     */
    protected function createModuleRoute()
    {
        $this->call('laravue:front-module-route', [
            'model' => $this->argument('model'),
            '--module' =>  $this->option('module'),
        ]);
    }
}
