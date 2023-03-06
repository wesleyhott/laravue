<?php

namespace wesleyhott\Laravue\Commands;

use Exception;
use Illuminate\Support\Str;

class LaravueFrontModelTypeCommand extends LaravueCommand
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'laravue:front-model-type {model*} {--f|fields=} {--m|module=}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Creates the types/models/<<module?>>/<<model>>.ts for the given model.';

  /**
   * File type that is been created/modified.
   *
   * @var string
   */
  protected $type = 'front_model_type';

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

    $file_name = "{$model}.ts";
    $path = $this->getFrontPath($file_name);
    $file = $this->createFile($path, 'front/model', true);
    try {
      $fields = $this->getFieldsArray($this->option('fields'));
      $contents = $this->build($file, $model, $fields);
      $this->files->put($path, $contents);
      $this->info("$date - [ $model ] >> {$file_name}");
    } catch (Exception $ex) {
      $this->error('File not found: ' . $path);
    }
  }

  protected function build(string $file, string $model, array $fields): string
  {
    $file = $this->replaceImports($file, $fields);
    $file = $this->replaceModel($file, $model);
    $file = $this->replaceFields($file, $fields);

    return $file;
  }

  protected function replaceImports(string $file, array $fields): string
  {
    $import_input_stub = <<< STUB
                          import { {{ field }} } from './{{ field }}';
                          STUB;
    $imports = '';

    foreach ($fields as $key => $value) {
      if ($this->isFk($key)) {
        $import_input = $import_input_stub;
        $key_name = Str::studly(str_replace('_id', '', $key));
        $imports .= PHP_EOL . str_replace('{{ field }}', $key_name, $import_input);
      }
    }
    return str_replace('{{ import }}', $imports, $file);
  }

  protected function replaceFields($file, $fields)
  {
    $new_line = PHP_EOL . $this->tabs(1);
    $return_fields = '';
    foreach ($fields as $key => $value) {
      $js_type = $this->parsePhpToJavaScriptType($this->getType($value));

      if ($this->isFk($key)) {
        $property = Str::camel(str_replace('_id', '', $key));
        $property_type = Str::ucfirst($property);
        $return_fields .= $new_line . "$property?: $property_type;";
        continue;
      }

      $return_fields .= $new_line . "$key?: $js_type;";
    }

    return str_replace('{{ fields }}', $return_fields, $file);
  }
}
