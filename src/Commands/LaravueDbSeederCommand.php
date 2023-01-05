<?php

namespace wesleyhott\Laravue\Commands;

class LaravueDbSeederCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:dbseeder {model*} {--x|mxn} {--s|schema= : determine a schema for model (postgres)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação do seed em database seeder nos padrões do Laravue.';

    /**
     * Tipo de modelo que está sendo criado.
     *
     * @var string
     */
    protected $type = 'seeder';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $model = "";
        if ($this->option('mxn')) {
            $model = $this->argument('model');
        } else {
            $argumentModel = $this->argument('model');
            $model = is_array($argumentModel) ? trim($argumentModel[0]) : trim($argumentModel);
        }

        $date = now();

        $path = $this->getPath($model);
        $this->files->put($path, $this->buildDataSeeder($model, $this->option('schema')));

        $formatedModel = $model;
        if ($this->option('mxn')) {
            $formatedModel = $model[0] . $model[1];
        }
        $this->info("$date - [ $formatedModel ] >> DatabaseSeeder.php");
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildDataSeeder($model, $schema)
    {
        $formatedModel = "";
        if ($this->option('mxn')) {
            $formatedModel = $model[0] . $model[1];
        } else {
            $formatedModel = $model;
        }

        $stub = $this->files->get($this->getPath($formatedModel));
        $seeder = $this->replaceSeeder($stub, $formatedModel);
        return $this->replaceUse($seeder, $schema);
    }

    protected function replaceSeeder($databaseSeederFile, $model)
    {
        $newSeeder = "";
        $newSeeder .= "$" . "this->call($model" . "Seeder::class);" . PHP_EOL;
        $newSeeder .= "\t\t// {{ laravue-insert:seed }}";

        return str_replace('// {{ laravue-insert:seed }}', $newSeeder, $databaseSeederFile);
    }

    protected function replaceUse($databaseSeederFile, $schema)
    {
        $schemaPath = isset($schema) && $schema != '' ? "$schema\\" : '';
        $newUse = "use Database\Seeders\Recipe\\{$schemaPath}NutritionSeeder;" . PHP_EOL;
        $newUse .= "// {{ laravue-insert:use }}";

        return str_replace('// {{ laravue-insert:use }}', $newUse, $databaseSeederFile);
    }
}
