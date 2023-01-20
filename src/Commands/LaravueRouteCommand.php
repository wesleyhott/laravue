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

    protected function replaceRoute($route_file, $model)
    {
        $formated_model = ucfirst($model);
        $model_name = ucfirst($this->pluralize($model));
        $route = str_replace('_', '-', Str::snake($model_name));
        $schema = Str::ucfirst($this->option('schema'));
        $parsed_schema = empty($schema) ? '' : "\\{$schema}";
        $route_schema = empty($schema) ? '' : Str::lcfirst("{$schema}-");

        $new_route = "";
        $new_route .= "'{$route_schema}{$route}' => \App\Http\Controllers{$parsed_schema}\\{$formated_model}Controller::class," . PHP_EOL;
        $new_route .= $this->tabs(2) . "// {{ laravue-insert:route }}";

        return str_replace('// {{ laravue-insert:route }}', $new_route, $route_file);
    }

    protected function replaceReport($route_file, $model)
    {
        $model_name = ucfirst($this->pluralize($model));
        $route = strtolower($model_name);

        $new_route = "";
        $new_route .= "Route::get('$route/{reportType}', '$model" . "ReportController@index');" . PHP_EOL;
        $new_route .= $this->tabs(2) . "// {{ laravue-insert:report }}";

        return str_replace('// {{ laravue-insert:report }}', $new_route, $route_file);
    }
}
