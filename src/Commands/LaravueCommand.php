<?php

namespace wesleyhott\Laravue\Commands;

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
     * Stub path 
     *
     * @var string
     */
    protected $stubPath = 'default';

    /**
     * Model type that is been created.
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
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }

    /**
     * Creates or overwrite a file with contents.
     *
     * @param  string  $path
     * @return void
     */
    protected function createFileWithContents(string $path, string $contents): void
    {
        $this->makeDirectory($path);
        $file = fopen($path, "w") or die("Unable to open file! File path: {$path}");
        fwrite($file, $contents);
        fclose($file);
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $model model name
     * @param  string  $ext file extension
     * @return string
     */
    protected function getPath($model = '', $ext = 'php', $schema = '')
    {
        $string_model = is_array($model) ? $model[0] :  $model;
        $path = '';
        $schemaPath = '';
        if ($schema != '') {
            $schemaPath = "$schema/";
        }
        $current_directory =  getcwd();
        switch ($this->type) {
            case 'model':
                $path = $this->makePath("Models/{$schemaPath}$string_model.$ext");
                break;
            case 'controller':
                $path = $this->makePath("Http/Controllers/{$schemaPath}{$string_model}Controller.$ext");
                break;
            case 'report':
                $path = $this->makePath("Http/Controllers/Reports/{$schemaPath}{$string_model}ReportController.$ext");
                break;
            case 'route':
                $path = $this->makePath("routes/api.php", true);
                break;
            case 'permission':
                $path = $this->makePath("database/seeders/LaravueSeeder.php", true);
                break;
            case 'migration':
                $prefix = date('Y_m_d_His');
                $parsed_schema = empty($schema) ? '' : strtolower("{$schema}_");
                if (is_array($model) && count($model) > 1) {
                    $model1 = Str::snake($model[0]);
                    $model2 = Str::snake($model[1]);
                    $path = $this->makePath("database/migrations/{$prefix}_create_{$parsed_schema}{$model1}_{$model2}_table.{$ext}", true);
                } else {
                    $model = is_array($model) ? Str::snake($this->pluralize($model[0])) : Str::snake($this->pluralize($model));
                    $path = $this->makePath("database/migrations/{$prefix}_create_{$parsed_schema}{$model}_table.{$ext}", true);
                }
                break;
            case 'seed':
                if (is_array($model) && count($model) > 1) {
                    $model1 = $model[0];
                    $model2 = $model[1];
                    $path = $this->makePath("database/seeders/{$schema}{$model1}{$model2}Seeder.php", true);
                } else {
                    $path = $this->makePath("database/seeders/{$schema}{$string_model}Seeder.php", true);
                }
                break;
            case 'seeder':
                $path = $this->makePath("database/seeders/DatabaseSeeder.php", true);
                break;
            case 'request':
                $type = '';
                if ($this->option('store')) {
                    $type = 'Store';
                }
                if ($this->option('update')) {
                    $type = 'Update';
                }
                $path = $this->makePath("Http/Requests/{$schemaPath}{$type}{$string_model}Request.{$ext}");
                break;
            case 'resource':
                $path = $this->makePath("Http/Resources/{$schemaPath}{$string_model}Resource.{$ext}");
                break;
            case 'service':
                $path = $this->makePath("Services/{$schemaPath}{$string_model}Service.{$ext}");
                break;
            case 'front-modal':
                $paths = explode("/", str_replace('\\', '/', $current_directory));

                $buildPath = $this->fileBuildPath('src', 'components', $this->projectName, 'Views', 'Pages', $model, 'forms');
                if (end($paths) == "laravue") { // Laravue Tests
                    $frontPath = $this->fileBuildPath($current_directory, 'admin', $buildPath);
                } else if ($this->option('outdocker')) {
                    $frontPath = Str::replaceFirst(end($paths), $this->fileBuildPath('admin', $buildPath), $current_directory);
                } else {
                    $frontPath = Str::replaceFirst(end($paths), $buildPath, $current_directory);
                }

                if (!is_dir($frontPath)) {
                    mkdir($frontPath, 0777, true);
                }
                $path = $this->fileBuildPath($frontPath, 'Modal.vue');
                break;
            case 'config':
                $paths = explode("/", str_replace('\\', '/', $current_directory));
                if (end($paths) == "laravue") { // Laravue Tests
                    $path = $this->makePath("config/config.php", true);
                } else {
                    $path = $this->makePath("config/laravue.php", true);
                }
                break;
            default:
                $path = $this->makePath($this->fileBuildPath('Models', "{$string_model}.{$ext}"));
        }

        return $path;
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $file_name
     * @return string
     */
    protected function getFrontPath($file_name)
    {
        $current_directory = getcwd();
        $paths = explode("/", str_replace('\\', '/', $current_directory));
        $parsed_module = $this->option('module') ? Str::ucfirst($this->option('module')) : '';
        $parsed_model = $this->argument('model') ? Str::ucfirst($this->argument('model')) : '';

        $front_directory = 'src';
        switch ($this->type) {
            case 'front_module_route':
                $front_directory = $this->fileBuildPath($front_directory, 'router');
                break;
            case 'front_module_index':
                $front_directory = $this->fileBuildPath($front_directory, 'router', 'modules');
                break;
            case 'front_module_page':
                $front_directory = $this->fileBuildPath($front_directory, 'pages', $parsed_module);
                break;
            case 'front_module_page_routes':
                $front_directory = $this->fileBuildPath($front_directory, 'router', 'modules');
                break;
            case 'front_model_index':
                $front_directory = $this->fileBuildPath($front_directory, 'pages', $parsed_module, $parsed_model);
                break;
        }

        $laravue_test_dir = $this->fileBuildPath($current_directory, 'front');
        if (end($paths) == "laravue" && is_dir($laravue_test_dir)) {
            return $this->fileBuildPath($laravue_test_dir, $front_directory, $file_name);
        }

        $built_path = $this->fileBuildPath('app', $front_directory);
        $front_directory = Str::replaceFirst(end($paths), $built_path, $current_directory);

        if (!is_dir($front_directory)) {
            mkdir($front_directory, 0777, true);
        }

        $file = "$front_directory/$file_name";

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
        $parsedFile = str_replace('\\', '/', $file);

        if (strpos($parsedFile, "/") !== false) {
            $folders = explode("/", $parsedFile);
            $parsedFile = array_pop($folders);

            $folders = '/' . implode("/", $folders);
        }

        $current_directory =  getcwd();
        $paths = explode("/", str_replace('\\', '/', $current_directory));

        $buildPath = $this->fileBuildPath('workspace', 'laravue' . $folders);

        $projectFolder = array_pop($paths);
        if ($projectFolder == "laravue") { // Is not Laravue Tests
            $developmentFolder = implode("/", $paths);
        } else {
            $arrayDevelopmentFolder = array_pop($paths);
            $developmentFolder = implode("/", $paths);
        }
        $dockerDirectory = $this->fileBuildPath($developmentFolder, $buildPath);

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
        $current_directory =  getcwd();
        $paths = explode("/", str_replace('\\', '/', $current_directory));

        if (end($paths) == "laravue") { // Laravue Tests
            $frontDirectory = $this->fileBuildPath($current_directory, 'admin', 'LaravueTest', 'Views', 'Pages', $name, 'forms');
        } else if ($this->option('outdocker')) {
            $buildPath = $this->fileBuildPath('admin', 'src', 'components', $this->projectName, 'Views', 'Pages', $name, 'forms');
            $frontDirectory = Str::replaceFirst(end($paths), $buildPath, $current_directory);
        } else {
            $buildPath = $this->fileBuildPath('src', 'components', $this->projectName, 'Views', 'Pages', $name, 'forms');
            $frontDirectory = Str::replaceFirst(end($paths), $buildPath, $current_directory);
        }

        if (!is_dir($frontDirectory)) {
            mkdir($frontDirectory, 0777, true);
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
            : __DIR__ . $stub;
    }

    /**
     * Get the stub file for the generator.
     *
     * @param string $name The stub name
     * @return string The object stub_path or the given stub name
     */
    protected function getStub(string $name = null): string
    {
        $stub = empty($name) ? $this->stubPath : $name;
        return $this->resolveStubPath("/stubs/{$stub}.stub");
    }

    /**
     * Set the stub path file for the command.
     *
     * @return string
     */
    protected function setStub($path)
    {
        $this->stubPath = $path;
    }

    /**
     * Pluralizes a word if quantity is not one.
     *
     * @param string $singular Singular form of word
     * @return string Pluralized word
     */
    public static function pluralize($singular)
    {
        if (!strlen($singular)) return $singular;

        // Exceções
        $exceptions = config('laravue.plural');
        foreach ($exceptions as $key => $value) {
            if (strcmp($key, $singular) == 0) {
                return $value;
            }
        }

        $ending_letters = substr($singular, -4);
        switch ($ending_letters) {
            case 'user':
            case 'User':
                return substr($singular, 0, -3) . 'sers';
        }


        $lang = config('laravue.language');
        $ending_letters = substr($singular, -2);
        if ($lang === 'pt-BR') {
            switch ($ending_letters) {
                case 'ao':
                    return substr($singular, 0, -2) . 'oes';
                case 'al':
                    return substr($singular, 0, -2) . 'ais';
                case 'el':
                    return substr($singular, 0, -2) . 'eis';
                case 'il':
                    return substr($singular, 0, -2) . 'is';
                case 'ol':
                    return substr($singular, 0, -2) . 'ois';
            }
        }
        if ($lang === 'en') {
            switch ($ending_letters) {
                case 'ss':
                case 'sh':
                case 'ch':
                    return $singular . 'es';
                case 'fe':
                    return substr($singular, 0, -1) . 'ves';
                case 'io':
                case 'oo':
                    return $singular . 's';
                case 'us':
                    return substr($singular, 0, -2) . 'i';
            }
        }

        $last_letter = strtolower($singular[strlen($singular) - 1]);
        if ($lang === 'pt-BR') {
            switch ($last_letter) {
                case 'm':
                    return substr($singular, 0, -1) . 'ns';
                case 'y':
                    return substr($singular, 0, -1) . 'ies';
                case 's':
                case 'r':
                    return $singular . 'es';
                case 'f':
                    return substr($singular, 0, -1) . 'ves';
                default:
                    return $singular . 's';
            }
        }
        if ($lang === 'en') {
            switch ($last_letter) {
                case 's':
                case 'x':
                case 'z':
                case 'o':
                    return $singular . 'es';
                case 'y':
                    return substr($singular, 0, -1) . 'ies';
                default:
                    return $singular . 's';
            }
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
        return str_replace('{{ route }}', strtolower($this->pluralize($model)), $stub);
    }

    /**
     * Replace the fields for the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @param  string  $schema
     * @return string
     */
    protected function replaceField($stub, $model = null, $schema = null)
    {
        return str_replace('{{ fields }}', "", $stub);
    }

    /**
     * Replace the relationship for the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @param  string  $fields
     * @return string $stub
     */
    protected function replaceRelation($stub, $model, $fields, $schema)
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
        return str_replace('{{ pluralclass }}', ucfirst($this->pluralize($model)), $stub);
    }

    /**
     * Replace the plural for class in the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @return string
     */
    protected function replaceTable($stub, $model, $plural = true)
    {
        if ($plural) {
            $model = $this->pluralize($model);
        }
        return str_replace('{{ table }}', Str::snake($model), $stub);
    }

    /**
     * Replace the Schema name in the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @return string
     */
    protected function replaceSchema($stub, $schema)
    {
        if (empty($schema)) {
            return str_replace('{{ schema }}', "", $stub);
        }
        return str_replace('{{ schema }}', ucfirst($schema), $stub);
    }

    /**
     * Replace the Schema Namespace in the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @return string
     */
    protected function replaceSchemaNamespace($stub, $schema)
    {
        if (empty($schema)) {
            return str_replace('{{ schemaNamespace }}', "", $stub);
        }
        return str_replace('{{ schemaNamespace }}', "\\" . ucfirst($schema), $stub);
    }

    /**
     * Replace the Schema Table in the given stub.
     * Used in migration and seeder
     *
     * @param  string  $stub
     * @param  string  $model
     * @return string
     */
    protected function replaceSchemaTable($stub, $schema)
    {
        $replacement = empty($schema) ? '' : strtolower("$schema.");
        return str_replace('{{ schemaTable }}', $replacement, $stub);
    }

    /**
     * Replace the Schema Route in the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @return string
     */
    protected function replaceSchemaRoute($stub, $schema)
    {
        return str_replace('{{ schemaRoute }}', strtolower($schema), $stub);
    }

    /**
     * Replace the Schema Namespace in the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @return string
     */
    protected function replaceModelVar($stub, $model)
    {
        if (empty($model)) {
            return str_replace('{{ modelVar }}', "", $stub);
        }
        return str_replace('{{ modelVar }}', Str::snake($model), $stub);
    }

    /**
     * Replace the title for the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @param  string  $isPlural
     * @return string
     */
    protected function replaceTitle($stub, $model, $isPlural = false)
    {
        return str_replace('{{ title }}',  $this->getTitle($model, $isPlural), $stub);
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
    protected function buildModel($model, $fields = null, $schema = null)
    {
        $stub = $this->files->get($this->getStub());

        if (is_array($model)) {
            return $this->replaceRelation($stub, $model, $fields, $schema);
        }

        $isPlural = true;
        $title = $this->replaceTitle($stub, $model, $isPlural);
        $route = $this->replaceRoute($title, $model);
        $field = $this->replaceField($route, $model);
        $table = $this->replaceTable($field, $model);
        $relation = $this->replaceRelation($table, $model, $fields, $schema);
        $parsedSchema = $this->replaceSchemaNamespace($relation, $schema);

        return $this->replaceModel($parsedSchema, $model);
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
    protected function getFieldsArray($options)
    {
        if (!isset($options)) {
            return [];
        }
        $pureOptions = str_replace(
            "=",
            "",
            str_replace(
                "[",
                "",
                str_replace("]", "", $options)
            )
        );
        $arr_fileds = [];
        foreach (explode(",", $pureOptions) as $field) {
            // Foreing Keys are big integer by default
            $hasType = str_contains($field, ':');
            if ($hasType) {
                list($key, $value) = explode(":", $field);
                $arr_fileds[$key] = $value;
            } else {
                // Fields are string by default
                $isFK = str_contains($field, '_id');
                $arr_fileds[$field] = $isFK ? 'bi' : 's';
            }
        }

        return $arr_fileds;
    }

    /**
     * Monta um array separando o campo das opções para o campo
     *
     * @param  string  $options
     * @return array
     */
    protected function getOptionsArray($field)
    {
        return explode(".", $field);
    }

    protected function dropDefault($field)
    {
        $default = $this->hasDefault($field);

        if ($default !== false) {
            /** @var string $default */
            $field = str_replace($default, '', $field);
        }

        return $field;
    }

    /**
     * Retorna verdade se o field contém a letra n (nullable); falso caso contrário.
     *
     * @param  string  $field
     * @return boolean nullable
     */
    protected function hasNullable($field)
    {
        // default may contain letter n
        $field = $this->dropDefault($field);

        $options = $this->getOptionsArray($field);
        $nullable = false;
        foreach ($options as $option) {
            if (strpos($option, 'n') !== false) {
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
    protected function hasDefault($field)
    {
        $options = $this->getOptionsArray($field);
        $hasDefault = false;
        foreach ($options as $option) {
            if (strpos($option, '#') !== false) {
                $defaultArray = explode('#', $option);
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
    protected function isUnsigned($field)
    {
        // default may contain carcater +
        $field = $this->dropDefault($field);

        $options = $this->getOptionsArray($field);
        $isUnsigned = false;
        foreach ($options as $option) {
            if (strpos($option, '+') !== false) {
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
    protected function isUnique($field)
    {
        // default may contain letter u
        $field = $this->dropDefault($field);

        $options = $this->getOptionsArray($field);
        $unique = false;
        foreach ($options as $option) {
            if ((strpos($option, 'u') !== false) && (strpos($option, 'uc') === false)) {
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
    protected function isUniqueComposition($field)
    {
        // default may contain letter uc
        $field = $this->dropDefault($field);

        $options = $this->getOptionsArray($field);
        $uniqueArray = false;
        foreach ($options as $option) {
            if (strpos($option, 'uc') !== false) {
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
    protected function hasNumber($field)
    {
        $options = $this->getOptionsArray($field);
        $numbers = false;
        foreach ($options as $option) {
            preg_match_all('!\d+!', $option, $matches);
            if (count($matches[0]) > 0) {
                $numbers = $matches[0];
            }
        }
        return $numbers;
    }

    /**
     * Returns false if do not contains number or
     * Returns precision numbers array
     *
     * @param  string  $field
     * @return false|array $precision_numbers
     */
    protected function getPrecisionNumbers(string $field)
    {
        $options = $this->getOptionsArray($field);
        $numbers = false;
        if (isset($options[1])) {
            $parsed_numbers = preg_replace("/[^0-9\-]/", "", $options[1]);
            $numbers = explode('-', $parsed_numbers);
        }

        return $numbers;
    }

    /**
     * Retorna verdade se o field contém a letra b (boolean); falso caso contrário.
     *
     * @param  string  $field
     * @return boolean boolean
     */
    protected function isBoolean($field)
    {
        $options = $this->getOptionsArray($field);
        $boolean = false;
        foreach ($options as $option) {
            if (strpos($option, 'b') !== false) {
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
    protected function getType($value)
    {
        $options = $this->getOptionsArray($value);
        switch ($options[0]) {
            case 'b':
                return 'boolean';
            case 'bpk':
                return 'bigIncrements';
            case 'bi':
                return 'bigInteger';
            case 'by':
                return 'binary';
            case 'c':
                return 'char';
            case 'cnpj':
                return 'cnpj';
            case 'cpf':
                return 'cpf';
            case 'cpfcnpj':
                return 'cpfcnpj';
            case 'd':
                return 'date';
            case 'db':
                return 'double';
            case 'de':
                return 'decimal';
            case 'dt':
                return 'dateTime';
            case 'e':
                return 'enum';
            case 'f':
                return 'float';
            case 'fj':
                return 'cpfcnpj';
            case 'i':
                return 'integer';
            case 'lt':
                return 'longText';
            case 'm':
                return 'morph';
            case 'mi':
                return 'mediumInteger';
            case 'money':
                return 'monetario';
            case 'mt':
                return 'mediumText';
            case 'pf':
                return 'cpf';
            case 'pj':
                return 'cnpj';
            case 'pk':
                return 'increments';
            case 'rt':
                return 'rememberToken';
            case 's':
                return 'string';
            case 'si':
                return 'smallInteger';
            case 't':
                return 'time';
            case 'ti':
                return 'tinyInteger';
            case 'ts':
                return 'timestamp';
            case 'tt':
                return 'timestamps';
            case 'tx':
                return 'text';
            case 'mv':
                return 'monetario';
            default:
                return 'string';
        }
    }

    /**
     * Creates a title from field
     *
     * @param string  $field 
     * @param boolean $plural
     * @return string
     */
    protected function getTitle($field, $plural = false)
    {
        $title = str_replace('_id', '', $field);
        if ($plural) {
            $title = $this->pluralize($title);
        }
        $title = ucwords(str_replace("_", " ", $title));
        // Setting space before uppercase letters
        preg_match_all('/[A-Z]/', $field, $matches, PREG_OFFSET_CAPTURE);
        for ($i = 0; $i < count($matches[0]); $i++) {
            $upperLetter = $matches[0][$i][0];
            $title = str_replace($upperLetter, " $upperLetter", $title);
        }
        $title = preg_replace('/\s+/', ' ', $title); // same initial letter issue

        $words = explode(' ', trim($title));
        foreach ($words as $key => $value) {
            $words[$key] = $this->tilCedilha($this->accentuation($value));
        }

        $title = implode(' ', $words);

        return $title;
    }

    /**
     * Coloca til e cecedilha nas palavras dadas.
     *
     * @param  string  $value
     * @return string
     */
    protected function tilCedilha($word)
    {
        $cedilha = substr($word, -4);
        switch ($cedilha) {
            case 'coes':
                return substr($word, 0, -4) . 'ções';
        }

        $cedilha = substr($word, -3);
        switch ($cedilha) {
            case 'cao':
                return substr($word, 0, -3) . 'ção';
            case 'oes':
                return substr($word, 0, -3) . 'ões';
            case 'eis':
                return substr($word, 0, -3) . 'éis';
            case 'ois':
                return substr($word, 0, -3) . 'óis';
        }

        $til = substr($word, -2);
        switch ($til) {
            case 'ao':
                return substr($word, 0, -2) . 'ão';
        }

        return $word;
    }

    /**
     * Verifica se o campo passado é uma chave estrangeira.
     *
     * @param  string  $value
     * @return boolean
     */
    protected function isFk($value)
    {
        return strpos($value, "_id") !== false;
    }

    /**
     * Insere tabulações para formatação do código.
     *
     * @param  int  $number
     * @return string
     */
    protected function tabs($number)
    {
        $tab = "";
        for ($i = 0; $i < $number; $i++) {
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
    function hasNext($array)
    {
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
    protected function makePath($file, $outside_app = false)
    {
        $folders = "";
        $folder_separator = DIRECTORY_SEPARATOR;
        if (strpos($file, "/") !== false) {
            $folders = explode("/", str_replace('\\', '/', $file));
            $file = array_pop($folders);

            $folders = $folder_separator . implode($folder_separator, $folders);
        }


        $current_directory =  getcwd();
        $back_path = $outside_app ? $current_directory . $folders : "{$current_directory}{$folder_separator}app{$folders}";

        if (!is_dir($back_path)) {
            mkdir($back_path, 0777, true);
        }

        return "{$back_path}{$folder_separator}{$file}";
    }

    protected function accentuation($word)
    {
        $accentuations = config('laravue.accentuation');
        foreach ($accentuations as $key => $value) {
            if (strcmp($key, $word) == 0) {
                return $value;
            }
        }

        return $word;
    }

    /**
     * Builds a file path with the appropriate directory separator.
     * @param string $segments,... unlimited number of path segments
     * @return string Path
     */
    protected function fileBuildPath(...$segments)
    {
        return join(DIRECTORY_SEPARATOR, $segments);
    }

    /**
     * Returns a proper field that must be used as label for select model.
     *
     * @param $fields an array of model fields
     * @return string label field
     */
    protected function getSelectLabel($fields)
    {
        $labels = config('laravue.select_label');
        foreach ($labels as $label) {
            if (in_array($label, $fields)) {
                return $label;
            }
        }

        return "id";
    }

    /**
     * Returns proper fields from a select model by key name.
     * Ex: user_id, returns fields from model User.
     *
     * @param $key an array of model fields
     * @return array modelFields
     */
    protected function getModelFieldsFromKey($key)
    {
        $modelFields = [];
        $controllerName = Str::studly(substr($key, 0, -3)) . "Controller.php";
        $path = $this->makePath("Http/Controllers/{$controllerName}");

        $controllerFile = @fopen($path, "r");
        if ($controllerFile) {
            $found = false;
            while (($line = fgets($controllerFile, 4096)) !== false) {
                if (strpos($line, '$request->input(') !== false) {
                    $found = true;
                    $splited = explode("'", $line);
                    array_push($modelFields, $splited[1]);
                }
                if ($found && strpos($line, 'return') !== false) {
                    break;
                }
            }
            if (!feof($controllerFile) && !$found) {
                echo "Erro: falha ao carregar $path\n";
            }

            fclose($controllerFile);
        }

        return $modelFields;
    }

    /**
     * Returns proper fields from a select model by key name.
     *
     * @param string $module
     * @param string $model
     * 
     * @return string $properties
     */
    protected function getLabelFromModel(string $module, string $model): string
    {
        $parsed_module = empty($module) ? '' : "{$module}/";
        $properties = [];
        $path = $this->makePath("Models/{$parsed_module}{$model}.php");

        $file = @fopen($path, "r");
        if ($file) {
            $found = false;
            while (($line = fgets($file, 4096)) !== false) {
                if (strpos($line, '@property') !== false) {
                    $found = true;
                    $splited = explode("$", $line);
                    array_push($properties, trim($splited[1]));
                }
                if ($found && strpos($line, 'extends') !== false) {
                    break;
                }
            }
            if (!feof($file) && !$found) {
                $this->info("Error: file not found in path: $path");
            }

            fclose($file);
        }

        return $this->getSelectLabel($properties);
    }

    /**
     * Returns language setted on config/laravue.php.
     * Ex: user_id, returns fields from model User.
     *
     * @return string language pt-BR or en
     */
    protected function getLanguage()
    {
        return config('laravue.language');
    }

    /**
     * Returns true is setted Brazilian Portuguese language, false otherwise.
     *
     * @return boolean isPtBR
     */
    protected function isPtBRLanguage()
    {
        return $this->getLanguage() === 'pt-BR';
    }

    /**
     * Returns true is setted EUA language, false otherwise.
     *
     * @return boolean isEUA
     */
    protected function isEnLanguage()
    {
        return $this->getLanguage() === 'en';
    }

    /**
     * Returns a string that contais de biggest number possible for a key decimal value..
     * Example: for decimal('amount', 6, 2) we have key: amount, value: 6-2. Then the
     * biggest possible number is 9999.99
     * @return string biggest_number
     */
    protected function decimalMaxSize(string $value): string
    {
        $numbers = $this->getPrecisionNumbers($value);

        if ($numbers !== false) {
            $base = '';
            for ($b = 0; $b < ($numbers[0] - $numbers[1]); $b++) {
                $base .= '9';
            }

            $digits = '';
            for ($f = 0; $f < $numbers[1]; $f++) {
                $digits .= '9';
            }

            return "{$base}.{$digits}";
        }

        return '';
    }

    // Frontend Generation
    protected function replaceLcfirstModel(string $stub, string $module): string
    {
        return str_replace('{{ lcfirst_model }}', Str::lcfirst($module), $stub);
    }

    protected function replacePluralTitle(string $stub, string $model): string
    {
        return str_replace('{{ plural_title }}',  $this->getTitle($model, true), $stub);
    }

    protected function replaceKebabModel(string $stub, string $model): string
    {
        return str_replace('{{ kebab_model }}',  Str::kebab($model), $stub);
    }

    protected function replaceRouteModel(string $stub, string $model): string
    {
        return str_replace('{{ route_model }}',  Str::kebab($this->pluralize($model)), $stub);
    }

    protected function replacePluralLcfirstModel(string $stub, string $module): string
    {
        return str_replace('{{ plural_lcfirst_model }}', $this->pluralize(Str::lcfirst($module)), $stub);
    }

    protected function replaceSelectedLabel(string $stub, string $model): string
    {
        return str_replace('{{ selected_label }}',  Str::kebab($model, true), $stub);
    }

    protected function replaceModule(string $stub, string $module): string
    {
        return str_replace('{{ module }}',  Str::ucfirst($module), $stub);
    }

    protected function replacePluralTitleModule(string $stub, string $module): string
    {
        return str_replace('{{ plural_title_module }}',  $this->getTitle($module, true), $stub);
    }

    protected function replaceSnakeModule(string $stub, string $module): string
    {
        return str_replace('{{ snake_module }}', Str::snake($module), $stub);
    }

    protected function replacePluralSnakeModule(string $stub, string $module): string
    {
        return str_replace('{{ plural_snake_module }}', $this->pluralize(Str::snake($module)), $stub);
    }

    protected function replaceUpperCaseFirstModule(string $stub, string $module): string
    {
        return str_replace('{{ ucfirst_module }}', Str::ucfirst($module), $stub);
    }

    protected function replaceInsert(string $key, string $replacement, string $stub): string
    {
        $return = str_replace("// {{ laravue-insert:{$key} }}", $replacement, $stub);
        return $return;
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
