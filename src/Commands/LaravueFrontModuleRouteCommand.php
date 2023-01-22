<?php

namespace wesleyhott\Laravue\Commands;

use Exception;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;

class LaravueFrontModuleRouteCommand extends LaravueCommand
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'laravue:front-module-route {model*} {--m|module=}';

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
  protected $type = 'front_module_route';

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

    $path = $this->getFrontPath('routes.ts');
    try {
      $module_exists = $this->lookForInFile($path, Str::snake($module));
      if ($module_exists) {
        return;
      }
      $this->files->put($path, $this->build($path, $model, $module));
      $this->info("$date - [ $model ] >> routes.ts");
    } catch (Exception $ex) {
      $this->error('File not found: ' . $path);
    }
  }

  protected function build(string $path, string $model, string $module): string
  {
    $route_file = $this->files->get($path);
    $route_file = $this->replaceImport($route_file, $module);
    return $this->replaceRouteRoutes($route_file, $module);
  }

  protected function replaceImport(string $route_file, string $module): string
  {
    $stub_import = "import { {{ upper_module }}_PAGES } from 'src/router/modules/{{ snake_module }}'";
    $stub_import .= PHP_EOL . "// {{ laravue-insert:import }}";
    $import = $stub_import;

    $import = $this->replaceUpperModule($import, $module);
    $import = $this->replaceSnakeModule($import, $module);

    return $this->replaceInsert('import', $import, $route_file);
  }

  protected function replaceRouteRoutes($route_file, $module)
  {
    $stub_route = <<<STUB
                        {
                                path: '{{ plural_snake_module }}',
                                name: '{{ snake_module }}',
                                component: () => import('src/pages/{{ ucfirst_module }}/{{ ucfirst_module }}Index.vue'),
                                children: {{ upper_module }}_PAGES,
                              },
                              // {{ laravue-insert:route }}
                        STUB;
    $new_route = $stub_route;
    $new_route = $this->replacePluralSnakeModule($new_route, $module);
    $new_route = $this->replaceSnakeModule($new_route, $module);
    $new_route = $this->replaceUpperCaseFirstModule($new_route, $module);
    $new_route = $this->replaceUpperModule($new_route, $module);

    return $this->replaceInsert('route', $new_route, $route_file);
  }

  protected function lookForInFile(string $path, string $needle): bool
  {
    $found = false;
    $file = @fopen($path, "r");
    if ($file) {
      while (($line = fgets($file, 4096)) !== false) {
        if (strpos($line, $needle) !== false) {
          $found = true;
          break;
        }
      }
      fclose($file);
    }

    return $found;
  }
}
