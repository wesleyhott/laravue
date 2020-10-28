<?php

namespace Mpmg\Laravue\Commands;

use Illuminate\Console\Command;

class LaravueFrontCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:front {model} {--f|fields=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação do frontend para o modelo.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->createIndex();
        $this->createModel();
        $this->createCreate();
        $this->createEdit();
        $this->createReport();
        $this->createModal();
        $this->createShow();
        $this->createDelete();
        $this->createFrontRoutes();
        $this->createFrontSideBar();
    }

    /**
     * Cria o Index.vue para o modelo.
     *
     * @return void
     */
    protected function createIndex()
    {
        $this->call('laravue:frontindex', [
            'model' => $this->argument('model'),
            '--fields' =>  $this->option('fields'),
        ]);
    }

    /**
     * Cria o forms/Model.vue para o modelo.
     *
     * @return void
     */
    protected function createModel()
    {
        $this->call('laravue:frontmodel', [
            'model' => $this->argument('model'),
            '--fields' =>  $this->option('fields'),
        ]);
    }

    /**
     * Cria o Create.vue para o modelo.
     *
     * @return void
     */
    protected function createCreate()
    {
        $this->call('laravue:frontcreate', [
            'model' => $this->argument('model'),
        ]);
    }

    /**
     * Cria o Edit.vue para o modelo.
     *
     * @return void
     */
    protected function createEdit()
    {
        $this->call('laravue:frontedit', [
            'model' => $this->argument('model'),
        ]);
    }

    /**
     * Cria o Report.vue para o modelo.
     *
     * @return void
     */
    protected function createReport()
    {
        $this->call('laravue:frontreport', [
            'model' => $this->argument('model'),
        ]);
    }

    /**
     * Cria o forms/Modal.vue para o modelo.
     *
     * @return void
     */
    protected function createModal()
    {
        $this->call('laravue:frontmodal', [
            'model' => $this->argument('model'),
            '--fields' =>  $this->option('fields'),
        ]);
    }

    /**
     * Cria o Show.vue para o modelo.
     *
     * @return void
     */
    protected function createShow()
    {
        $this->call('laravue:frontshow', [
            'model' => $this->argument('model'),
        ]);
    }

    /**
     * Cria o Delete.vue para o modelo.
     *
     * @return void
     */
    protected function createDelete()
    {
        $this->call('laravue:frontdelete', [
            'model' => $this->argument('model'),
        ]);
    }

    /**
     * Cria rotas no frontend para o modelo.
     *
     * @return void
     */
    protected function createFrontRoutes()
    {
        $this->call('laravue:frontroute', [
            'model' => $this->argument('model'),
        ]);
    }

    /**
     * Cria menu no frontend para o modelo.
     *
     * @return void
     */
    protected function createFrontSideBar()
    {
        $this->call('laravue:frontsidebar', [
            'model' => $this->argument('model'),
        ]);
    }
}
