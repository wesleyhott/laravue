<?php

namespace wesleyhott\Laravue\Commands;

use Exception;
use Illuminate\Support\Str;

class LaravueFrontModulePageCommand extends LaravueCommand
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'laravue:front-module-page {model*} {--m|module=}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Creates a frontend Module Route for the model';

  /**
   * File type that is been created/modified.
   *
   * @var string
   */
  protected $type = 'front_module_page';

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $module = $this->option('module');
    $argumentModel = $this->argument('model');
    $model = is_array($argumentModel) ? trim($argumentModel[0]) : trim($argumentModel);
    $date = now();

    $path = $this->getFrontPath("{$module}Index.vue");
    try {
      $module_exists = $this->lookForInFile($path, $this->getTitle($model));
      if ($module_exists) {
        return;
      }
      $this->files->put($path, $this->build($path, $model, $module));
      $this->info("$date - [ $model ] >> {$module}Index.vue");
    } catch (Exception $ex) {
      $this->error('File not found: ' . $path);
    }
  }

  protected function build(string $path, string $model, string $module): string
  {
    $this->createIndexFileIfNotExists($module);
    $route_file = $this->files->get($path);
    return $this->replaceRouteRoutes($route_file, $model);
  }

  protected function createIndexFileIfNotExists(string $module)
  {
    $path = $this->getFrontPath("{$module}Index.vue");

    if (file_exists($path)) {
      return;
    }

    $stub = $this->files->get($this->getStub('front/module-index'));
    $stub = $this->replacePluralTitleModule($stub, $module);
    $this->createFileWithContent($path, $stub);
    $this->files->put($path, $stub);
  }

  protected function replaceRouteRoutes($route_file, $model)
  {
    $stub_route = <<<STUB
                      {
                          title: '{{ title }}',
                          icon: 'drag_indicator',
                          route: { name: '{{ plural_lcfirst_model }}' },
                        },
                        // {{ laravue-insert:route }}
                      STUB;
    $new_route = $stub_route;
    $new_route = $this->replaceTitle($new_route, $model);
    $new_route = $this->replacePluralLcfirstModel($new_route, $model);

    return $this->replaceInsert('route', $new_route, $route_file);
  }
}
