<?php

namespace Mpmg\Laravue\Commands;
use Illuminate\Support\Str;

class LaravueFrontSideBarCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:frontsidebar {model} {--o|outdocker}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação do menu no frontend para o modelo';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $model = trim($this->argument('model'));
        $date = now();

        $path = $this->getFrontMenuPath();
        $this->files->put($path, $this->buildMenu($model));

        $this->info("$date - [ $model ] >> sidebarLinks.js");
    }

    protected function getFrontMenuPath()
    {
        $currentDirectory =  getcwd();
        $paths = explode( "/", $currentDirectory );

        if ( end( $dirs ) == "laravue") { // Laravue Tests
            $routeDirectory = "$currentDirectory/Frontend/src";
        } else if ( $this->option('outdocker') ) {
            $routeDirectory = Str::replaceFirst( end( $paths ), "frontend/src", $currentDirectory);
        } else { 
            $routeDirectory = Str::replaceFirst( end( $paths ), "src", $currentDirectory);
        }

        if( !is_dir($routeDirectory) ) {
            mkdir( $routeDirectory, 0777, true);
        }

        $file = "$routeDirectory/sidebarLinks.js";
        
        return $file;
    }

    protected function buildMenu($model)
    {
        $routes = $this->files->get($this->resolveFrontMenuPath());

        return $this->replaceRouteRoutes($routes, $model);
    }

    protected function resolveFrontMenuPath()
    {
        $currentDirectory =  getcwd();
        $paths = explode( "/", $currentDirectory );

        if ( end( $dirs ) == "laravue") { // Laravue Tests
            $routeDirectory = "$currentDirectory/Frontend/src/sidebarLinks.js";
        } else if ( $this->option('outdocker') ) {
            $routeDirectory = Str::replaceFirst( end( $paths ), "frontend/src/sidebarLinks.js", $currentDirectory);
        } else { 
            $routeDirectory = Str::replaceFirst( end( $paths ), "src/sidebarLinks.js", $currentDirectory);
        }

        return $routeDirectory;
    }

    protected function replaceRouteRoutes($routeFile, $model)
    {   
        $isPlural = true;
        $ModelName = $this->getTitle( $model, $isPlural );
        $route = $this->pluralize( 2, strtolower( $model ) );

        $newRoute = "";
        $newRoute .= "// {{ laravue-insert:routes }}" . PHP_EOL;
        $newRoute .= "\t{" . PHP_EOL;
        $newRoute .= "\t\tname: '$ModelName'," . PHP_EOL;
        $newRoute .= "\t\ticon: 'nc-icon nc-paper', " . PHP_EOL;
        $newRoute .= "\t\tpath: '/paginas/$route'," . PHP_EOL;
        $newRoute .= "\t\tpermission: 'ver $route'," . PHP_EOL;
        $newRoute .= "\t\t//children: [" . PHP_EOL;
        $newRoute .= "\t\t//\t{" . PHP_EOL;
        $newRoute .= "\t\t//\t\tname: ''," . PHP_EOL;
        $newRoute .= "\t\t//\t\tpath: ''," . PHP_EOL;
        $newRoute .= "\t\t//\t\tpermission: ''," . PHP_EOL;
        $newRoute .= "\t\t//\t}," . PHP_EOL;
        $newRoute .= "\t\t//]," . PHP_EOL;
        $newRoute .= "\t},";

        return str_replace( "// {{ laravue-insert:routes }}", $newRoute, $routeFile );
    }
}
