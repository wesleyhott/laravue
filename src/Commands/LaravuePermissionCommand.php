<?php

namespace wesleyhott\Laravue\Commands;

use Illuminate\Support\Str;

class LaravuePermissionCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:permission {model*} 
                                            {--x|mxn} 
                                            {--i|view : build a model based on view, not table}
                                            {--s|schema= : determine a schema for model (postgres)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação das permissões para o modelo';

    /**
     * Tipo de modelo que está sendo criado.
     *
     * @var string
     */
    protected $type = 'permission';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $model = "";
        if ($this->option('mxn')) {
            $argumentModel = $this->argument('model');
            $model = trim($argumentModel[0] . $argumentModel[0]);
        } else {
            $argumentModel = $this->argument('model');
            $model = is_array($argumentModel) ? trim($argumentModel[0]) : trim($argumentModel);
        }
        $date = now();

        $path = $this->getPath($model);
        $this->files->put($path, $this->buildPermission($model));

        $this->info("$date - [ $model ] >> LaravueSeeder.php");
    }

    protected function buildPermission($model)
    {
        $routes = $this->files->get($this->getPath($model));
        $menu = $this->replacePermission($routes, $model);

        return $this->replaceMenu($menu, $model);
    }

    protected function replacePermission($permission_file, $model)
    {
        $formated_model = ucfirst($model);
        $model_name = $this->getTitle($formated_model);
        $route = Str::snake($model);
        $permission_name = str_replace('_', '-', Str::snake($this->pluralize($model_name)));
        $schema = $this->option('schema');
        $parsed_schema = empty($schema) ? '' : Str::snake($schema) . '-';
        $var_schema = empty($schema) ? '' : Str::snake($schema) . '_';

        $new_permission = "";
        $new_permission .= "\$create_{$var_schema}{$route} = Permission::create(['name' => 'c-{$parsed_schema}{$permission_name}', 'label' => 'Create {$schema} {$model_name}']);" . PHP_EOL;
        $new_permission .= "\t\t\$read_{$var_schema}{$route} = Permission::create(['name' => 'r-{$parsed_schema}{$permission_name}', 'label' => 'Read {$schema} {$model_name}']);" . PHP_EOL;
        if (!$this->option('view')) {
            $new_permission .= "\t\t$" . "update_{$var_schema}{$route} = Permission::create(['name' => 'u-{$parsed_schema}{$permission_name}', 'label' => 'Update {$schema} {$model_name}']);" . PHP_EOL;
            $new_permission .= "\t\t$" . "delete_{$var_schema}{$route} = Permission::create(['name' => 'd-{$parsed_schema}{$permission_name}', 'label' => 'Delete {$schema} {$model_name}']);" . PHP_EOL;
        }
        $new_permission .= "\t\t$" . "print_{$var_schema}{$route} = Permission::create(['name' => 'p-{$parsed_schema}{$permission_name}', 'label' => 'Print {$schema} {$model_name}']);" . PHP_EOL;
        $new_permission .= PHP_EOL;
        $new_permission .= "\t\t// {{ laravue-insert:permissions }}";

        return str_replace('// {{ laravue-insert:permissions }}', $new_permission, $permission_file);
    }

    protected function replaceMenu($permission_file, $model)
    {
        $formated_model = ucfirst($model);
        $model_name = $this->getTitle($formated_model);
        $route = Str::snake($model);
        $permission_name = str_replace('_', '-', Str::snake($this->pluralize($model_name)));
        $schema = $this->option('schema');
        $parsed_schema = empty($schema) ? '' : Str::snake($schema) . '-';
        $var_schema = empty($schema) ? '' : Str::snake($schema) . '_';

        $new_permission = "";
        $new_permission .= "$" . "access_{$var_schema}{$route}_menu = Permission::create(['name' => 'm-{$parsed_schema}{$permission_name}', 'label' => 'Access {$model_name} menu']);" . PHP_EOL;
        $new_permission .= "\t\t// {{ laravue-insert:menu }}";

        return str_replace('// {{ laravue-insert:menu }}', $new_permission, $permission_file);
    }
}
