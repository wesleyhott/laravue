<?php

namespace Mpmg\Laravue;

use Illuminate\Support\ServiceProvider;

class LaravueServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands([
            Commands\MpmgApi::class,
            Commands\MpmgBuild::class,
            Commands\MpmgCommand::class,
            Commands\MpmgController::class,
            Commands\MpmgDatabaseSeeder::class,
            Commands\MpmgFront::class,
            Commands\MpmgFrontCreate::class,
            Commands\MpmgFrontDelete::class,
            Commands\MpmgFrontEdit::class,
            Commands\MpmgFrontIndex::class,
            Commands\MpmgFrontModal::class,
            Commands\MpmgFrontModel::class,
            Commands\MpmgFrontReport::class,
            Commands\MpmgFrontRoute::class,
            Commands\MpmgFrontShow::class,
            Commands\MpmgFrontSideBar::class,
            Commands\MpmgMigration::class,
            Commands\MpmgModel::class,
            Commands\MpmgPermission::class,
            Commands\MpmgReport::class,
            Commands\MpmgRoute::class,
            Commands\MpmgSeed::class,
        ]);
    }
}