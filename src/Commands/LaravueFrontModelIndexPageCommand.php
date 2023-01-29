<?php

namespace wesleyhott\Laravue\Commands;

use Exception;
use Illuminate\Support\Str;

class LaravueFrontModelIndexPageCommand extends LaravueCommand
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'laravue:front-model-index-page {model*} {--f|fields=} {--m|module=}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Creates a frontend Index Model file for the model';

  /**
   * File type that is been created/modified.
   *
   * @var string
   */
  protected $type = 'front_model_index_page';

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

    $path = $this->getFrontPath("{$model}IndexPage.vue");
    try {
      $this->files->put($path, $this->build($path, $model, $module));
      $this->info("$date - [ $model ] >> {$model}IndexPage.vue");
    } catch (Exception $ex) {
      $this->error('File not found: ' . $path);
    }
  }

  protected function build(string $path, string $model, string $module): string
  {
    // Get File
    $file_content = $this->createIndexFileIfNotExists($path, $module);

    // Replacements
    $file_content = $this->replacePluralTitle($file_content, $model);
    $file_content = $this->replaceLcfirstModel($file_content, $model);
    $file_content = $this->replaceRouteModel($file_content, "{$module}{$model}");
    $file_content = $this->replaceSelectedLabel($file_content, $this->getLabelFromModel($module, $model));
    $file_content = $this->replaceModule($file_content, $module);
    $file_content = $this->replaceModel($file_content, $model);
    $file_content = $this->replaceKebabModel($file_content, $model);
    $file_content = $this->replaceFields($file_content, $module, $model);

    return $file_content;
  }

  protected function createIndexFileIfNotExists(string $path, string $module): string
  {
    $content = $this->files->get($this->getStub('front/model-index-page'));
    if (!file_exists($path)) {
      $this->createFileWithContents($path, $content);
    }

    return $content;
  }

  protected function replaceFields($route_file, $module, $model)
  {
    $stub_field = PHP_EOL . $this->tabs(1);
    $stub_field .= <<<STUB
                      {
                          name: '{{ field_key }}',
                          required: true,
                          label: '{{ title }}',
                          align: 'left',
                          field: {{ field }}, {{ filter }}
                          sortable: true,
                        },
                      STUB;

    $fields = $this->getFieldsArray($this->option('fields'));
    $return_fields = '';
    foreach ($fields as $key => $value) {
      $new_field = $stub_field;

      $new_field = $this->replaceTitle($new_field, $key);
      $new_field = str_replace('{{ field_key }}', $key, $new_field);
      $field = "'{$key}'";
      $filter = '';
      if ($this->isFk($key)) {
        $model_key = Str::studly(substr($key, 0, -3));
        $label = $this->getLabelFromModel($module, $model_key);
        $lcfirst_model_key = Str::lcfirst($model_key);
        $field = "(row) => row." . $lcfirst_model_key . "?.{$label}";
        // Filter
        $filter = PHP_EOL . $this->tabs(2);
        $filter .= "filterBy: { relations: '{$lcfirst_model_key}', field: '{$label}' },";
      }
      $new_field = str_replace('{{ field }}', $field, $new_field);
      $new_field = str_replace('{{ filter }}', $filter, $new_field);

      $return_fields .= $new_field;
    }

    return str_replace('{{ fields }}', $return_fields, $route_file);
  }
}
