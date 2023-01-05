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
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
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
        $path = '';
        $schemaPath = '';
        if ($schema != '') {
            $schemaPath = "$schema/";
        }
        $currentDirectory =  getcwd();
        switch ($this->type) {
            case 'model':
                $path = $this->makePath("Models/{$schemaPath}$model.$ext");
                break;
            case 'controller':
                $path = $this->makePath("Http/Controllers/{$schemaPath}{$model}Controller.$ext");
                break;
            case 'report':
                $path = $this->makePath("Http/Controllers/Reports/{$schemaPath}{$model}ReportController.$ext");
                break;
            case 'route':
                $path = $this->makePath("routes/{$schemaPath}api.php", true);
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
                    $path = $this->makePath("database/seeders/{$schemaPath}{$model1}{$model2}Seeder.php", true);
                } else {
                    $parsedModel = is_array($model) ? $model[0] : $model;
                    $path = $this->makePath("database/seeders/{$schemaPath}{$parsedModel}Seeder.php", true);
                }
                break;
            case 'seeder':
                $path = $this->makePath("database/seeders/DatabaseSeeder.php", true);
                break;
            case 'request':
                $parsedModel = is_array($model) ? $model[0] : $model;
                $type = '';
                if ($this->option('store')) {
                    $type = 'Store';
                }
                if ($this->option('update')) {
                    $type = 'Update';
                }
                $path = $this->makePath("Http/Requests/{$schemaPath}{$type}{$parsedModel}Request.{$ext}");
                break;
            case 'front-modal':
                $paths = explode("/", str_replace('\\', '/', $currentDirectory));

                $buildPath = $this->fileBuildPath('src', 'components', $this->projectName, 'Views', 'Pages', $model, 'forms');
                if (end($paths) == "laravue") { // Laravue Tests
                    $frontPath = $this->fileBuildPath($currentDirectory, 'admin', $buildPath);
                } else if ($this->option('outdocker')) {
                    $frontPath = Str::replaceFirst(end($paths), $this->fileBuildPath('admin', $buildPath), $currentDirectory);
                } else {
                    $frontPath = Str::replaceFirst(end($paths), $buildPath, $currentDirectory);
                }

                if (!is_dir($frontPath)) {
                    mkdir($frontPath, 0777, true);
                }
                $path = $this->fileBuildPath($frontPath, 'Modal.vue');
                break;
            case 'config':
                $paths = explode("/", str_replace('\\', '/', $currentDirectory));
                if (end($paths) == "laravue") { // Laravue Tests
                    $path = $this->makePath("config/config.php", true);
                } else {
                    $path = $this->makePath("config/laravue.php", true);
                }
                break;
            default:
                $path = $this->makePath($this->fileBuildPath('Models', "$model.$ext"));
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
        $paths = explode("/", str_replace('\\', '/', $currentDirectory));

        if (end($paths) == "laravue") { // Laravue Tests
            $frontDirectory = $this->fileBuildPath($currentDirectory, 'admin', 'LaravueTest', 'Views', 'Pages', $name);
        } else if ($this->option('outdocker')) {
            $buildPath = $this->fileBuildPath('admin', 'src', 'components', $this->projectName, 'Views', 'Pages', $name);
            $frontDirectory = Str::replaceFirst(end($paths), $buildPath, $currentDirectory);
        } else {
            $buildPath = $this->fileBuildPath('src', 'components', $this->projectName, 'Views', 'Pages', $name);
            $frontDirectory = Str::replaceFirst(end($paths), $buildPath, $currentDirectory);
        }

        if (!is_dir($frontDirectory)) {
            mkdir($frontDirectory, 0777, true);
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
        $parsedFile = str_replace('\\', '/', $file);

        if (strpos($parsedFile, "/") !== false) {
            $folders = explode("/", $parsedFile);
            $parsedFile = array_pop($folders);

            $folders = '/' . implode("/", $folders);
        }

        $currentDirectory =  getcwd();
        $paths = explode("/", str_replace('\\', '/', $currentDirectory));

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
        $currentDirectory =  getcwd();
        $paths = explode("/", str_replace('\\', '/', $currentDirectory));

        if (end($paths) == "laravue") { // Laravue Tests
            $frontDirectory = $this->fileBuildPath($currentDirectory, 'admin', 'LaravueTest', 'Views', 'Pages', $name, 'forms');
        } else if ($this->option('outdocker')) {
            $buildPath = $this->fileBuildPath('admin', 'src', 'components', $this->projectName, 'Views', 'Pages', $name, 'forms');
            $frontDirectory = Str::replaceFirst(end($paths), $buildPath, $currentDirectory);
        } else {
            $buildPath = $this->fileBuildPath('src', 'components', $this->projectName, 'Views', 'Pages', $name, 'forms');
            $frontDirectory = Str::replaceFirst(end($paths), $buildPath, $currentDirectory);
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
    protected function setStub($path)
    {
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
        if ($lang === 'pt-BR') {
            $ending_letters = substr($singular, -2);
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

        $last_letter = strtolower($singular[strlen($singular) - 1]);
        switch ($last_letter) {
            case 'm':
                return substr($singular, 0, -1) . 'ns';
            case 'y':
                return substr($singular, 0, -1) . 'ies';
            case 's':
            case 'r':
                return $singular . 'es';
            default:
                return $singular . 's';
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
            if ((strpos($option, 'u') !== false) && (strpos($option, '*') === false)) {
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
    protected function isUniqueArray($field)
    {
        // default may contain letter u*
        $field = $this->dropDefault($field);

        $options = $this->getOptionsArray($field);
        $uniqueArray = false;
        foreach ($options as $option) {
            if (strpos($option, 'u*') !== false) {
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
     * Retorna falso se não contém número ou retorna os números de precisão
     *
     * @param  string  $field
     * @return boolean false or
     * @return integer number
     */
    protected function getPrecisionNumbers($field)
    {
        $options = $this->getOptionsArray($field);
        $numbers = false;
        if (isset($options[1])) {
            $numbers = explode('-', $options[1]);
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
            case 'mv':
                return 'monetario';
            default:
                return 'string';
        }
    }

    /**
     * Cria o título a partir do nome do campo
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
    protected function makePath($file, $outsideApp = false)
    {
        $folders = "";
        if (strpos($file, "/") !== false) {
            $folders = explode("/", $file);
            $file = array_pop($folders);

            $folders = "/" . implode("/", $folders);
        }

        $currentDirectory =  getcwd();
        $backPath = $outsideApp ? $currentDirectory . $folders : "$currentDirectory/app$folders";

        if (!is_dir($backPath)) {
            mkdir($backPath, 0777, true);
        }

        return "$backPath/$file";
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
}
