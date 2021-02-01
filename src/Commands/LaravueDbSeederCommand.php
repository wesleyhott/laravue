<?php

namespace Mpmg\Laravue\Commands;

class LaravueDbSeederCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:dbseeder {model*} {--x|mxn}';

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
        if( $this->option('mxn') ) { 
            $model = $this->argument('model');
        } else {
            $model = trim($this->argument('model')[0]);
        }

        $date = now();

        $path = $this->getPath($model);
        $this->files->put( $path, $this->buildDataSeeder($model) );

        $formatedModel = $model;
        if( $this->option('mxn') ) { 
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
    protected function buildDataSeeder($model)
    {
        $formatedModel = "";
        if( $this->option('mxn') ) { 
            $formatedModel = $model[0] . $model[1];
        } else {
            $formatedModel = $model;
        }

        $stub = $this->files->get( $this->getPath($formatedModel) );

        return $this->replaceSeeder($stub, $formatedModel);
    }

    protected function replaceSeeder($databaseSeederFile, $model)
    {
        $newSeeder = "";
        $newSeeder .= "$"."this->call($model"."Seeder::class);" . PHP_EOL;
        $newSeeder .= "\t\t// {{ laravue-insert:seed }}";
        
        return str_replace( '// {{ laravue-insert:seed }}', $newSeeder, $databaseSeederFile );
    }
}
