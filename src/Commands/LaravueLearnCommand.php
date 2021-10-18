<?php

namespace Mpmg\Laravue\Commands;

use Illuminate\Support\Str;

class LaravueLearnCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:learn {words* : The words to be learned} 
        {--a|accentuation : Indicates to learn accentuation words}
        {--p|plural : Indicates to learn plural words}
        {--s|selectlabel : Indicates to learn label word for select component}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensina ao Laravue novas palavras.';

    /**
     * Tipo de comando que está sendo executado.
     *
     * @var string
     */
    protected $type = 'config';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $argumentWord = $this->argument('words');
        if( gettype( $this->argument('words') ) == 'string' ) {
            $argumentWord = array( $this->argument('word') );
        }

        $words = [];
        foreach ($argumentWord as $word){
            array_push( $words, $word );
        }

        if( $this->option('accentuation') ){
            $this->learnAccentuation( $words );
        } else if( $this->option('plural') ){
            $this->learnPlural( $words );
        } else {
            $this->learnSelectLabel( $words );
        }
    }

    /**
     * Cria nova entrada de configuração para acentuação
     *
     * @return void
     */
    protected function learnAccentuation( $words )
    {
        // Get file path
        $path = $this->getPath();

        // Get the file
        $configFile = $this->files->get( $path );

        // Transform data
        $withoutAccent = $words[0];
        $withAccent = $words[1];

        $newPlural = "'$withoutAccent' => '$withAccent'," . PHP_EOL;
        $newPlural .= $this->tabs(2) . "// {{ laravue-insert:accentuation }}";

        $newConfigFile = str_replace( '// {{ laravue-insert:accentuation }}', $newPlural, $configFile );

        // Override teh file
        $this->files->put( $path, $newConfigFile );

        $date = now();
        $this->info("$date - [ New Accentuation ] >> config.php");
    }

    /**
     * Cria nova entrada de configuração para plural
     *
     * @return void
     */
    protected function learnPlural( $words )
    {
        // Get file path
        $path = $this->getPath();

        // Get the file
        $configFile = $this->files->get( $path );

        // Transform data
        $singular = $words[0];
        $plural = $words[1];

        $newPlural = "'$singular' => '$plural'," . PHP_EOL;
        $newPlural .= $this->tabs(2) . "// {{ laravue-insert:plural }}";

        $newConfigFile = str_replace( '// {{ laravue-insert:plural }}', $newPlural, $configFile );

        // Override teh file
        $this->files->put( $path, $newConfigFile );

        $date = now();
        $this->info("$date - [ New Plural ] >> config.php");
    }

    /**
     * Cria nova entrada de configuração para rótulos do component select
     *
     * @return void
     */
    protected function learnSelectLabel( $words )
    {
        // Get file path
        $path = $this->getPath();

        // Get the file
        $configFile = $this->files->get( $path );

        // Transform data
        $label = $words[0];

        $newLabel = "'$label'," . PHP_EOL;
        $newLabel .= $this->tabs(2) . "// {{ laravue-insert:selectlabel }}";

        $newConfigFile = str_replace( '// {{ laravue-insert:selectlabel }}', $newLabel, $configFile );

        // Override teh file
        $this->files->put( $path, $newConfigFile );

        $date = now();
        $this->info("$date - [ New Label ] >> config.php");
    }
}
