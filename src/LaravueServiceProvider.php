<?php

namespace wesleyhott\Laravue;

use Illuminate\Support\ServiceProvider;

class LaravueServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register the command if we are using the application via the CLI
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\LaravueApiCommand::class,
                Commands\LaravueBuildCommand::class,
                Commands\LaravueCommand::class,
                Commands\LaravueControllerCommand::class,
                Commands\LaravueDbSeederCommand::class,
                Commands\LaravueFrontCommand::class,

                Commands\LaravueFrontModulePageRoutesCommand::class,
                Commands\LaravueFrontModulePageCommand::class,
                Commands\LaravueFrontModuleIndexCommand::class,
                Commands\LaravueFrontModuleRouteCommand::class,

                Commands\LaravueFrontCreateCommand::class,
                Commands\LaravueFrontDeleteCommand::class,
                Commands\LaravueFrontEditCommand::class,
                Commands\LaravueFrontIndexCommand::class,
                Commands\LaravueLearnCommand::class,
                Commands\LaravueFrontModalCommand::class,
                Commands\LaravueFrontModelCommand::class,
                Commands\LaravueFrontReportCommand::class,
                Commands\LaravueFrontRouteCommand::class,
                Commands\LaravueFrontShowCommand::class,
                Commands\LaravueFrontSideBarCommand::class,
                Commands\LaravueInstallCommand::class,
                Commands\LaravueMigrationCommand::class,
                Commands\LaravueModelCommand::class,
                Commands\LaravueMNCommand::class,
                Commands\LaravueMNApiCommand::class,
                Commands\LaravueMNFrontCommand::class,
                Commands\LaravuePermissionCommand::class,
                Commands\LaravueReportCommand::class,
                Commands\LaravueRequestCommand::class,
                Commands\LaravueResourceCommand::class,
                Commands\LaravueRouteCommand::class,
                Commands\LaravueSeedCommand::class,
                Commands\LaravueServiceCommand::class,
                Commands\LaravueSpatiePermissionCommand::class,
            ]);

            if (!class_exists('CreateMonitorsTable')) {
                // Export the migrations
                $this->publishes([
                    __DIR__ . '/../publishes/database/migrations/create_laravue_tables.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_laravue_tables.php'),
                ], 'migrations');

                // Export Images
                $this->publishes([
                    __DIR__ . '/../publishes/assets/img/logo_header.png' => public_path('img/logo_header.png'),
                    __DIR__ . '/../publishes/assets/img/profile.jpg' => public_path('img/users/avatar/profile.jpg'),
                ], 'public');

                // Export Config
                $this->publishes([
                    __DIR__ . '/../config/config.php' => config_path('laravue.php'),
                ], 'config');
            }
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'laravue');
    }
}
