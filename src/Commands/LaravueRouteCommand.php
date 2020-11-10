<?php

namespace Mpmg\Laravue\Commands;

class LaravueRouteCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:route {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação da rota para o modelo';

    /**
     * Tipo de modelo que está sendo criado.
     *
     * @var string
     */
    protected $type = 'route';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $model = trim($this->argument('model'));
        $date = now();

        $path = $this->getPath($model);
        $this->files->put($path, $this->buildRoute($model));

        $this->info("$date - [ $model ] >> api.php");
    }

    protected function buildRoute($model)
    {
        $routes = $this->files->get( $this->getPath($model) );
        $report = $this->replaceRoute($routes, $model);

        return $this->replaceReport($report, $model);
    }

    protected function replaceRoute($routeFile, $model)
    {   
        $formatedModel = ucfirst( $model );
        $ModelName = ucfirst( $this->pluralize( 2, $model ) );
        $route = strtolower( $ModelName );

        $newRoute = "";
        $newRoute .= "'$route' => \App\Http\Controllers\\$formatedModel"."Controller::class," . PHP_EOL;
        $newRoute .= "\t// {{ laravue-insert:route }}";

        return str_replace( '// {{ laravue-insert:route }}', $newRoute, $routeFile );
    }

    protected function replaceReport($routeFile, $model)
    {   
        $formatedModel = ucfirst( $model );
        $ModelName = ucfirst( $this->pluralize( 2, $model ) );
        $route = strtolower( $ModelName );

        $newRoute = "";
        $newRoute .= "Route::get('$route/{reportType}', '$model"."ReportController@index');" . PHP_EOL;
        $newRoute .= "\t// {{ laravue-insert:report }}";

        return str_replace( '// {{ laravue-insert:report }}', $newRoute, $routeFile );
    }
}
