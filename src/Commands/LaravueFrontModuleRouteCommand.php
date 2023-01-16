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
        return $this->replaceImport($route_file, $module);
    }

    protected function replaceImport(string $route_file, string $module): string
    {
        $stub_import = "import { {{ upper_module }}_PAGES } from 'src/router/modules/{{ snake_module }}'";
        $stub_import .= PHP_EOL . "// {{ laravue-insert:import }}";
        $import = $stub_import;

        $import = $this->replaceUpperModule($import, $module);
        $import = $this->replaceSnakeModule($import, $module);

        return $this->replaceInsert('import', $import, $route_file);;
    }

    protected function replaceUpperModule(string $stub, string $module): string
    {
        return str_replace('{{ upper_module }}',  Str::upper(Str::snake($module)), $stub);
    }

    protected function replaceSnakeModule(string $stub, string $module): string
    {
        return str_replace('{{ snake_module }}', Str::snake($module), $stub);
    }

    protected function replacePluralSnakeModule(string $stub, string $module): string
    {
        return $this->pluralize($this->replaceSnakeModule($stub, $module));
    }

    protected function replaceInsert(string $key, string $replacement, string $stub): string
    {
        $return = str_replace("// {{ laravue-insert:{$key} }}", $replacement, $stub);
        return $return;
    }


    protected function replaceRouteRoutes($route_file, $module)
    {
        $stub_route = <<<STUB
                                    {
                                        path: '{{ plural_snake_module }}',
                                        name: '{{ snake_module }}',
                                        component: () => import('src/pages/{{ uc_module }}/{{ ucf_module }}Index.vue'),
                                        children: {{ cap_module }}_PAGES,
                                    },
                        STUB;

        $newRoute = "";

        $newRoute .= "\t\t// {{ laravue-insert:route }}";

        return str_replace('// {{ laravue-insert:route }}', $newRoute, $route_file);
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
