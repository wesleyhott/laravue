<?php

namespace wesleyhott\Laravue\Commands;

use Exception;
use Illuminate\Support\Str;

class LaravueFrontModelSavePageCommand extends LaravueCommand
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'laravue:front-model-save-page {model*} {--m|module=}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Creates the pages/<<module?>>/<<model>>/<<module>>SavePage.vue for the given model.';

  /**
   * File type that is been created/modified.
   *
   * @var string
   */
  protected $type = 'front_model_save_page';

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $module = $this->option('module');
    $snake_module = Str::snake($module);
    $argumentModel = $this->argument('model');
    $model = is_array($argumentModel) ? Str::ucfirst(trim($argumentModel[0])) : Str::ucfirst(trim($argumentModel));
    $date = now();

    $file_name = "{$model}SavePage.vue";
    $path = $this->getFrontPath($file_name);
    $file = $this->createFile($path, 'front/model-save-page');
    try {
      $file_exists = $this->lookForInFile($path, "{$model}Form");
      if ($file_exists) {
        return;
      }
      $contents = $this->build($file, $model);
      $this->files->put($path, $contents);
      $this->info("$date - [ $model ] >> {$file_name}");
    } catch (Exception $ex) {
      $this->error('File not found: ' . $path);
    }
  }

  protected function build(string $file, string $model): string
  {
    $file = $this->replaceKebabModel($file, $model);
    $file = $this->replaceModel($file, $model);
    return $file;
  }
}
