<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MpmgFront extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mpmg:front {model} {--f|fields=}';

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
        $this->call('mpmg:index', [
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
        $this->call('mpmg:frontmodel', [
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
        $this->call('mpmg:frontcreate', [
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
        $this->call('mpmg:frontedit', [
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
        $this->call('mpmg:frontreport', [
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
        $this->call('mpmg:frontmodal', [
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
        $this->call('mpmg:frontshow', [
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
        $this->call('mpmg:frontdelete', [
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
        $this->call('mpmg:frontroute', [
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
        $this->call('mpmg:frontsidebar', [
            'model' => $this->argument('model'),
        ]);
    }
}
