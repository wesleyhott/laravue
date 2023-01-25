<?php

namespace wesleyhott\Laravue\Commands;

use Exception;
use Illuminate\Support\Str;

class LaravueFrontModulePageRoutesCommand extends LaravueCommand
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'laravue:front-module-page-routes {model*} {--m|module=}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Creates a frontend Module Page Routes for the model';

  /**
   * File type that is been created/modified.
   *
   * @var string
   */
  protected $type = 'front_module_page_routes';

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $module = $this->option('module');
    $lower_module = Str::lower($module);
    $argumentModel = $this->argument('model');
    $model = is_array($argumentModel) ? Str::ucfirst(trim($argumentModel[0])) : Str::ucfirst(trim($argumentModel));
    $date = now();

    $path = $this->getFrontPath("{$lower_module}.ts");
    try {
      $module_exists = $this->lookForInFile($path, $model);
      if ($module_exists) {
        return;
      }
      $this->files->put($path, $this->build($path, $model, $module));
      $this->info("$date - [ $model ] >> {$lower_module}.ts");
    } catch (Exception $ex) {
      $this->error('File not found: ' . $path);
    }
  }

  protected function build(string $path, string $model, string $module): string
  {
    $this->createRouteFileIfNotExists($module);
    $route_file = $this->files->get($path);
    return $this->replaceRouteRoutes($route_file, $module, $model);
  }

  protected function createRouteFileIfNotExists(string $module)
  {
    $path = $this->getFrontPath("{$module}.ts");

    if (file_exists($path)) {
      return;
    }

    $stub = $this->files->get($this->getStub('front/module-page-routes'));
    $stub = $this->replaceUpperModule($stub, $module);
    $this->createFileWithContent($path, $stub);
    $this->files->put($path, $stub);
  }

  protected function replaceRouteRoutes(string $route_file, string $module, string $model)
  {
    $snake_module = empty($module) ? '' : Str::snake($module);
    $lcfirst_module = empty($module) ? '' : Str::lcfirst($module);
    $snake_path_module = empty($snake_module) ? '' : $snake_module  . "_";
    $route_module = empty($module) ? '' : $module  . "/";

    $plural_model = $this->pluralize($model);
    $plural_snake_model = Str::snake($plural_model);
    $snake_model = Str::snake($model);


    $stub_route = <<<STUB
                      {
                          path: "{{ snake_path_module }}{{ plural_snake_model }}",
                          name: "{{ lcfirst_module }}{{ plural_model }}",
                          component: () => import("src/pages/{{ route_module }}{{ model }}/{{ model }}IndexPage.vue"),
                        },
                        {
                          path: "{{ snake_path_module }}{{ snake_model }}_save/:id?",
                          name: "{{ lcfirst_module }}{{ model }}Save",
                          component: () => import("src/pages/{{ route_module }}{{ model }}/{{ model }}SavePage.vue"),
                        },
                        // {{ laravue-insert:route }}
                      STUB;
    $new_route = $stub_route;

    $new_route = str_replace('{{ snake_path_module }}',  $snake_path_module, $new_route);
    $new_route = str_replace('{{ plural_snake_model }}',  $plural_snake_model, $new_route);
    $new_route = str_replace('{{ lcfirst_module }}',  $lcfirst_module, $new_route);
    $new_route = str_replace('{{ plural_model }}',  $plural_model, $new_route);
    $new_route = str_replace('{{ route_module }}',  $route_module, $new_route);
    $new_route = str_replace('{{ model }}',  $model, $new_route);
    $new_route = str_replace('{{ snake_model }}',  $snake_model, $new_route);

    return $this->replaceInsert('route', $new_route, $route_file);
  }
}
