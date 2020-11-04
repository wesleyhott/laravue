<?php

namespace Mpmg\Laravue\Commands;

class LaravuePermissionCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:permission {model}';

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
        $model = trim($this->argument('model'));
        $date = now();

        $path = $this->getPath($model);
        $this->files->put($path, $this->buildPermission($model));

        $this->info("$date - [ $model ] >> LaravueSeeder.php");
    }

    protected function buildPermission($model)
    {
        $routes = $this->files->get( $this->getPath($model) );
        $menu = $this->replacePermission($routes, $model);

        return $this->replaceMenu($menu, $model);
    }

    protected function replacePermission($permissionFile, $model)
    {   
        $formatedModel = ucfirst( $model );
        $ModelName = ucfirst( $this->pluralize( 2, $model ) );
        $route = strtolower( $ModelName );

        $newPermission = "";
        $newPermission .= "$"."ver_$route = Permission::create(['name' => 'ver $route']);" . PHP_EOL;
        $newPermission .= "\t\t$"."editar_$route = Permission::create(['name' => 'editar $route']);" . PHP_EOL;
        $newPermission .= "\t\t$"."apagar_$route = Permission::create(['name' => 'apagar $route']);" . PHP_EOL;
        $newPermission .= "\t\t$"."imprimir_$route = Permission::create(['name' => 'imprimir $route']);" . PHP_EOL;
        $newPermission .= PHP_EOL;
        $newPermission .= "\t\t// {{ laravue-insert:permissions }}";

        return str_replace( '// {{ laravue-insert:permissions }}', $newPermission, $permissionFile );
    }

    protected function replaceMenu($permissionFile, $model)
    {   
        $formatedModel = ucfirst( $model );
        $ModelName = ucfirst( $this->pluralize( 2, $model ) );
        $route = strtolower( $ModelName );

        $newPermission = "";
        $newPermission .= "$"."ver_menu_$route = Permission::create(['name' => 'ver menu $route']);" . PHP_EOL;
        $newPermission .= "\t\t// {{ laravue-insert:menu }}";
        
        return str_replace( '// {{ laravue-insert:menu }}', $newPermission, $permissionFile );
    }
}
