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
     * Variávies de Instalação.
     *
     * @var string
     */
    // .env
    protected $applicationName;
    protected $databaseName;
    protected $databaseUserName;
    protected $databaseUserPassword;

    protected $casHostName;
    protected $casLogoutUrl;
    protected $casLogoutRedirect;
    protected $casService;

    protected $ldapHosts;
    protected $ldapBaseDn;

    protected $serverUriIndex;
    // seeder user
    protected $seederUserName;
    protected $seederUserEmail;
    protected $seederUserPassword;
    // docker
    protected $dockerProxy;


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Setting context
        $this->promptChoices();

        // Docker
        $this->makeDockerFile();
        $this->makeDockerPhpIni();
        $this->makeDockerCompose();
        $this->makeDockerNginxConf();
        $this->makeDockerMssqlCreateDB();
        $this->makeDockerMssqlEntryPoint();
        $this->makeDockerMssqlRunInitialization();

        // .env
        $this->makeDotEnvExample();
        $this->makeDotEnv();
        // .gitignore
        $this->makeDotGitIgnoreStorageApp();
        $this->makeDotGitIgnoreStorageAppReports();
        $this->makeDotGitIgnorePublicImgAvatar();
        // migration
        $this->makeUserMigration();
        // Seeder
        $this->makeUserSeeder();
        $this->makeFuncionarioMPSeeder();
        $this->makeOauthClientSeeder();
        $this->makeTaskGroupSeeder();
        $this->makeTaskStatusSeeder();
        $this->makeProjectModuleSeeder();
        $this->makeTaskSeeder();
        $this->makeVersionSeeder();
        $this->makeDatabaseSeeder();
        $this->makeLaravueSeeder();
        // Filters
        $this->makeAtivoEdicaoFilter();
        $this->makeFieldLikeFilter();
        $this->makeModelFilter();
        $this->makeOrderByIdFilter();
        $this->makeOrderByNameFilter();
        $this->makeAbstractFilter();
        $this->makeLaravueFilter();
        // Events
        $this->makeEventMonitor();
        // Listeners
        $this->makeListenerMonitor();
        // Models
        $this->makeLaravueModel();
        $this->makeModelMonitor();
        $this->makeModelUser();
        $this->makeModelRole();
        $this->makeModelPermission();
        $this->makeModelTaskGroup();
        $this->makeModelTaskStatus();
        $this->makeModelProjectModule();
        $this->makeModelTask();
        $this->makeModelVersion();
        $this->makeModelReport();
        // Resource
        $this->makeViewPdfBlade();
        // Config
        $this->makeLaravueConfigApp();
        $this->makeLaravueConfigAuth();
        $this->makeLaravueConfigServer();
        // Provider
        $this->makeLaravueProviderApp();
        $this->makeLaravueProviderAuth();
        // Controller
        $this->makeLaravueController();
        $this->makeControllerTaskGroup();
        $this->makeControllerTaskStatus();
        $this->makeControllerProjectModule();
        $this->makeControllerTask();
        $this->makeControllerVersion();
        $this->makeControllerCurrentVersion();
        $this->makeControllerRoadMap();
        $this->makeLaravueAccessTokenController();
        $this->makeControllerMonitor();
        $this->makeControllerPermission();
        $this->makeControllerRolePermission();
        $this->makeControllerRole();
        $this->makeControllerUserPermission();
        $this->makeControllerUserRole();
        $this->makeControllerUser();
        // Report Controller
        $this->makeReportControllerLaravue();
        $this->makeReportControllerMonitor();
        $this->makeReportControllerTask();
        $this->makeReportControllerUser();
        // Middlewares
        $this->makeMiddlewareLaravueAuthenticate();
        $this->makeMiddlewareLaravueSetHeaders();
        // Route
        $this->makeLaravueRouteApi();
        // General
        $this->makeKernel();
        $this->makeProviderPhpOffice();
        // Dependences
        $this->publishSpatiePermission();
        $this->publishAdLdap();
        $this->publishSubFissionCas();
        $this->publishLogViewer();
        $this->publishInterventionImage();
        $this->publishInternacionalization();
        $this->publishLaravue();
    }

    protected function promptChoices() {
        $this->newLine();
        $this->line('LARAVUE INSTALAÇÃO');
        $this->line('-------------------');
        $this->newLine();

        $this->applicationName = $this->ask('Qual o nome da aplicação? [Laravue]');
        if( !isset($this->applicationName) ) {
            $this->applicationName = "Laravue";
        }

        $this->databaseName = $this->ask('Qual o nome do banco de dados? [dbsLaravue]');
        if( !isset($this->databaseName) ) {
            $this->databaseName = "dbsLaravue";
        }
        $this->databaseUserName = $this->ask('Qual o nome do usuário do banco de dados? [sa]');
        if( !isset($this->databaseUserName) ) {
            $this->databaseUserName = "sa";
        }
        $this->databaseUserPassword = $this->ask('Qual a senha do usuário do banco de dados? [Abcd12345]');
        if( !isset($this->databaseUserPassword) ) {
            $this->databaseUserPassword = "Abcd12345";
        }
        
        $this->accessManagement = $this->choice(
            'Qual o gerenciador de acesso?',
            ['Keycloak', 'CAS'],
            0
        );
        
        if( $this->accessManagement == 'CAS' ) {
            $this->casHostName = $this->ask('Qual o CAS hostname?');
            $this->casLogoutUrl = $this->ask('Qual o CAS URL?');
            $this->casLogoutRedirect = $this->ask('Qual o CAS logout redirect?');
            $this->casService = $this->ask('Qual o CAS service?');
        }

        $this->permissionManagement = $this->choice(
            'Qual o gerenciador de permissões?',
            ['Microsoft Azure', 'LDAP'],
            0
        );

        if( $this->permissionManagement == 'LDAP' ) {
            $this->ldapHosts = $this->ask('Qual o LDAP hosts?');
            $this->ldapBaseDn = $this->ask('Qual o LDAP base Dn?');
        }

        $this->serverUriIndex = $this->ask('Qual o Server URI index? [3]');
        if( !isset($this->serverUriIndex) ) {
            $this->serverUriIndex = "3";
        }

        $this->seederUserName = $this->ask('Qual o nome do Usuário Administrador? [Administrador]');
        if( !isset($this->seederUserName) ) {
            $this->seederUserName = "Administrador";
        }

        $this->seederUserEmail = $this->ask('Qual o email do Usuário Administrador do sistema? [administrador@mpmg.mp.br]');
        if( !isset($this->seederUserEmail) ) {
            $this->seederUserEmail = "administrador@mpmg.mp.br";
        }

        $this->seederUserPassword = $this->ask('Qual é a senha Usuário Administrador do sistema? [05121652Administrador@mpmg.mp.br]');
        if( !isset($this->seederUserPassword) ) {
            $this->seederUserPassword = "05121652Administrador@mpmg.mp.br";
        }

        $this->dockerProxy = $this->ask('Qual é o proxy da rede? [proxy@proxy.br]');
        if( !isset($this->dockerProxy) ) {
            $this->dockerProxy = "proxy@proxy.br";
        }
    }

    protected function promptChoicesTest() {
        $this->applicationName = "Laravue";
        $this->databaseName = "dbsLaravue";
        $this->databaseUserName = "sa";
        $this->databaseUserPassword = "Abcd12345";        
        $this->accessManagement = 'CAS'; //['Keycloak', 'CAS']

        if( $this->accessManagement == 'CAS' ) {
            $this->casHostName = 'casHostName';
            $this->casLogoutUrl = 'casLogoutUrl';
            $this->casLogoutRedirect = 'casLogoutRedirect';
            $this->casService = 'casService';
        }

        $this->permissionManagement = 'LDAP'; //['Microsoft Azure', 'LDAP']

        if( $this->permissionManagement == 'LDAP' ) {
            $this->ldapHosts = 'ldapHosts';
            $this->ldapBaseDn = 'ldapBaseDn';
        }

        $this->serverUriIndex = "3";

        $this->seederUserName = "Administrador";
        $this->seederUserEmail = "administrador@mpmg.mp.br";
        $this->seederUserPassword = "05121652Administrador@mpmg.mp.br";
        $this->dockerProxy = "proxy@proxy.br";
    }

    protected function replaceChoices( $choices ) {
        $stub = $this->files->get( $this->getStub() );

        foreach( $choices as $key => $choice ) { 
            $stub = str_replace("{{ $key }}", $choice, $stub);
        }

        return $stub;
    }

    protected function makeDockerFile() {
        $this->setStub('/install/docker/dockerfile');
        $date = now();

        $path = $this->getDockerPath("Dockerfile");

        $choices = array(
            "dockerProxy" => $this->dockerProxy,
        );
        $stub = $this->replaceChoices( $choices );

        $this->files->put( $path, $stub );

        $this->info("$date - [ Installing ] >> docker/Dockerfile");
    }

    protected function makeDockerCompose() {
        $this->setStub('/install/docker/docker-compose');
        $date = now();

        $path = $this->getDockerPath("docker-compose.yml");

        $choices = array(
            "applicationName" => strtolower( $this->applicationName ),
            "databaseUserPassword" => $this->databaseUserPassword,
        );
        $stub = $this->replaceChoices( $choices );

        $this->files->put( $path, $stub );

        $this->info("$date - [ Installing ] >> docker/docker-compose.yml");
    }

    protected function makeDockerPhpIni() {
        $this->setStub('/install/docker/php-ini');
        $date = now();

        $path = $this->getDockerPath("php/local.ini");
        $stub = $this->files->get( $this->getStub() );

        $this->files->put( $path, $stub );

        $this->info("$date - [ Installing ] >> docker/php/local.ini");
    }

    protected function makeDockerNginxConf() {
        $this->setStub('/install/docker/nginx-conf');
        $date = now();

        $appName = strtolower( $this->applicationName );
        $path = $this->getDockerPath("nginx/conf.d/$appName.conf");

        $choices = array(
            "applicationName" => $appName,
        );
        $stub = $this->replaceChoices( $choices );

        $this->files->put( $path, $stub );

        $this->info("$date - [ Installing ] >> docker/docker-compose.yml");
    }

    protected function makeDockerMssqlEntryPoint() {
        $this->setStub('/install/docker/mssql-entrypoint');
        $date = now();
        $path = $this->getDockerPath("mssql/usr/src/entrypoint.sh");
        $stub = $this->files->get( $this->getStub() );
        $this->files->put( $path, $stub );
        $this->info("$date - [ Installing ] >> docker/mssql/usr/src/entrypoint.sql");
    }

    protected function makeDockerMssqlCreateDB() {
        $this->setStub('/install/docker/mssql-create-database');
        $date = now();

        $path = $this->getDockerPath("mssql/usr/src/create-database.sql");

        $choices = array(
            "applicationName" => strtoupper( $this->applicationName ),
        );
        $stub = $this->replaceChoices( $choices );

        $this->files->put( $path, $stub );

        $this->info("$date - [ Installing ] >> docker/mssql/usr/src/create-database.sql");
    }

    protected function makeDockerMssqlRunInitialization() {
        $this->setStub('/install/docker/mssql-run-initialization');
        $date = now();

        $path = $this->getDockerPath("mssql/usr/src/run-initialization.sh");

        $choices = array(
            "databaseUserName" => $this->databaseUserName,
            "databaseUserPassword" => $this->databaseUserPassword,
        );
        $stub = $this->replaceChoices( $choices );

        $this->files->put( $path, $stub );

        $this->info("$date - [ Installing ] >> docker/mssql/usr/src/run-initialization.sh");
    }

    protected function makeDotEnvExample() {
        $this->setStub('install/.env-example');
        $fileName = ".env.example";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp);

        $choices = array(
            "applicationName" => $this->applicationName,
            "databaseName" => $this->databaseName,
            "databaseUserName" => $this->databaseUserName,
            "databaseUserPassword" => $this->databaseUserPassword,
            "casHostName" => $this->casHostName,
            "casLogoutUrl" => $this->casLogoutUrl,
            "casLogoutRedirect" => $this->casLogoutRedirect,
            "casService" => $this->casService,
            "ldapHosts" => $this->ldapHosts,
            "ldapBaseDn" => $this->ldapBaseDn,
            "serverUriIndex" => $this->serverUriIndex,
        );
        $stub = $this->replaceChoices( $choices );

        $this->files->put( $path, $stub );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeDotEnv() {
        $this->setStub('install/.env');
        $fileName = ".env";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp);

        $choices = array(
            "applicationName" => $this->applicationName,
            "databaseName" => $this->databaseName,
            "databaseUserName" => $this->databaseUserName,
            "databaseUserPassword" => $this->databaseUserPassword,
            "casHostName" => $this->casHostName,
            "casLogoutUrl" => $this->casLogoutUrl,
            "casLogoutRedirect" => $this->casLogoutRedirect,
            "casService" => $this->casService,
            "ldapHosts" => $this->ldapHosts,
            "ldapBaseDn" => $this->ldapBaseDn,
            "serverUriIndex" => $this->serverUriIndex,
        );
        $stub = $this->replaceChoices( $choices );

        $this->files->put( $path, $stub );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeDotGitIgnoreStorageApp() {
        $this->setStub('install/.gitignore-storage-app');
        $fileName = "storage/app/.gitignore";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp);

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeDotGitIgnoreStorageAppReports() {
        $this->setStub('install/.gitignore-storage-app-reports');
        $fileName = "storage/app/reports/.gitignore";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp);

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeDotGitIgnorePublicImgAvatar() {
        $this->setStub('install/.gitignore-public-img-avatar');
        $fileName = "public/img/avatar/.gitignore";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp);

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeUserMigration() {
        $this->setStub('install/migration-user');
        $fileName = "database/migrations/2014_10_12_000000_create_users_table.php";
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

        $choices = array(
            "seederUserName" => $this->seederUserName,
            "seederUserEmail" => $this->seederUserEmail,
            "seederUserPassword" => $this->seederUserPassword,
        );
        $stub = $this->replaceChoices( $choices );

        $this->files->put( $path, $stub );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeFuncionarioMPSeeder() {
        $this->setStub('install/seeder-funcionario-mp');
        $fileName = "database/seeders/FuncionarioMpSeeder.php";
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

    protected function makeTaskSeeder() {
        $this->setStub('install/seeder-task');
        $fileName = "database/seeders/TaskSeeder.php";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp);

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }
    
    protected function makeVersionSeeder() {
        $this->setStub('install/seeder-version');
        $fileName = "database/seeders/VersionSeeder.php";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp);

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeDatabaseSeeder() {
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

    protected function makeAtivoEdicaoFilter() {
        $this->setStub('install/filter-ativo-edicao');
        $fileName = "Filters/AtivoEdicao.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeFieldLikeFilter() {
        $this->setStub('install/filter-field-like');
        $fileName = "Filters/FieldLike.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeModelFilter() {
        $this->setStub('install/filter-model');
        $fileName = "Filters/ModelFilter.php";
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

    protected function makeModelMonitor() {
        $this->setStub('install/model-monitor');
        $fileName = "Models/Monitor.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeEventMonitor() {
        $this->setStub('install/event-monitor');
        $fileName = "Events/Monitor.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeListenerMonitor() {
        $this->setStub('install/listener-monitor');
        $fileName = "Listeners/MonitorListener.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }
    
    protected function makeModelUser() {
        $this->setStub('install/model-user');
        $fileName = "Models/User.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeModelRole() {
        $this->setStub('install/model-role');
        $fileName = "Models/Role.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeModelPermission() {
        $this->setStub('install/model-permission');
        $fileName = "Models/Permission.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeModelTaskGroup() {
        $this->setStub('install/model-taskgroup');
        $fileName = "Models/TaskGroup.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeModelTaskStatus() {
        $this->setStub('install/model-taskstatus');
        $fileName = "Models/TaskStatus.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeModelProjectModule() {
        $this->setStub('install/model-project-module');
        $fileName = "Models/ProjectModule.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeModelTask() {
        $this->setStub('install/model-task');
        $fileName = "Models/Task.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeModelVersion() {
        $this->setStub('install/model-version');
        $fileName = "Models/Version.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeModelReport() {
        $this->setStub('install/model-report');
        $fileName = "Models/Report.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeViewPdfBlade() {
        $this->setStub('install/resource-view-pdf-report');
        $fileName = "resources/views/reports/default_pdf_report.blade.php";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeLaravueConfigApp() {
        $this->setStub('install/config-app');
        $fileName = "config/app.php";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeLaravueConfigAuth() {
        $this->setStub('install/config-auth');
        $fileName = "config/auth.php";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeLaravueConfigServer() {
        $this->setStub('install/config');
        $fileName = "config/laravue.php";
        $outsideApp = true;
        $path = $this->makePath( $fileName, $outsideApp );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeLaravueProviderApp() {
        $this->setStub('install/provider-app');
        $fileName = "Providers/AppServiceProvider.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeLaravueProviderAuth() {
        $this->setStub('install/provider-auth');
        $fileName = "Providers/AuthServiceProvider.php";
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

    protected function makeControllerMonitor() {
        $this->setStub('install/controller-monitor');
        $fileName = "Http/Controllers/MonitorController.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeControllerCurrentVersion() {
        $this->setStub('install/controller-current-version');
        $fileName = "Http/Controllers/CurrentVersionController.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeControllerProjectModule() {
        $this->setStub('install/controller-project-module');
        $fileName = "Http/Controllers/ProjectModuleController.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeControllerRoadMap() {
        $this->setStub('install/controller-roadmap');
        $fileName = "Http/Controllers/RoadMapController.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeControllerTask() {
        $this->setStub('install/controller-task');
        $fileName = "Http/Controllers/TaskController.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeControllerTaskGroup() {
        $this->setStub('install/controller-taskgroup');
        $fileName = "Http/Controllers/TaskGroupController.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeControllerTaskStatus() {
        $this->setStub('install/controller-taskstatus');
        $fileName = "Http/Controllers/TaskStatusController.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeControllerVersion() {
        $this->setStub('install/controller-version');
        $fileName = "Http/Controllers/VersionController.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeControllerPermission() {
        $this->setStub('install/controller-permission');
        $fileName = "Http/Controllers/PermissionController.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeControllerRolePermission() {
        $this->setStub('install/controller-role-permission');
        $fileName = "Http/Controllers/RolePermissionController.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeControllerRole() {
        $this->setStub('install/controller-role');
        $fileName = "Http/Controllers/RoleController.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeControllerUserPermission() {
        $this->setStub('install/controller-user-permission');
        $fileName = "Http/Controllers/UserPermissionController.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeControllerUserRole() {
        $this->setStub('install/controller-user-role');
        $fileName = "Http/Controllers/UserRoleController.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeControllerUser() {
        $this->setStub('install/controller-user');
        $fileName = "Http/Controllers/UserController.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeReportControllerLaravue() {
        $this->setStub('install/report-controller');
        $fileName = "Http/Controllers/Reports/LaravueReportController.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeReportControllerMonitor() {
        $this->setStub('install/report-controller-monitor');
        $fileName = "Http/Controllers/Reports/MonitorReportController.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeReportControllerTask() {
        $this->setStub('install/report-controller-task');
        $fileName = "Http/Controllers/Reports/TaskReportController.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeReportControllerUser() {
        $this->setStub('install/report-controller-user');
        $fileName = "Http/Controllers/Reports/UserReportController.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeMiddlewareLaravueAuthenticate() {
        $this->setStub('install/middleware-authenticate');
        $fileName = "Http/Middleware/LaravueAuthenticate.php";
        $path = $this->makePath( $fileName );

        $this->files->put( $path, $this->files->get( $this->getStub() ) );

        $date = now();
        $this->info("$date - [ Installing ] >> $fileName");
    }

    protected function makeMiddlewareLaravueSetHeaders() {
        $this->setStub('install/middleware-set-headers');
        $fileName = "Http/Middleware/LaravueSetHeaders.php";
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
    
    protected function publishInternacionalization() {
        $date = now();
        $this->info("$date - [ Publishing ] >> ImageServiceProviderLaravelRecent");
        $this->call('vendor:publish',[
            '--tag' =>  'laravel-pt-br-localization',
        ]);
    }

    protected function publishLaravue() {
        $date = now();
        $this->info("$date - [ Publishing ] >> LaravueServiceProvider");
        $this->call('vendor:publish',[
            '--provider' =>  'Mpmg\Laravue\LaravueServiceProvider',
        ]);
    }

}
