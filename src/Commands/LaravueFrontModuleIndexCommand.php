<?php

namespace wesleyhott\Laravue\Commands;

use Exception;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;

class LaravueFrontModuleIndexCommand extends LaravueCommand
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'laravue:front-module-index {model*} {--m|module=}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Creates a frontend Module Index Route for the model';

  /**
   * File type that is been created/modified.
   *
   * @var string
   */
  protected $type = 'front_module_index';

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

    $path = $this->getFrontPath('index.ts');
    try {
      $module_exists = $this->lookForInFile($path, Str::snake($module));
      if ($module_exists) {
        return;
      }
      $this->files->put($path, $this->build($path, $model, $module));
      $this->info("$date - [ $model ] >> index.ts");
    } catch (Exception $ex) {
      $this->error('File not found: ' . $path);
    }
  }

  protected function build(string $path, string $model, string $module): string
  {
    $route_file = $this->files->get($path);
    return $this->replaceRouteRoutes($route_file, $module);
  }


  protected function replaceRouteRoutes($route_file, $module)
  {
    $stub_route = <<<STUB
                      {
                          title: '{{ ucfirst_module }}',
                          caption: '',
                          icon: 'dinner_dining',
                          route: { name: '{{ snake_module }}' },
                        },
                        // {{ laravue-insert:module }}
                      STUB;
    $new_route = $stub_route;
    $new_route = $this->replaceSnakeModule($new_route, $module);
    $new_route = $this->replaceUpperCaseFirstModule($new_route, $module);

    return $this->replaceInsert('module', $new_route, $route_file);
  }
}
