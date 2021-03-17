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
    protected $signature = 'laravue:frontsidebar {model*} {--o|outdocker}';

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
        $argumentModel = $this->argument('model');
        $model = is_array( $argumentModel ) ? trim( $argumentModel[0] ) : trim( $argumentModel ); 
        $date = now();

        $path = $this->getFrontMenuPath();
        $this->files->put($path, $this->buildMenu($model));

        $this->info("$date - [ $model ] >> sidebarLinks.js");
    }

    protected function getFrontMenuPath()
    {
        $currentDirectory =  getcwd();
        $paths = explode( "/", str_replace( '\\', '/', $currentDirectory) );

        if ( end( $paths ) == "laravue") { // Laravue Tests
            $menuDirectory = $this->fileBuildPath($currentDirectory, 'Frontend', 'src' );
        } else if ( $this->option('outdocker') ) {
            $menuDirectory = Str::replaceFirst( end( $paths ), $this->fileBuildPath( 'frontend', 'src' ), $currentDirectory);
        } else { 
            $menuDirectory = Str::replaceFirst( end( $paths ), $this->fileBuildPath( 'src' ), $currentDirectory);
        }

        if( !is_dir($menuDirectory) ) {
            mkdir( $menuDirectory, 0777, true);
        }

        $file = $this->fileBuildPath( $menuDirectory, 'sidebarLinks.js' );
        
        return $file;
    }

    protected function buildMenu($model)
    {
        $routes = $this->files->get($this->getFrontMenuPath());

        return $this->replaceRouteRoutes($routes, $model);
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
