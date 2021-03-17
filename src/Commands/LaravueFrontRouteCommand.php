<?php

namespace Mpmg\Laravue\Commands;
use Illuminate\Support\Str;

class LaravueFrontRouteCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:frontroute {model*} {--o|outdocker}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação da rota no frontend para o modelo';

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

        $path = $this->getFrontRoutePath();
        $this->files->put($path, $this->buildRoute($model));

        $this->info("$date - [ $model ] >> routes.js");
    }

    protected function getFrontRoutePath()
    {
        $currentDirectory =  getcwd();
        $paths = explode( "/", str_replace( '\\', '/', $currentDirectory) );

        if ( end( $paths ) == "laravue") { // Laravue Tests
            $routeDirectory = $this->fileBuildPath($currentDirectory, 'Frontend', 'src', 'routes');
        } else if ( $this->option('outdocker') ) {
            $routeDirectory = Str::replaceFirst( end( $paths ), $this->fileBuildPath( 'frontend', 'src', 'routes' ), $currentDirectory);
        } else { 
            $routeDirectory = Str::replaceFirst( end( $paths ), $this->fileBuildPath('src', 'routes' ), $currentDirectory);
        }

        if( !is_dir($routeDirectory) ) {
            mkdir( $routeDirectory, 0777, true);
        }

        $file = $this->fileBuildPath( $routeDirectory, 'routes.js' );
        
        return $file;
    }

    protected function buildRoute($model)
    {
        $routes = $this->files->get($this->getFrontRoutePath());
        $imports = $this->replaceRouteImports($routes, $model);

        return $this->replaceRouteRoutes($imports, $model);
    }

    protected function replaceRouteImports($routeFile, $model)
    {   
        $projectName = "";
        $formatedModel = ucfirst( $model );

        $newImport = "";
        $newImport .= "// $formatedModel" . PHP_EOL;
        $newImport .= "import $formatedModel" . "Create from 'src/components/$this->projectName/Views/Pages/$formatedModel/Create.vue'" . PHP_EOL;
        $newImport .= "import $formatedModel" . "Edit from 'src/components/$this->projectName/Views/Pages/$formatedModel/Edit.vue'" . PHP_EOL;
        $newImport .= "import $formatedModel" . "Index from 'src/components/$this->projectName/Views/Pages/$formatedModel/Index.vue'" . PHP_EOL;
        $newImport .= "import $formatedModel" . "Report from 'src/components/$this->projectName/Views/Pages/$formatedModel/Report.vue'" . PHP_EOL;
        $newImport .= PHP_EOL;
        $newImport .= '// {{ laravue-insert:import }}' . PHP_EOL;

        return str_replace( '// {{ laravue-insert:import }}', $newImport, $routeFile );
    }

    protected function replaceRouteRoutes($routeFile, $model)
    {   
        $formatedModel = ucfirst( $model );
        $ModelName = ucfirst( $this->pluralize( 2, $model ) );
        $route = strtolower( $ModelName );

        $newRoute = "";
        $newRoute .= "// $ModelName" . PHP_EOL;
        $newRoute .= "\t\t{" . PHP_EOL;
        $newRoute .= "\t\t\tpath: '$route/create', " . PHP_EOL;
        $newRoute .= "\t\t\tname: 'Inserir $ModelName', " . PHP_EOL;
        $newRoute .= "\t\t\tcomponent: $formatedModel"."Create, " . PHP_EOL;
        $newRoute .= "\t\t}," . PHP_EOL;

        $newRoute .= "\t\t{" . PHP_EOL;
        $newRoute .= "\t\t\tpath: '$route/edit/:modelId', " . PHP_EOL;
        $newRoute .= "\t\t\tname: 'Editar $ModelName', " . PHP_EOL;
        $newRoute .= "\t\t\tcomponent: $formatedModel"."Edit, " . PHP_EOL;
        $newRoute .= "\t\t}," . PHP_EOL;

        $newRoute .= "\t\t{" . PHP_EOL;
        $newRoute .= "\t\t\tpath: '$route', " . PHP_EOL;
        $newRoute .= "\t\t\tname: '$ModelName', " . PHP_EOL;
        $newRoute .= "\t\t\tcomponent: $formatedModel"."Index, " . PHP_EOL;
        $newRoute .= "\t\t}," . PHP_EOL;

        $newRoute .= "\t\t{" . PHP_EOL;
        $newRoute .= "\t\t\tpath: '$route/report', " . PHP_EOL;
        $newRoute .= "\t\t\tname: 'Imprimir $ModelName', " . PHP_EOL;
        $newRoute .= "\t\t\tcomponent: $formatedModel"."Report, " . PHP_EOL;
        $newRoute .= "\t\t}," . PHP_EOL;
        $newRoute .= "\t\t// {{ laravue-insert:routes }}";

        return str_replace( '// {{ laravue-insert:routes }}', $newRoute, $routeFile );
    }
}
