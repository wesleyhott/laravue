<?php

namespace App\Console\Commands;

class MpmgDatabaseSeeder extends MpmgCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mpmg:dbseeder {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação do seed em database seeder nos padrões do MPMG.';

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
        $formatedModel = ucfirst( $this->pluralize( 2, $model ) );

        $newSeeder = "";
        $newSeeder .= "$"."this->call($formatedModel"."TableSeeder::class);" . PHP_EOL;
        $newSeeder .= "\t\t// {{ mpmg-insert:seed }}";
        
        return str_replace( '// {{ mpmg-insert:seed }}', $newSeeder, $databaseSeederFile );
    }
}
