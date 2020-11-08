<?php

namespace Mpmg\Laravue;

use Illuminate\Support\ServiceProvider;

class LaravueServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register the command if we are using the application via the CLI
        if( $this->app->runningInConsole() ) {
            $this->commands([
                Commands\LaravueApiCommand::class,
                Commands\LaravueBuildCommand::class,
                Commands\LaravueCommand::class,
                Commands\LaravueControllerCommand::class,
                Commands\LaravueDatabaseSeederCommand::class,
                Commands\LaravueFrontCommand::class,
                Commands\LaravueFrontCreateCommand::class,
                Commands\LaravueFrontDeleteCommand::class,
                Commands\LaravueFrontEditCommand::class,
                Commands\LaravueFrontIndexCommand::class,
                Commands\LaravueFrontModalCommand::class,
                Commands\LaravueFrontModelCommand::class,
                Commands\LaravueFrontReportCommand::class,
                Commands\LaravueFrontRouteCommand::class,
                Commands\LaravueFrontShowCommand::class,
                Commands\LaravueFrontSideBarCommand::class,
                Commands\LaravueInstallCommand::class,
                Commands\LaravueMigrationCommand::class,
                Commands\LaravueModelCommand::class,
                Commands\LaravuePermissionCommand::class,
                Commands\LaravueReportCommand::class,
                Commands\LaravueRouteCommand::class,
                Commands\LaravueSeedCommand::class,
            ]);

            // Export the migrations
            if (! class_exists('CreateMonitorsTable')) {
                $this->publishes([
                __DIR__ . '/../database/migrations/create_laravue_tables.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_laravue_tables.php'),
                ], 'migrations');
            }
        }
    }
}