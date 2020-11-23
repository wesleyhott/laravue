<?php

namespace Mpmg\Laravue\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class LaravueCommand extends Command
{
    protected $hidden = true;

    /**
     * O nome do projeto atual.
     *
     * @var string
     */
    protected $projectName;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Classe base dos comandos personalizados do Laravue';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * Caminho relativo a pasta stubs
     *
     * @var string
     */
    protected $stubPath = 'default';

    /**
     * Tipo de modelo que está sendo criado.
     *
     * @var string
     */
    protected $type = 'model';

    /**
     * Create a new controller creator command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct($files);

        $this->files = $files;
        $this->composer = $composer;
        $this->projectName = config('app.name', "Laravue");
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return $this->laravel->getNamespace();
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (! $this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $model
     * @return string
     */
    protected function getPath($model, $ext = 'php')
    { 
        $path = '';
        $currentDirectory =  getcwd();
        switch ($this->type) {
            case 'model':
                $path = $this->makePath( "Models/$model.$ext" );
                break;
            case 'controller': 
                $path = $this->makePath( "Http/Controllers/${model}Controller.$ext" );
                break;
            case 'report':
                $path = $this->makePath( "Http/Controllers/Reports/${model}ReportController.$ext" );
                break;
            case 'route':
                $path = $this->makePath( "routes/api.php", true );
                break;
            case 'permission':
                $path = $this->makePath( "database/seeders/LaravueSeeder.php", true );
                break;
            case 'migration':
                $prefix = date('Y_m_d_His');
                $model = Str::snake($model);
                $path = $this->makePath( "database/migrations/$prefix"."_create_$model"."_table.$ext", true );
                break;
            case 'seed':
                $path = $this->makePath( "database/seeders/$model"."Seeder.php", true );
                break;
            case 'seeder':
                $path = $this->makePath( "database/seeders/DatabaseSeeder.php", true );
                break;
            case 'front-modal':
                $dirs = explode( "/", $currentDirectory );

                if( end( $dirs ) == "laravue") { // Laravue Tests
                    $frontPath = "$currentDirectory/Frontend/src/components/$this->projectName/Views/Pages/$model/forms";
                } else { 
                    $frontPath = Str::replaceFirst( end( $dirs ), "src/components/$this->projectName/Views/Pages/$model/forms", $currentDirectory);
                }

                if( !is_dir($frontPath) ) {
                    mkdir( $frontPath, 0777, true);
                }
                $path = "$frontPath/Modal.vue";
                break;
            default:
                $path = $this->makePath("/Models/$model.$ext");
        }
        
        return $path;
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getFrontPath($name, $filename = null,  $ext = 'vue')
    {
        $currentDirectory =  getcwd();
        $paths = explode( "/", $currentDirectory );

        if( end( $paths ) == "api") { // laravel
            $frontDirectory = Str::replaceFirst( end( $paths ),"frontend/src/components/$this->projectName/Views/Pages/$name", $currentDirectory);
        } else { // docker
            $frontDirectory = Str::replaceFirst( end( $paths ), "src/components/$this->projectName/Views/Pages/$name", $currentDirectory);
        }

        if( !is_dir($frontDirectory) ) {
            mkdir( $frontDirectory, 0777, true);
        }

        $file = $filename ? "$frontDirectory/$filename.$ext" : "$frontDirectory/$name.$ext";
        
        return $file;
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getFrontFormsPath($name, $filename = null,  $ext = 'vue')
    {
        $currentDirectory =  getcwd();
        $paths = explode( "/", $currentDirectory );

        if( end( $paths ) == "laravue") { // Laravue Tests
            $frontDirectory = "$currentDirectory/Frontend/LaravueTest/Views/Pages/$name/forms";
        } else { 
            $frontDirectory = Str::replaceFirst( end( $paths ), "src/components/$this->projectName/Views/Pages/$name/forms", $currentDirectory);
        }

        if( !is_dir($frontDirectory) ) {
            mkdir( $frontDirectory, 0777, true);
        }

        $file = $filename ? "$frontDirectory/$filename.$ext" : "$frontDirectory/$name.$ext";
        
        return $file;
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.$stub;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath("/stubs/$this->stubPath.stub");
    }

    /**
     * Set the stub path file for the command.
     *
     * @return string
     */
    protected function setStub( $path ) {
        $this->stubPath = $path;
    }

    /**
     * Pluralizes a word if quantity is not one.
     *
     * @param int $quantity Number of items
     * @param string $singular Singular form of word
     * @param string $plural Plural form of word; function will attempt to deduce plural form from singular if not provided
     * @return string Pluralized word if quantity is not one, otherwise singular
     */
    public static function pluralize($quantity, $singular, $plural=null) {
        if( $quantity == 1 || !strlen($singular) ) return $singular;
        if( $plural !== null ) return $plural;

        // Exceções
        switch($singular) {
            case 'Acordao': return 'Acordaos';
            case 'Cidadao': return 'Cidadaos';
            case 'Orgao': return 'Orgaos';
            case 'Vao': return 'Vaos';
            case 'Cao': return 'Caes';
            case 'Mal': return 'Males';
            case 'Missil': return 'Misseis';
            case 'Reptil': return 'Repteis';
        }

        $ending_letters = substr($singular, -2);
        switch($ending_letters) {
            case 'ao': 
                return substr($singular, 0, -2).'oes';
            case 'al': 
                return substr($singular, 0, -2).'ais';
            case 'el': 
                return substr($singular, 0, -2).'eis';
            case 'il': 
                return substr($singular, 0, -2).'is';
            case 'ol': 
                return substr($singular, 0, -2).'ois';
        }

        $last_letter = strtolower($singular[strlen($singular)-1]);
        switch($last_letter) {
            case 'm':
                return substr($singular,0,-1).'ns';
            case 'y':
                return substr($singular,0,-1).'ies';
            case 's':
                return $singular.'es';
            default:
                return $singular.'s';
        }
    }

    /**
     * Replace the model name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceModel($stub, $model)
    {
        return str_replace(['{{ model }}', '{{ class }}'],  $model, $stub);
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $model)
    {
        return str_replace('{{ class }}',  $model, $stub);
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceRoute($stub, $model)
    {
        return str_replace( '{{ route }}', strtolower( $this->pluralize( 2, $model ) ) , $stub );
    }

    /**
     * Replace the fields for the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @return string
     */
    protected function replaceField($stub, $model)
    {
        return str_replace( '{{ fields }}', "" , $stub );
    }

    /**
     * Replace the relationship for the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @param  string  $fields
     * @return string $stub
     */
    protected function replaceRelation($stub, $model, $fields)
    {
        // {{ laravue-insert:relationship }} must be implemented
        return $stub;
    }

    /**
     * Replace the plural for class in the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @return string
     */
    protected function replacePluralClass($stub, $model)
    {
        return str_replace( '{{ pluralclass }}', ucfirst( $this->pluralize( 2, $model ) ) , $stub );
    }

    /**
     * Replace the plural for class in the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @return string
     */
    protected function replaceTable($stub, $model)
    {
        return str_replace( '{{ table }}', Str::snake( $this->pluralize( 2, $model ) ) , $stub );
    }

    /**
     * Replace the title for the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @param  string  $isPlural
     * @return string
     */
    protected function replaceTitle( $stub, $model, $isPlural = false )
    { 
        return str_replace( '{{ title }}',  $this->getTitle( $model, $isPlural ), $stub );
    }

    /**
     * Build the model.
     *
     * @param  string  $model
     * @param  string  $fields = null
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildModel($model, $fields = null)
    {
        $stub = $this->files->get($this->getStub());
        $isPlural = true;
        $title = $this->replaceTitle($stub, $model, $isPlural);
        $route = $this->replaceRoute($title, $model);
        $field = $this->replaceField($route, $model);
        $relation = $this->replaceRelation($field, $model, $fields);

        return $this->replaceModel($relation, $model);
    }

    /**
     * Build the class with the given model.
     *
     * @param  string  $model
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildMigration($model)
    {
        $stub = $this->files->get($this->getStub());
        $class = $this->replaceClass($stub, $model);
        $table = $this->replaceTable($class, $model);

        return $this->replaceField($table, $model);
    }

    /**
     * Build the class with the given model.
     *
     * @param  string  $model
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildSeed($model)
    {
        $stub = $this->files->get($this->getStub());
        $class = $this->replaceClass($stub, $model);
        $table = $this->replaceTable($class, $model);

        return $this->replaceField($table, $model);
    }
    
    /**
     * Alphabetically sorts the imports for the given stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function sortImports($stub)
    {
        if (preg_match('/(?P<imports>(?:use [^;]+;$\n?)+)/m', $stub, $match)) {
            $imports = explode("\n", trim($match['imports']));

            sort($imports);

            return str_replace(trim($match['imports']), implode("\n", $imports), $stub);
        }

        return $stub;
    }

    /**
     * Monta um array a partir da string passada na option fields.
     *
     * @param  string  $options
     * @return array
     */
    protected function getFieldsArray($options) {
        $pureOptions = str_replace( "=", "", 
                       str_replace("[", "", 
                       str_replace("]", "", $options) ) );
        $arr_fileds = [];
        foreach( explode( ",", $pureOptions ) as $field ) {
            list( $key, $value ) = explode( ":", $field );
            $arr_fileds[$key] = $value;
        }

        return $arr_fileds;
    }

    /**
     * Monta um array separando o campo das opções para o campo
     *
     * @param  string  $options
     * @return array
     */
    protected function getOptionsArray($field) {
        return explode( ".", $field );
    }

    /**
     * Retorna verdade se o field contém a letra n (nullable); falso caso contrário.
     *
     * @param  string  $field
     * @return boolean nullable
     */
    protected function hasNullable($field) {
        $options = $this->getOptionsArray($field);
        $nullable = false;
        foreach ($options as $option){
            if( strpos( $option, 'n') !== false ) {
                $nullable = true;
            }
        }
        return $nullable;
    }

    /**
     * Retorna verdade se o field contém a letra u e não contém * (unique single); falso caso contrário.
     *
     * @param  string  $field
     * @return boolean nullable
     */
    protected function isUnique($field) {
        $options = $this->getOptionsArray($field);
        $nullable = false;
        foreach ($options as $option){
            if( ( strpos( $option, 'u') !== false ) && ( strpos( $option, '*') === false ) ) {
                $nullable = true;
            }
        }
        return $nullable;
    }

    /**
     * Retorna verdade se o field contém a letra u e não contém * (unique single); falso caso contrário.
     *
     * @param  string  $field
     * @return boolean nullable
     */
    protected function isUniqueArray($field) {
        $options = $this->getOptionsArray($field);
        $nullable = false;
        foreach ($options as $option){
            if( strpos( $option, 'u*') !== false ) {
                $nullable = true;
            }
        }
        return $nullable;
    }

    /**
     * Retorna falso se não contém número ou retorna o número
     *
     * @param  string  $field
     * @return boolean false or
     * @return integer number
     */
    protected function hasNumber($field) {
        $options = $this->getOptionsArray($field);
        $numbers = false;
        foreach ($options as $option){
            preg_match_all('!\d+!', $option, $matches);
            if( count( $matches[0] ) > 0 ) {
                $numbers = $matches[0];
            }
        }
        return $numbers;
    }

    /**
     * Retorna o tipo baseado na letra declarada em option
     *
     * @param  string  $value
     * @return string
     */
    protected function getType($value) {
        $options = $this->getOptionsArray($value);
        switch( $options[0] ) {
            case 'b': return 'boolean';
            case 'bpk': return 'bigIncrements';
            case 'bi': return 'bigInteger';
            case 'by': return 'binary';
            case 'c': return 'char';
            case 'd': return 'date';
            case 'db': return 'double';
            case 'de': return 'decimal';
            case 'dt': return 'dateTime';
            case 'e': return 'enum';
            case 'f': return 'float';
            case 'i': return 'integer';
            case 'lt': return 'longText';
            case 'm': return 'morph';
            case 'mi': return 'mediumInteger';
            case 'mt': return 'mediumText';
            case 'pk': return 'increments';
            case 'rt': return 'rememberToken';
            case 's': return 'string';
            case 'si': return 'smallInteger';
            case 't': return 'time';
            case 'ti': return 'tinyInteger';
            case 'ts': return 'timestamp';
            case 'tt': return 'timestamps';
            default: return 'string';
        }
    }

    /**
     * Cria o título a partir do nome do modelo
     *
     * @param  string  $value
     * @return string
     */
    protected function getTitle( $model, $plural = false ) {
        $title = $model;
        if($plural) {
            $title = $this->pluralize( 2, $model );
        }
        $title = ucwords( str_replace( "_", " ", $this->tilCedilha( $title ) ) );
        // Setting space before uppercase letters
        preg_match_all( '/[A-Z]/', $model, $matches, PREG_OFFSET_CAPTURE );
        for($i = 0; $i < count($matches[0]); $i++) {
            $upperLetter = $matches[0][$i][0];
            $title = str_replace( $upperLetter, " $upperLetter", $title );
        }
        $title = preg_replace('/\s+/', ' ', $title); // same initial letter issue

        return trim( $title );
    }

    /**
     * Coloca til e cecedilha nas palavras dadas.
     *
     * @param  string  $value
     * @return string
     */
    protected function tilCedilha( $word ) {
        $cedilha = substr($word, -4);
        switch($cedilha) {
            case 'coes':
                return substr($word,0,-4).'ções';
        }

        $cedilha = substr($word, -3);
        switch($cedilha) {
            case 'cao':
                return substr($word,0,-3).'ção';
            case 'oes':
                return substr($word,0,-3).'ões';
            case 'eis':
                return substr($word,0,-3).'éis';
            case 'ois':
                return substr($word,0,-3).'óis';
        }

        $til = substr($word, -2);
        switch($til) {
            case 'ao':
                return substr($word,0,-2).'ão';
        }

        return $word;
    }

    /**
     * Verifica se o campo passado é uma chave estrangeira.
     *
     * @param  string  $value
     * @return boolean
     */
    protected function isFk( $value ) {
        return strpos( $value, "_id") !== false;
    }

    /**
     * Insere tabulações para formatação do código.
     *
     * @param  int  $number
     * @return string
     */
    protected function tabs( $number ){
        $tab = "";
        for( $i = 0; $i < $number; $i++ ) {
            $tab .= "\t";
        }
    
        return $tab;
    }

    /**
     * Verifica se o array dado possui próximo item.
     *
     * @param  array  $array
     * @return boolean
     */
    function hasNext($array) {
        if (is_array($array)) {
            if (next($array) === false) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * Make relative app path
     *
     * @param  string  File with relative app path
     * @return string
     */
    protected function makePath( $file, $outsideApp = false ) {
        $folders = "";
        if( strpos( $file, "/" ) !== false ) {
            $folders = explode("/", $file);
            $file = array_pop( $folders );
            
            $folders = "/" . implode( "/", $folders );
        }
        
        $currentDirectory =  getcwd();
        $backPath = $outsideApp ? $currentDirectory . $folders : "$currentDirectory/app$folders";

        if( !is_dir($backPath) ) {
            mkdir( $backPath, 0777, true);
        }

        return "$backPath/$file";
    }
}