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

        $new_permission = '';
        $tabs = '';
        if (!$this->lookForIntoLaravueSeeder("c-{$parsed_schema}{$permission_name}")) {
            $new_permission .= "\$create_{$var_schema}{$route} = Permission::updateOrCreate(['name' => 'c-{$parsed_schema}{$permission_name}', 'label' => 'Create {$schema} {$model_name}']);" . PHP_EOL;
            $tabs = $this->tabs(2);
        }

        if (!$this->lookForIntoLaravueSeeder("r-{$parsed_schema}{$permission_name}")) {
            $new_permission .= $tabs . "\$create_{$var_schema}{$route} = Permission::updateOrCreate(['name' => 'r-{$parsed_schema}{$permission_name}', 'label' => 'Read {$schema} {$model_name}']);" . PHP_EOL;
            $tabs = $this->tabs(2);
        }

        if ((!$this->option('view')) && !$this->lookForIntoLaravueSeeder("u-{$parsed_schema}{$permission_name}")) {
            $new_permission .= $tabs . "\$create_{$var_schema}{$route} = Permission::updateOrCreate(['name' => 'u-{$parsed_schema}{$permission_name}', 'label' => 'Update {$schema} {$model_name}']);" . PHP_EOL;
            $tabs = $this->tabs(2);
        }

        if ((!$this->option('view')) && !$this->lookForIntoLaravueSeeder("d-{$parsed_schema}{$permission_name}")) {
            $new_permission .= $tabs . "\$create_{$var_schema}{$route} = Permission::updateOrCreate(['name' => 'd-{$parsed_schema}{$permission_name}', 'label' => 'Delete {$schema} {$model_name}']);" . PHP_EOL;
            $tabs = $this->tabs(2);
        }

        if (!$this->lookForIntoLaravueSeeder("p-{$parsed_schema}{$permission_name}")) {
            $new_permission .= $tabs . "\$create_{$var_schema}{$route} = Permission::updateOrCreate(['name' => 'p-{$parsed_schema}{$permission_name}', 'label' => 'Print {$schema} {$model_name}']);" . PHP_EOL;
            $tabs = $this->tabs(2);
        }

        $new_permission .= $tabs == '' ? '' :  PHP_EOL;
        $new_permission .= $tabs . "// {{ laravue-insert:permissions }}";

        return str_replace('// {{ laravue-insert:permissions }}', $new_permission, $permission_file);
    }

    protected function replaceMenu($permission_file, $model)
    {
        $formated_model = ucfirst($model);
        $model_name = $this->getTitle($formated_model);
        $route = Str::snake($model);
        $permission_name = str_replace('_', '-', Str::snake($this->pluralize($model_name)));
        $schema = $this->option('schema');
        $schema_title = $this->getTitle($schema);
        $snaked_squema = Str::snake($schema);
        $parsed_schema = empty($schema) ? '' : $snaked_squema . '-';
        $var_schema = empty($schema) ? '' : Str::snake($schema) . '_';

        $new_permission = "";

        $tabs = '';
        $module_exists = $this->lookForIntoLaravueSeeder("Access {$schema_title} Module menu");
        if (!$module_exists) {
            $new_permission .= "$" . "access_{$var_schema}menu = Permission::create(['name' => 'm-{$snaked_squema}', 'label' => 'Access {$schema_title} Module menu']);" . PHP_EOL . $this->tabs(2);
            $tabs = $this->tabs(2);
        }

        if (!$this->lookForIntoLaravueSeeder("m-{$parsed_schema}{$permission_name}")) {
            $new_permission .= "$" . "access_{$var_schema}{$route}_menu = Permission::create(['name' => 'm-{$parsed_schema}{$permission_name}', 'label' => 'Access {$model_name} menu']);" . PHP_EOL;
            $tabs = $this->tabs(2);
        }
        $new_permission .= $tabs . "// {{ laravue-insert:menu }}";

        return str_replace('// {{ laravue-insert:menu }}', $new_permission, $permission_file);
    }

    private function lookForIntoLaravueSeeder(string $needle): bool
    {
        $file_name = "LaravueSeeder";
        $this->type = 'laravue-seeder';
        $path = $this->getPath($file_name);
        $this->type = 'permission';
        return $this->lookForInFile($path, $needle);
    }
}
