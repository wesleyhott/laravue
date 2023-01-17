<?php

namespace wesleyhott\Laravue\Commands;

use Illuminate\Support\Str;

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
            $argument_model = $this->argument('model');
            $model = is_array($argument_model) ? trim($argument_model[0]) : trim($argument_model);
        }

        $date = now();

        $path = $this->getPath($model);
        $schema = Str::ucfirst($this->option('schema'));
        $this->files->put($path, $this->buildDataSeeder($model, $schema));

        $formated_model = $model;
        if ($this->option('mxn')) {
            $formated_model = $model[0] . $model[1];
        }
        $this->info("$date - [ $formated_model ] >> DatabaseSeeder.php");
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
        $formated_model = "";
        if ($this->option('mxn')) {
            $formated_model = $model[0] . $model[1];
        } else {
            $formated_model = $model;
        }

        $stub = $this->files->get($this->getPath($formated_model));
        return $this->replaceSeeder($stub, $formated_model, $schema);
    }

    protected function replaceSeeder(string $database_seeder_file, string $model, string $schema): string
    {
        $new_seeder = "";
        $new_seeder .= "\$this->call({$schema}{$model}Seeder::class);" . PHP_EOL;
        $new_seeder .= $this->tabs(2) . "// {{ laravue-insert:seed }}";

        return str_replace('// {{ laravue-insert:seed }}', $new_seeder, $database_seeder_file);
    }
}
