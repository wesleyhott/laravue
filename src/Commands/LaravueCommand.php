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
                if( is_array( $model ) && count( $model ) > 1 ) {
                    $model1 = Str::snake( $model[0] );
                    $model2 = Str::snake( $model[1] );
                    $path = $this->makePath( "database/migrations/${prefix}_create_${model1}_${model2}_table.$ext", true );
                } else {
                    $model = is_array( $model ) ? Str::snake($model[0]) : Str::snake($model);
                    $path = $this->makePath( "database/migrations/${prefix}_create_${model}_table.$ext", true );
                }
                break;
            case 'seed':
                if( is_array( $model ) && count( $model ) > 1 ) {
                    $model1 = $model[0];
                    $model2 = $model[1];
                    $path = $this->makePath( "database/seeders/${model1}${model2}Seeder.php", true );
                } else {
                    $parsedModel = is_array( $model ) ? $model[0] : $model;
                    $path = $this->makePath( "database/seeders/${parsedModel}Seeder.php", true );
                }
                break;
            case 'seeder':
                $path = $this->makePath( "database/seeders/DatabaseSeeder.php", true );
                break;
            case 'front-modal':
                $paths = explode( "/", str_replace( '\\', '/', $currentDirectory) );

                $buildPath = $this->fileBuildPath( 'src', 'components', $this->projectName, 'Views', 'Pages', $model, 'forms' );
                if ( end( $paths ) == "laravue") { // Laravue Tests
                    $frontPath = $this->fileBuildPath( $currentDirectory, 'Frontend', $buildPath);
                } else if ( $this->option('outdocker') ) {
                    $frontPath = Str::replaceFirst( end( $paths ), $this->fileBuildPath( 'frontend', $buildPath ), $currentDirectory);
                } else { 
                    $frontPath = Str::replaceFirst( end( $paths ), $buildPath, $currentDirectory);
                }

                if( !is_dir($frontPath) ) {
                    mkdir( $frontPath, 0777, true);
                }
                $path = $this->fileBuildPath($frontPath, 'Modal.vue' );
                break;
            default:
                $path = $this->makePath( $this->fileBuildPath( 'Models', "$model.$ext" ) );
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
        $currentDirectory = getcwd();
        $paths = explode( "/", str_replace( '\\', '/', $currentDirectory ) );

        if ( end( $paths ) == "laravue" ) { // Laravue Tests
            $frontDirectory = $this->fileBuildPath( $currentDirectory, 'Frontend', 'LaravueTest', 'Views', 'Pages', $name );
        } else if ( $this->option('outdocker') ) {
            $buildPath = $this->fileBuildPath( 'frontend', 'src', 'components', $this->projectName, 'Views', 'Pages', $name );
            $frontDirectory = Str::replaceFirst( end( $paths ), $buildPath, $currentDirectory);
        } else {
            $buildPath = $this->fileBuildPath( 'src', 'components', $this->projectName, 'Views', 'Pages', $name );
            $frontDirectory = Str::replaceFirst( end( $paths ), $buildPath, $currentDirectory );
        }

        if( !is_dir($frontDirectory) ) {
            mkdir( $frontDirectory, 0777, true);
        }

        $file = $filename ? "$frontDirectory/$filename.$ext" : "$frontDirectory/$name.$ext";
        
        return $file;
    }

    /**
     * Get the Docker destination path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getDockerPath($file)
    {
        $folders = "";
        $parsedFile = str_replace( '\\', '/', $file );

        if( strpos( $parsedFile, "/" ) !== false ) {
            $folders = explode("/", $parsedFile);
            $parsedFile = array_pop( $folders );
            
            $folders = '/'. implode( "/", $folders );
        }
        
        $currentDirectory =  getcwd();
        $paths = explode( "/", str_replace( '\\', '/', $currentDirectory ) );

        if ( end( $paths ) == "laravue" ) { // Laravue Tests
            $dockerDirectory = $this->fileBuildPath( $currentDirectory, 'Docker'.$folders );
        } else {
            $buildPath = $this->fileBuildPath( 'Docker'.$folders );
            $dockerDirectory = Str::replaceFirst( end( $paths ), $buildPath, $currentDirectory);
        }

        if( !is_dir($dockerDirectory) ) {
            mkdir( $dockerDirectory, 0777, true);
        }

        return "$dockerDirectory/$parsedFile";
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
        $paths = explode( "/", str_replace( '\\', '/', $currentDirectory ) );

        if( end( $paths ) == "laravue") { // Laravue Tests
            $frontDirectory = $this->fileBuildPath( $currentDirectory, 'Frontend', 'LaravueTest', 'Views', 'Pages', $name, 'forms' );
        } else if ( $this->option('outdocker') ) {
            $buildPath = $this->fileBuildPath( 'frontend', 'src', 'components', $this->projectName, 'Views', 'Pages', $name, 'forms' );
            $frontDirectory = Str::replaceFirst( end( $paths ), $buildPath, $currentDirectory);
        } else { 
            $buildPath = $this->fileBuildPath( 'src', 'components', $this->projectName, 'Views', 'Pages', $name, 'forms' );
            $frontDirectory = Str::replaceFirst( end( $paths ), $buildPath, $currentDirectory);
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
            case 'User': return 'Users';
        }

        $ending_letters = substr($singular, -4);
        switch($ending_letters) {
            case 'user': 
            case 'User': 
                return substr($singular, 0, -3).'sers';
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
            case 'r':
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
     * Replace the relationship for the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @param  string  $fields
     * @return string $stub
     */
    protected function replaceMxNRelation($stub, $model, $fields)
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
    protected function replaceTable($stub, $model, $plural = true )
    {
        if( $plural ) {
            $model = $this->pluralize( 2, $model );
        }
        return str_replace( '{{ table }}', Str::snake( $model ) , $stub );
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
        if( is_array( $model ) ) {
            return $this->replaceRelation($table, $model, $fields);
        }
        
        $stub = $this->files->get($this->getStub());
        $isPlural = true;
        $title = $this->replaceTitle($stub, $model, $isPlural);
        $route = $this->replaceRoute($title, $model);
        $field = $this->replaceField($route, $model);
        $table = $this->replaceTable($field, $model);
        $relation = $this->replaceRelation($table, $model, $fields);

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

        if( is_array($model) && count( $model ) > 1 ) { // mxn
            $class = $this->replaceClass($stub, $model[0] . $model[1]); 
            $table = $this->replaceTable($class, $model[0] . $model[1], $plural = false);
            return $this->replaceField($table, $model);
        } 

        $parsedModel =  is_array($model) ? $model[0] : $model;
        $class = $this->replaceClass($stub, $parsedModel);
        $table = $this->replaceTable($class, $parsedModel);

        return $this->replaceField($table, $parsedModel);
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

        if( is_array($model) ) { // mxn
            $class = $this->replaceClass($stub, $model[0] . $model[1]);
            $table = $this->replaceTable($class, $model[0] . $model[1], $plural = false);
            return $this->replaceField($table, $model);
        }

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
        if( !isset( $options) ) {
            return [];
        }
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
    
    protected function dropDefault($field) {
        $default = $this->hasDefault($field);

        if( $default !== false ) {
            $field = str_replace( $default, '', $field );
        }

        return $field;
    }

    /**
     * Retorna verdade se o field contém a letra n (nullable); falso caso contrário.
     *
     * @param  string  $field
     * @return boolean nullable
     */
    protected function hasNullable($field) {
        // default may contain letter n
        $field = $this->dropDefault( $field );

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
     * Retorna verdade se o field contém o caractere # (default); falso caso contrário.
     *
     * @param  string  $field
     * @return any default
     */
    protected function hasDefault($field) {
        $options = $this->getOptionsArray($field);
        $hasDefault = false;
        foreach ($options as $option){
            if( strpos( $option, '#') !== false ) {
                $defaultArray = explode( '#', $option );
                $hasDefault = $defaultArray[1];
            }
        }
        return $hasDefault;
    }

    /**
     * Retorna verdade se o field contém o caractere + (unsigned); falso caso contrário.
     *
     * @param  string  $field
     * @return any default
     */
    protected function isUnsigned($field) {
        // default may contain carcater +
        $field = $this->dropDefault( $field );

        $options = $this->getOptionsArray($field);
        $isUnsigned = false;
        foreach ( $options as $option ) {
            if( strpos( $option, '+') !== false ) {
                $isUnsigned = true;
            }
        }
        return $isUnsigned;
    }

    /**
     * Retorna verdade se o field contém a letra u e não contém * (unique single); falso caso contrário.
     *
     * @param  string  $field
     * @return boolean unique
     */
    protected function isUnique($field) {
        // default may contain letter u
        $field = $this->dropDefault( $field );

        $options = $this->getOptionsArray($field);
        $unique = false;
        foreach ($options as $option){
            if( ( strpos( $option, 'u') !== false ) && ( strpos( $option, '*') === false ) ) {
                $unique = true;
            }
        }
        return $unique;
    }

    /**
     * Retorna verdade se o field contém a letra u e não contém * (unique single); falso caso contrário.
     *
     * @param  string  $field
     * @return boolean uniqueArray
     */
    protected function isUniqueArray($field) {
        // default may contain letter u*
        $field = $this->dropDefault( $field );

        $options = $this->getOptionsArray($field);
        $uniqueArray = false;
        foreach ($options as $option){
            if( strpos( $option, 'u*') !== false ) {
                $uniqueArray = true;
            }
        }
        return $uniqueArray;
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
     * Retorna verdade se o field contém a letra b (boolean); falso caso contrário.
     *
     * @param  string  $field
     * @return boolean boolean
     */
    protected function isBoolean($field) {
        $options = $this->getOptionsArray($field);
        $boolean = false;
        foreach ($options as $option){
            if( strpos( $option, 'b') !== false ) {
                $boolean = true;
            }
        }
        return $boolean;
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
        $title = ucwords( str_replace( "_", " ", $title ) );
        // Setting space before uppercase letters
        preg_match_all( '/[A-Z]/', $model, $matches, PREG_OFFSET_CAPTURE );
        for($i = 0; $i < count($matches[0]); $i++) {
            $upperLetter = $matches[0][$i][0];
            $title = str_replace( $upperLetter, " $upperLetter", $title );
        }
        $title = preg_replace('/\s+/', ' ', $title); // same initial letter issue

        $words = explode( ' ', trim( $title ) );
        foreach ( $words as $key => $value ) {
            $words[$key] = $this->tilCedilha( $this->accentuation( $value ) );
        }

        $title = implode( ' ', $words );

        return $title;
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

    protected function accentuation( $word ) {
        switch( $word ) {
            case 'Analise': return 'Análise';
            case 'Analises': return 'Análises';
            case 'Ausencia': return 'Ausência';
            case 'Ausencias': return 'Ausências';
            case 'Codigo': return 'Código';
            case 'Codigos': return 'Códigos';
            case 'Horaria': return 'Horária';
            case 'Horarias': return 'Horárias';
            case 'Inicio': return 'Início';
            case 'Inicios': return 'Inícios';
            case 'Matricula': return 'Matrícula';
            case 'Matriculas': return 'Matrículas';
            case 'Mes': return 'Mês';
            case 'Obrigatoria': return 'Obrigatória';
            case 'Obrigatorias': return 'Obrigatórias';
            case 'Ocorrencia': return 'Ocorrência';
            case 'Ocorrencias': return 'Ocorrências';
            case 'Responsavel': return 'Responsável';
            case 'Responsaveis': return 'Responsáveis';
            case 'Tacita': return 'Tácita';
            case 'Tacitas': return 'Tácitas';
            case 'Usuario': return 'Usuário';
            case 'Usuarios': return 'Usuários';
            default: return $word;
        }
    }

    /**
    * Builds a file path with the appropriate directory separator.
    * @param string $segments,... unlimited number of path segments
    * @return string Path
    */
    protected function fileBuildPath(...$segments) {
        return join(DIRECTORY_SEPARATOR, $segments);
    }

    /**
    * Returns a proper field that must be used as label for model.
    *
    * @param $fields an array of model fields
    * @return string label field
    */
    protected function getLabel( $fields ) {
        if (array_key_exists("label", $fields)) {
            return 'label';
        }
        if (array_key_exists("name", $fields)) {
            return 'name';
        }
        if (array_key_exists("nome", $fields)) {
            return 'nome';
        }
        if (array_key_exists("title", $fields)) {
            return 'title';
        }
        if (array_key_exists("titulo", $fields)) {
            return 'titulo';
        }
        if (array_key_exists("description", $fields)) {
            return 'description';
        }
        if (array_key_exists("descricao", $fields)) {
            return 'descricao';
        }
        if (array_key_exists("desc", $fields)) {
            return 'desc';
        }
        if (array_key_exists("text", $fields)) {
            return 'text';
        }
        if (array_key_exists("sigla", $fields)) {
            return 'sigla';
        }
        if (array_key_exists("uf", $fields)) {
            return 'uf';
        }
        if (array_key_exists("code", $fields)) {
            return 'code';
        }
        if (array_key_exists("codigo", $fields)) {
            return 'codigo';
        }

        return "id";
    }

}