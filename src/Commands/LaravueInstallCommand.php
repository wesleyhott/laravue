<?php

namespace Mpmg\Laravue\Commands;

class LaravueInstallCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Laravue instalations';

    /**
     * Tipo de modelo que está sendo criado.
     *
     * @var string
     */
    protected $type = 'install';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->makeDotEnvExample();
        $this->makeDotEnv();
        $this->makeUserSeeder();
        $this->makeOauthClientSeeder();
        $this->makeTaskGroupSeeder();
        $this->makeTaskStatusSeeder();
        $this->makeProjectModuleSeeder();
        $this->makeDataBaseSeeder();
        $this->makeLaravueSeeder();
        $this->makeActiveFilter();
        $this->makeActiveEditionFilter();
        $this->makeOrderByIdFilter();
        $this->makeOrderByNameFilter();
        $this->makeUserIdFilter();
        $this->makeAbstractFilter();
        $this->makeLaravueFilter();
        $this->makeLaravueModel();
        $this->makePtBrLocale();
        $this->makeLaravueConfigApp();
        $this->makeLaravueConfigServer();
        $this->makeLaravueController();
        $this->makeLaravueAccessTokenController();
        $this->makeLaravueRouteApi();

        $this->makeKernel();
        $this->makeProviderPhpOffice();
        // Dependences
        $this->publishSpatiePermission();
        $this->publishAdLdap();
        $this->publishSubFissionCas();
        $this->publishLogViewer();
        $this->publishInterventionImage();
    }

    protected function makeDotEnvExample() {
        $this->setStub('install/.env-example');
        $fileName = ".env.example";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp);

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeUserSeeder() {
        $this->setStub('install/seeder-user');
        $fileName = "database/seeders/UserSeeder.php";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp);

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeOauthClientSeeder() {
        $this->setStub('install/seeder-oauth-client');
        $fileName = "database/seeders/OauthClientSeeder.php";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp);

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeTaskGroupSeeder() {
        $this->setStub('install/seeder-taskgroup');
        $fileName = "database/seeders/TaskGroupSeeder.php";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp);

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeTaskStatusSeeder() {
        $this->setStub('install/seeder-taskstatus');
        $fileName = "database/seeders/TaskStatusSeeder.php";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp);

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeProjectModuleSeeder() {
        $this->setStub('install/seeder-project-module');
        $fileName = "database/seeders/ProjectModuleSeeder.php";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp);

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeDataBaseSeeder() {
        $this->setStub('install/seeder-database');
        $fileName = "database/seeders/DatabaseSeeder.php";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp);

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeLaravueSeeder() {
        $this->setStub('install/seeder');
        $fileName = "database/seeders/LaravueSeeder.php";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp);

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeDotEnv() {
        $this->setStub('install/.env');
        $fileName = ".env";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp);

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeActiveFilter() {
        $this->setStub('install/filter-active');
        $fileName = "Filters/Active.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeActiveEditionFilter() {
        $this->setStub('install/filter-active-edition');
        $fileName = "Filters/ActiveEdition.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeOrderByIdFilter() {
        $this->setStub('install/filter-orderby-id');
        $fileName = "Filters/OrderById.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeOrderByNameFilter() {
        $this->setStub('install/filter-orderby-name');
        $fileName = "Filters/OrderByName.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeUserIdFilter() {
        $this->setStub('install/filter-user-id');
        $fileName = "Filters/UserId.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeAbstractFilter() {
        $this->setStub('install/filter-abstract');
        $fileName = "Filters/AbstractFilter.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeLaravueFilter() {
        $this->setStub('install/filter');
        $fileName = "Filters/LaravueFilter.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeLaravueModel() {
        $this->setStub('install/model');
        $fileName = "Models/LaravueModel.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeLaravueMonitorModel() {
        $this->setStub('install/model-monitor');
        $fileName = "Models/Monitor.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makePtBrLocaleAuth() {
        $this->setStub('install/locale-auth');
        $fileName = "resources/lang/pt-BR/auth.php";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makePtBrLocalePagination() {
        $this->setStub('install/locale-pagination');
        $fileName = "resources/lang/pt-BR/pagination.php";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makePtBrLocalePasswords() {
        $this->setStub('install/locale-passwords');
        $fileName = "resources/lang/pt-BR/passwords.php";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makePtBrLocaleValidation() {
        $this->setStub('install/locale-validation');
        $fileName = "resources/lang/pt-BR/validation.php";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makePtBrLocalePtBr() {
        $this->setStub('install/locale-validation');
        $fileName = "resources/lang/pt-BR.json";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makePtBrLocale() {
        $this->makePtBrLocaleAuth();
        $this->makePtBrLocalePagination();
        $this->makePtBrLocalePasswords();
        $this->makePtBrLocaleValidation();
        $this->makePtBrLocalePtBr();
    }

    protected function makeLaravueConfigApp() {
        $this->setStub('install/config-app');
        $fileName = "config/app.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeLaravueConfigServer() {
        $this->setStub('install/config-server');
        $fileName = "config/server.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeLaravueController() {
        $this->setStub('install/controller');
        $fileName = "Http/Controllers/LaravueController.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeLaravueAccessTokenController() {
        $this->setStub('install/controller-access-token');
        $fileName = "Http/Controllers/Laravue/AccessTokenController.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeLaravueRouteApi() {
        $this->setStub('install/route-api');
        $fileName = "routes/api.php";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeKernel() {
        $this->setStub('install/kernel');
        $fileName = "Http/Kernel.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeProviderPhpOffice() {
        $this->setStub('install/provider-phpoffice');
        $fileName = "Providers/PhpSpreadsheetServiceProvider.php";
        $path = $this->makePath( $fileName );
        
        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function publishSpatiePermission() {
        $date = now();
        $this->info("$date - [ Publishing ] >> PermissionServiceProvider");
        $this->call('vendor:publish',[
            '--provider' =>  'Spatie\Permission\PermissionServiceProvider',
        ]);
    }

    protected function publishAdLdap() {
        $date = now();
        $this->info("$date - [ Publishing ] >> AdldapServiceProvider");
        $this->call('vendor:publish',[
            '--provider' =>  'Adldap\Laravel\AdldapServiceProvider',
        ]);
    }

    protected function publishSubFissionCas() {
        $date = now();
        $this->info("$date - [ Publishing ] >> CasServiceProvider");
        $this->call('vendor:publish',[
            '--provider' =>  'Subfission\Cas\CasServiceProvider',
        ]);
    }

    protected function publishLogViewer() {
        $date = now();
        $this->info("$date - [ Publishing ] >> LogViewerServiceProvider");
        $this->call('vendor:publish',[
            '--provider' =>  'Arcanedev\LogViewer\LogViewerServiceProvider',
        ]);
    }

    protected function publishInterventionImage() {
        $date = now();
        $this->info("$date - [ Publishing ] >> ImageServiceProviderLaravelRecent");
        $this->call('vendor:publish',[
            '--provider' =>  'Intervention\Image\ImageServiceProviderLaravelRecent',
        ]);
    }

}
