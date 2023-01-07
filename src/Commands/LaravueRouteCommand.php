<?php

namespace wesleyhott\Laravue\Commands;

use Illuminate\Support\Str;

class LaravueRouteCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:route {model*} {--s|schema= : determine a schema for model (postgres)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It creates a model api route.';

    /**
     * Command type for path generation.
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
        $argumentModel = $this->argument('model');
        $model = is_array($argumentModel) ? trim($argumentModel[0]) : trim($argumentModel);
        $date = now();

        $path = $this->getPath($model);
        $this->files->put($path, $this->buildRoute($model));

        $this->info("$date - [ $model ] >> api.php");
    }

    protected function buildRoute($model)
    {
        $routes = $this->files->get($this->getPath($model));
        $report = $this->replaceRoute($routes, $model);

        return $this->replaceReport($report, $model);
    }

    protected function replaceRoute($routeFile, $model)
    {
        $formatedModel = ucfirst($model);
        $ModelName = ucfirst($this->pluralize($model));
        $route = strtolower($ModelName);
        $schema = Str::ucfirst($this->option('schema'));
        $parsedSchema = empty($schema) ? '' : "\\{$schema}";

        $newRoute = "";
        $newRoute .= "'$route' => \App\Http\Controllers{$parsedSchema}\\{$formatedModel}Controller::class," . PHP_EOL;
        $newRoute .= "\t// {{ laravue-insert:route }}";

        return str_replace('// {{ laravue-insert:route }}', $newRoute, $routeFile);
    }

    protected function replaceReport($routeFile, $model)
    {
        $formatedModel = ucfirst($model);
        $ModelName = ucfirst($this->pluralize($model));
        $route = strtolower($ModelName);

        $newRoute = "";
        $newRoute .= "Route::get('$route/{reportType}', '$model" . "ReportController@index');" . PHP_EOL;
        $newRoute .= "\t// {{ laravue-insert:report }}";

        return str_replace('// {{ laravue-insert:report }}', $newRoute, $routeFile);
    }
}
