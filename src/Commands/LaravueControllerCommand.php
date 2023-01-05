<?php

namespace wesleyhott\Laravue\Commands;

use Illuminate\Support\Str;

class LaravueControllerCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:controller {model*} {--f|fields=} {--x|mxn} {--s|schema= : determine a schema for model (postgres)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria um novo controlador nos padrões do Laravue.';

    /**
     * Tipo de modelo que está sendo criado.
     *
     * @var string
     */
    protected $type = 'controller';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('mxn')) {
            $this->mxnProperty($this->argument('model')[0], $this->argument('model')[1]);
            $this->mxnProperty($this->argument('model')[1], $this->argument('model')[0]);
            $this->mxnModel($this->argument('model')[0], $this->argument('model')[1]);
            $this->mxnModel($this->argument('model')[1], $this->argument('model')[0]);
            $this->mxnAfterSave($this->argument('model')[0], $this->argument('model')[1]);
            $this->mxnAfterSave($this->argument('model')[1], $this->argument('model')[0]);
            return;
        }

        $this->setStub('/controller');
        $argumentModel = $this->argument('model');
        $model = is_array($argumentModel) ? trim($argumentModel[0]) : trim($argumentModel);
        $date = now();

        $path = $this->getPath(model: $model, schema: $this->option('schema'));
        $this->files->put($path, $this->buildController($model));

        $this->info("$date - [ $model ] >> $model" . "Controller.php");
    }

    protected function buildController($model, $fields = null)
    {
        $stub = $this->files->get($this->getStub());
        $uniqueMessages = $this->replaceUniqueMessages($stub, $model);
        $field = $this->replaceCollectOnly($uniqueMessages);
        $beforeIndex = $this->replaceBeforeIndex($field, $model);
        $schemaNamespace = $this->replaceSchemaNamespace($beforeIndex, $this->option('schema'));
        $schemaRoute = $this->replaceSchemaRoute($schemaNamespace, $this->option('schema'));
        $modelVar = $this->replaceModelVar($schemaRoute, $model);
        $title = $this->replaceTitle($modelVar, $model);

        return $this->replaceModel($title, $model);
    }

    protected function replaceUniqueMessages($stub, $model)
    {
        if (!$this->option('fields')) {
            return str_replace('{{ unique:messages }}', '', $stub);
        }
        $fields = $this->getFieldsArray($this->option('fields'));

        $messageFields = '';
        $uniqueArray = [];
        $first = true;
        foreach ($fields as $key => $value) {
            $isUniqueArray = $this->isUniqueArray($value);
            if ($first && $isUniqueArray) {
                $first = false;
                $messageFields .= "'$key.unique' => 'Já existe cadastro de $model com ";
                array_push($uniqueArray, $key);
                continue;
            }
            if ($isUniqueArray) {
                array_push($uniqueArray, $key);
            }
        }
        $uniqueArrayTitle = [];
        foreach ($uniqueArray as $unique) {
            array_push($uniqueArrayTitle, $this->getTitle($unique));
        }

        $messageFields .= implode(", ", $uniqueArrayTitle) . " fornecidos.'" . PHP_EOL;
        $message = '';
        if (count($uniqueArray) > 0) {
            $message .= PHP_EOL . $this->tabs(3);
            $message .= "'messages' => [" . PHP_EOL;
            $message .= $this->tabs(4) . $messageFields;
            $message .= $this->tabs(3) . "]";
        }
        return str_replace('{{ unique:messages }}', $message, $stub);
    }

    protected function replaceField($stub, $model = null, $schema = null)
    {
        if (!$this->option('fields')) {
            $fieldsParsed = str_replace('{{ fields }}', "//$" . "model->field = $" . "request->input('field');", $stub);
            return str_replace('{{ rules }}', "// Insira regras aqui", $fieldsParsed);
        }

        $fields = $this->getFieldsArray($this->option('fields'));

        //fields
        $returnFields = "";
        $first = true;
        foreach ($fields as $key => $value) {
            if ($first) {
                $first = false;
            } else {
                $returnFields .= PHP_EOL;
                $returnFields .= $this->tabs(2);
            }
            switch ($this->getType($value)) {
                case 'cpf':
                case 'cnpj':
                case 'cpfcnpj':
                    $returnFields .= "\$model->$key = \$this->unmask( \$request->input('$key') );";
                    break;
                default:
                    $returnFields .= "\$model->$key = \$request->input('$key');";
            }
        }
        $returnFields .= $this->tabs(2);
        $parsedfFields = str_replace('{{ fields }}', $returnFields, $stub);

        //rules
        $returnRules = "";
        $first = true;
        $firstUniqueArray = true;
        foreach ($fields as $key => $value) {
            $type = $this->getType($value);
            // Nullable
            $nullable = $this->hasNullable($value);
            $required = $nullable ? '' : '|required';
            // String Size
            $maxSize = '';
            if ($type == 'string') {
                $isNumbers = $this->hasNumber($value);
                if ($isNumbers !== false) {
                    $maxSize = "|max:" . $isNumbers[0];
                }
            }
            // Unsigned Integer
            $unsigned = '';
            if ($type == 'integer') {
                $isUnsigned = $this->isUnsigned($value);
                if ($isUnsigned !== false) {
                    $unsigned = "|min:0";
                }
            }
            // Monetary Value
            if ($type == 'monetario' || $type == 'monetary') {
                $type = 'decimal:2';
            }
            // Small integer
            if ($type == 'smallInteger') {
                $type = 'integer';
            }
            // Char
            if ($type == 'char') {
                $type = 'string';
            }
            // Datetime
            if ($type == 'dateTime') {
                $type = 'date';
            }
            // Decimal
            if ($type == 'decimal') {
                $type = 'digits_between:0,2';
            }
            // Binary
            if ($type == 'binary') {
                $type = 'string';
            }
            // Unique 
            $isUnique = $this->isUnique($value);
            $table = $this->pluralize(Str::snake($model));
            $unique = $isUnique ? "|unique:$table,$key,' . \$data['id']" : '';
            // Unique array 
            $uniqueArray = '';
            $isUniqueArray = $this->isUniqueArray($value);
            $skipEndingApostrophe = false;
            if ($firstUniqueArray && $isUniqueArray) {
                $fieldsUnique = $this->getFieldsArray($this->option('fields'));
                foreach ($fieldsUnique as $k => $v) {
                    $isUniqueInternalArray = $this->isUniqueArray($v);
                    if ($firstUniqueArray && $isUniqueInternalArray) {
                        $firstUniqueArray = false;
                        $uniqueArray .= "|unique:$table,$k,'" . PHP_EOL;
                        $uniqueArray .= $this->tabs(5) . ". \$data['id'] . ',id,'";
                        continue;
                    }
                    if ($isUniqueInternalArray) {
                        $uniqueArray .= PHP_EOL . $this->tabs(5) . ". '$k,' . \$data['$k'],";
                        $skipEndingApostrophe = true;
                    }
                }
            }

            if ($first) {
                $first = false;
            } else {
                $returnRules .= PHP_EOL;
                $returnRules .= $this->tabs(4);
            }

            // ending line rules
            $ending = $isUnique ? ',' : "',";
            if ($isUniqueArray  && !$skipEndingApostrophe) {
                $ending = "',";
            }
            if ($isUniqueArray  && $skipEndingApostrophe) {
                $ending = "";
            }

            if ($type == 'cpf') { // CPF
                $isRequired = $required != '' ? " 'required'," : '';
                $isUnique = $unique != '' ? " 'unique'," : '';
                $returnRules .= "'$key' => [ 'string', 'max:11',{$isRequired}{$isUnique} new \App\Rules\IsCpf() ],";
            } else if ($type == 'cnpj') { // CNPJ
                $isRequired = $required != '' ? " 'required'," : '';
                $returnRules .= "'$key' => [ 'string', 'max:14',{$isRequired}{$isUnique} new \App\Rules\IsCnpj() ],";
            } else if ($type == 'cpfcnpj') { // CPF ou CNPJ
                $isRequired = $required != '' ? " 'required'," : '';
                $returnRules .= "'$key' => [ 'string', 'max:14',{$isRequired}{$isUnique} new \App\Rules\IsCpfOrCnpj() ],";
            } else {
                $returnRules .= "'$key' => '{$type}{$required}{$maxSize}{$unique}{$uniqueArray}{$unsigned}";
                $returnRules .= "{$ending}";
            }
        }

        return str_replace('{{ rules }}', $returnRules, $parsedfFields);
    }

    protected function replaceCollectOnly($stub)
    {
        if (!$this->option('fields')) {
            return str_replace('{{ fields }}', "", $stub);
        }

        $fields = $this->getFieldsArray($this->option('fields'));

        //fields
        $returnFields = "";
        $lastKey = array_key_last($fields);
        foreach ($fields as $key => $value) {
            if (strcmp($lastKey, $key) == 0) {
                $returnFields .= "'$key'";
                continue;
            }

            $returnFields .= "'$key',";
        }

        return str_replace('{{ fields }}', $returnFields, $stub);
    }

    protected function replaceBeforeIndex($stub, $model)
    {
        if (!$this->option('fields')) {
            return str_replace('{{ beforeIndex }}', "// public function beforeIndex(\$data) { return \$data; }", $stub);
        }

        $booleanArray = array();
        $dateArray = array();
        $moneyArray = array();
        $cpfArray = array();
        $cpfCnpjArray = array();
        $cnpjArray = array();

        $fields = $this->getFieldsArray($this->option('fields'));
        foreach ($fields as $key => $value) {
            $type = $this->getType($value);
            if ($type === 'boolean') {
                array_push($booleanArray, $key);
            }
            if ($type === 'date') {
                array_push($dateArray, $key);
            }
            if ($type === 'monetario' || $type === 'monetary') {
                array_push($moneyArray, $key);
            }
            if ($type === 'cpf') {
                array_push($cpfArray, $key);
            }
            if ($type === 'cpfcnpj') {
                array_push($cpfCnpjArray, $key);
            }
            if ($type === 'cnpj') {
                array_push($cnpjArray, $key);
            }
        }

        if (count($booleanArray) == 0) {
            return str_replace('{{ beforeIndex }}', "// public function beforeIndex(\$data) { return \$data; }", $stub);
        }

        $beforeIndex = "public function beforeIndex(\$data) { " . PHP_EOL;
        $beforeIndex .= $this->tabs(2) . "foreach(\$data as \$item){" . PHP_EOL;
        foreach ($booleanArray as $field) {
            $beforeIndex .= $this->tabs(3) . "\$item->$field = \$item->$field == 1 ? \"Sim\" : \"Não\";" . PHP_EOL;
        }
        foreach ($dateArray as $field) {
            $beforeIndex .= $this->tabs(3) . "\$item->$field = date( 'd/m/Y', strtotime( \$item->$field  ) );" . PHP_EOL;
        }
        foreach ($moneyArray as $field) {
            $beforeIndex .= $this->tabs(3) . "\$item->$field = number_format(\$item->$field , 2, ',', '.');" . PHP_EOL;
        }
        foreach ($cpfArray as $field) {
            $beforeIndex .= $this->tabs(3) . "\$item->$field = \$this->mask(\$item->$field, '###.###.###-##');" . PHP_EOL;
        }
        foreach ($cpfCnpjArray as $field) {
            $item = $this->tabs(3) . "\${$field}Maskared = strlen( \$item->$field ) == 11" . PHP_EOL;
            $item .= $this->tabs(4) . "? \$this->mask(\$item->$field, '###.###.###-##')" . PHP_EOL;
            $item .= $this->tabs(4) . ": \$this->mask(\$item->$field, '##.###.###/####-##');" . PHP_EOL;
            $beforeIndex .= $item;
        }
        foreach ($cnpjArray as $field) {
            $beforeIndex .= $this->tabs(3) . "\$item->$field = \$this->mask(\$item->$field, '##.###.###/####-##');" . PHP_EOL;
        }
        $beforeIndex .= $this->tabs(2) . "}" . PHP_EOL;
        $beforeIndex .= $this->tabs(2) . "return \$data; " . PHP_EOL;
        $beforeIndex .= $this->tabs(1) . "}" . PHP_EOL;

        return str_replace('{{ beforeIndex }}', $beforeIndex, $stub);
    }

    protected function mxnProperty($modelM, $modelN)
    {
        $currentDirectory =  getcwd();
        $path = "$currentDirectory/app/Http/Controllers/{$modelM}Controller.php";
        $controllerFile = "";
        try {
            $controllerFile = $this->files->get($path);
        } catch (\Exception $e) {
            $this->error("Arquivo - $currentDirectory/app/Http/Controllers/{$modelM}Controller.php - não encontrado.");
        }

        $pluraLower = $this->pluralize(lcfirst($modelN));

        $property = "protected \${$pluraLower}_ids = null;" . PHP_EOL;
        $property .= $this->tabs(1) . "// {{ laravue-insert:property }}";

        $parsedProperty = str_replace('// {{ laravue-insert:property }}', $property, $controllerFile);;

        $this->files->put($path, $parsedProperty);
    }

    protected function mxnModel($modelM, $modelN)
    {
        $currentDirectory =  getcwd();
        $path = "$currentDirectory/app/Http/Controllers/{$modelM}Controller.php";
        $controllerFile = "";
        try {
            $controllerFile = $this->files->get($path);
        } catch (\Exception $e) {
            $this->error("Arquivo - $currentDirectory/app/Http/Controllers/{$modelM}Controller.php - não encontrado.");
        }

        $singularLower = lcfirst($modelN);

        $model = "\$this->{$singularLower}_ids = \$request->input('{$singularLower}_ids');" . PHP_EOL;
        $model .= $this->tabs(2) . "// {{ laravue-insert:setModel }}";

        $parsedModel = str_replace('// {{ laravue-insert:setModel }}', $model, $controllerFile);;

        $this->files->put($path, $parsedModel);
    }

    protected function mxnAfterSave($modelM, $modelN)
    {
        $currentDirectory =  getcwd();
        $path = "$currentDirectory/app/Http/Controllers/{$modelM}Controller.php";
        $controllerFile = "";
        try {
            $controllerFile = $this->files->get($path);
        } catch (\Exception $e) {
            $this->error("Arquivo - $currentDirectory/app/Http/Controllers/{$modelM}Controller.php - não encontrado.");
        }

        $singularLower = lcfirst($modelN);
        $pluraLower = $this->pluralize(lcfirst($modelN));

        $afterSaveMethod = "public function afterSave(\$model) {" . PHP_EOL;

        $afterSaveIf = $this->tabs(2) . "if( isset( \$this->{$singularLower}_ids) ) {" . PHP_EOL;
        $afterSaveIf .= $this->tabs(3) . "\$model->$pluraLower()->sync(\$this->{$singularLower}_ids);" . PHP_EOL;
        $afterSaveIf .= $this->tabs(2) . "}" . PHP_EOL;

        $afterSaveReturn = $this->tabs(2) . "return \$model; " . PHP_EOL;
        $afterSaveReturn .= $this->tabs(1) . "}" . PHP_EOL;

        $afterSaveInsert = PHP_EOL;
        $afterSaveInsert .= $this->tabs(1) . "// {{ laravue-insert:method }}";

        $afterSave = "";
        $parsedAfterSave = "";

        if (strpos($controllerFile, '// public function afterSave($model) { return $model; }') !== false) {
            $afterSave = $afterSaveMethod . $afterSaveIf . $afterSaveReturn;
            $parsedAfterSave = str_replace('// public function afterSave($model) { return $model; }', $afterSave, $controllerFile);
        } else if (strpos($controllerFile, 'public function afterSave($model) {') !== false) {
            $afterSave = $afterSaveMethod . $afterSaveIf;
            $parsedAfterSave = str_replace('public function afterSave($model) {', $afterSave, $controllerFile);
        } else {
            $afterSave = $afterSaveMethod . $afterSaveIf . $afterSaveReturn . $afterSaveInsert;
            $parsedAfterSave = str_replace('// {{ laravue-insert:method }}', $afterSave, $controllerFile);
        }

        $this->files->put($path, $parsedAfterSave);
    }
}
