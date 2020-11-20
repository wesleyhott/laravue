<?php

namespace Mpmg\Laravue\Commands;

class LaravueDatabaseSeederCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:dbseeder {model}';

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
        $model = trim($this->argument('model'));
        $date = now();

        $path = $this->getPath($model);
        $this->files->put( $path, $this->buildDataSeeder($model) );

        $this->info("$date - [ $model ] >> DatabaseSeeder.php");
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildDataSeeder($model)
    {
        $stub = $this->files->get( $this->getPath($model) );

        return $this->replaceSeeder($stub, $model);
    }

    protected function replaceSeeder($databaseSeederFile, $model)
    {
        $formatedModel = ucfirst( $model );

        $newSeeder = "";
        $newSeeder .= "$"."this->call($formatedModel"."Seeder::class);" . PHP_EOL;
        $newSeeder .= "\t\t// {{ laravue-insert:seed }}";
        
        return str_replace( '// {{ laravue-insert:seed }}', $newSeeder, $databaseSeederFile );
    }
}
