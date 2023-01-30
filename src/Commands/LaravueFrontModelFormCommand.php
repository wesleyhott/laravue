<?php

namespace wesleyhott\Laravue\Commands;

use Exception;
use Illuminate\Support\Str;

class LaravueFrontModelFormCommand extends LaravueCommand
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'laravue:front-model-form {model*} {--f|fields=} {--m|module=}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Creates the pages/<<module?>>/<<model>>/forms/<<module>>Form.vue for the given model.';

  /**
   * File type that is been created/modified.
   *
   * @var string
   */
  protected $type = 'front_model_form';

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

    $file_name = "{$model}Form.vue";
    $path = $this->getFrontPath($file_name);
    $file = $this->createFile($path, 'front/forms/model-form', true);
    try {
      $contents = $this->build($file, $model, $module);
      $this->files->put($path, $contents);
      $this->info("$date - [ $model ] >> {$file_name}");
    } catch (Exception $ex) {
      $this->error('File not found: ' . $path);
    }
  }

  protected function build(string $file, string $model, string $module): string
  {
    $file = $this->replaceTitle($file, $model);
    $file = $this->replaceLcfirstPluralModel($file, $model);
    $file = $this->replaceModel($file, $model);
    $file = $this->replaceLcfirstModel($file, $model);
    $file = $this->replacePathSnakeModule($file, $module);
    $file = $this->replaceKebabPluralModuleModel($file, $module, $model);

    $file = $this->replaceFields($file, $module, $model);

    return $file;
  }

  protected function createIndexFileIfNotExists(string $path, string $module): string
  {
    $content = $this->files->get($this->getStub('front/model-index-page'));
    if (!file_exists($path)) {
      $this->createFileWithContents($path, $content);
    }

    return $content;
  }

  protected function replaceFields($file, $module, $model)
  {
    $first_line = PHP_EOL . $this->tabs(2);

    $fields = $this->getFieldsArray($this->option('fields'));
    $components = '';
    $has_input = false;
    $has_select = false;
    foreach ($fields as $key => $value) {
      $type = $this->getType($value);
      $is_nullable = $this->hasNullable($value);

      if ($this->isFk($key)) {
        $has_select = true;
        $components .= $first_line . $this->componentFactory('select', $module, $model, $key, $is_nullable);
        continue;
      }

      switch ($type) {
        case 'string':
          $components .= $first_line . $this->componentFactory('input', $module, $model, $key, $is_nullable);
          $has_input = true;
          break;
        case 'text':
          $components .= $first_line . $this->componentFactory('textarea', $module, $model, $key, $is_nullable);
          $has_input = true;
          break;
        default:
          $components .= $first_line . $this->componentFactory('input', $module, $model, $key, $is_nullable);
      }
    }

    if ($has_input) {
      $import_input = <<< STUB
                          import LaravueForm from 'components/LaravueForm.vue';
                          import LaravueInput from 'pages/components/LaravueInput.vue';
                          STUB;
      $file = str_replace('import LaravueForm from \'components/LaravueForm.vue\';', $import_input, $file);
    }

    if ($has_select) {
      $import_input = <<< STUB
                          import LaravueForm from 'components/LaravueForm.vue';
                          import LaravueSelect from 'pages/components/LaravueSelect.vue';
                          STUB;
      $file = str_replace('import LaravueForm from \'components/LaravueForm.vue\';', $import_input, $file);
    }

    return str_replace('{{ fields }}', $components, $file);
  }

  private function componentFactory(string $component, string $module, string $model, string $field, bool $is_nullable): string
  {
    switch ($component) {
      case 'input':
        return $this->inputComponent($model, $field, $is_nullable);
      case 'textarea':
        return $this->textAreaComponent($model, $field, $is_nullable);
      case 'select':
        return $this->selectComponent($module,  $model,  $field);
      case 'toggle':
        return $this->toggleComponent();
      case 'date':
        return $this->dateComponent();
      default:
        return $this->inputComponent($model, $field, $is_nullable);
    }
  }

  private function inputComponent(string $model, string $field, bool $is_nullable): string
  {
    $required = $is_nullable ? '' : ' *';
    $title_field = $this->getTitle($field);
    $lcfirst_model = Str::lcfirst($model);
    $component = <<<STUB
                      <laravue-input
                            v-model="{$lcfirst_model}.{$field}"
                            label="{$title_field}{$required}"
                            :rules="[(val) => (val && val.length > 0) || '{$title_field} is required.']"
                          />
                      STUB;
    return $component;
  }

  private function textAreaComponent(string $model, string $field, bool $is_nullable): string
  {
    $required = $is_nullable ? '' : ' *';
    $title_field = $this->getTitle($field);
    $lcfirst_model = Str::lcfirst($model);
    $component = <<<STUB
                      <laravue-input
                            v-model="{$lcfirst_model}.{$field}"
                            type="textarea"
                            rows=4
                            label="{$title_field}{$required}"
                            :rules="[(val) => (val && val.length > 0) || '{$title_field} is required.']"
                          />
                      STUB;
    return $component;
  }

  private function selectComponent(string $module, string $model, string $field): string
  { // field: user_id
    $clean_field = substr($field, 0, -3);
    $v_model = Str::lcfirst($model) . '.' . $clean_field;
    $title = $this->getTitle($field);
    $plural_clean_field  = $this->pluralize($clean_field);
    $endpoint = Str::kebab("{$module}-{$plural_clean_field}");
    $label = $this->getLabelFromModel($module, $model);
    $component = <<<STUB
                      <laravue-select
                            v-model="{$v_model}"
                            label="{$title}"
                            endpoint="{$endpoint}"
                            :endpoint-fields="['{$label}']"
                          />
                      STUB;
    return $component;
  }

  private function toggleComponent(): string
  {
    $component = <<<STUB

                      STUB;
    return $component;
  }

  private function dateComponent(): string
  {
    $component = <<<STUB

                      STUB;
    return $component;
  }
}
