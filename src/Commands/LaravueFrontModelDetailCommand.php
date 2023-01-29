<?php

namespace wesleyhott\Laravue\Commands;

use Exception;
use Illuminate\Support\Str;

class LaravueFrontModelDetailCommand extends LaravueCommand
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'laravue:front-model-detail {model*} {--f|fields=} {--m|module=}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Creates a frontend detail page for the model';

  /**
   * File type that is been created/modified.
   *
   * @var string
   */
  protected $type = 'front_model_detail';

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

    $file_name = "{$model}DetailForm.vue";
    $path = $this->getFrontPath($file_name);
    try {
      $this->files->put($path, $this->build($path, $model, $module));
      $this->info("$date - [ $model ] >> {$file_name}");
    } catch (Exception $ex) {
      $this->error('File not found: ' . $path);
    }
  }

  protected function build(string $path, string $model, string $module): string
  {
    // Get File
    $file_content = $this->createIndexFileIfNotExists($path);

    // Replacements
    $file_content = $this->replaceTitle($file_content, $model);
    $file_content = $this->replaceModel($file_content, $model);
    $file_content = $this->replacePathSnakeModule($file_content, $module);
    $file_content = $this->replaceFields($file_content, $module, $model);

    return $file_content;
  }

  protected function createIndexFileIfNotExists(string $path): string
  {
    $content = $this->files->get($this->getStub('front/forms/model-detail-form'));
    if (!file_exists($path)) {
      $this->createFileWithContents($path, $content);
    }

    return $content;
  }

  protected function replaceFields($route_file, $module, $model)
  {
    $stub_field = PHP_EOL . $this->tabs(5);
    $stub_field .= <<<STUB
                      <div class="row">
                                  <div class="col-4"><strong>{{ title }}:</strong></div>
                                  <div class="col text-left">
                                    {{ props.model?.{{ key }} }}
                                  </div>
                                </div>
                      STUB;

    $fields = $this->getFieldsArray($this->option('fields'));
    $return_fields = '';
    foreach ($fields as $key => $value) {
      $new_field = $stub_field;

      $new_field = $this->replaceTitle($new_field, $key);
      $field = $key;
      if ($this->isFk($key)) {
        $model_key = Str::studly(substr($key, 0, -3));
        $label = $this->getLabelFromModel($module, $model_key);
        $lcfirst_model_key = Str::lcfirst($model_key);

        $field = "{$lcfirst_model_key}?.{$label}";
      }
      $new_field = str_replace('{{ key }}', $field, $new_field);

      $return_fields .= $new_field;
    }

    return str_replace('{{ fields }}', $return_fields, $route_file);
  }
}
