<?php

namespace wesleyhott\Laravue\Commands;

use Illuminate\Support\Str;

class LaravueFrontModelCommand extends LaravueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravue:frontmodel {model*} {--f|fields=} {--o|outdocker}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criação do frontend forms/Model.vue';

    /**
     * The model fields.
     *
     * @var string
     */
    protected $fields = [];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('fields') !== null) {
            $this->fields = $this->getFieldsArray($this->option('fields'));
        }

        $this->setStub('/front/forms/model');
        $argumentModel = $this->argument('model');
        $model = is_array($argumentModel) ? trim($argumentModel[0]) : trim($argumentModel);
        $date = now();

        $path = $this->getFrontFormsPath($model, "Model");
        $this->files->put($path, $this->buildModel($model));

        $this->info("$date - [ $model ] >> forms/Model.vue");
    }

    protected function replaceField($stub, $model = null, $schema = null)
    {
        $default = "<div class=\"row formSpace\">" . PHP_EOL;
        $default .= $this->tabs(5) .  "<div class=\"col-sm-12\">" . PHP_EOL;
        $default .= $this->tabs(6) .  "<!-- insert code here. -->" . PHP_EOL;
        $default .= $this->tabs(5) .  "</div>" . PHP_EOL;
        $default .= $this->tabs(4) .  "</div>";

        if (!$this->option('fields')) {
            $toComment = str_replace(
                [
                    '{{ fields:model }}',
                    '{{ fields:model2 }}',
                    '{{ fields:validation }}',
                    '{{ fields:computed }}',
                    '{{ fields:data-selects }}',
                    '{{ fields:data-submit }}',
                    '{{ fields:method }}',
                ],
                "// insert code here.",
                $stub
            );

            return str_replace('{{ fields }}', $default, $toComment);
        }

        $returnFields = "";
        $first = true;
        foreach ($this->fields as $key => $value) {
            $type = $this->getType($value);
            $rules = '';
            // Nullable
            $isNullable = $this->hasNullable($value);
            $rules = $isNullable ? '' : 'required';
            // String Size
            $size = '';
            if ($type == 'string' || $type == 'char') {
                $isNumbers = $this->hasNumber($value);
                if ($isNumbers !== false) {
                    $size = $isNumbers[0];
                    $rules .= $rules == '' ? "max:$size" : "|max:$size";
                }
            }
            // Unsigned Integer
            $unsigned = '';
            if ($type == 'integer') {
                $isUnsigned = $this->isUnsigned($value);
                if ($isUnsigned !== false) {
                    $rules .= $rules == '' ? "min_value:0" : "|min_value:0";
                }
            }

            if ($this->isFk($key)) {
                $returnFields .= $this->getSelect($key, $rules);
                continue;
            }

            switch ($type) {
                case 'boolean':
                    $returnFields .= $this->getCheckBox($key, $rules);
                    break;
                case 'date':
                case 'dateTime':
                    $returnFields .= $this->getDate($key, $rules);
                    break;
                case 'integer':
                    $rules .= $rules == '' ? "integer" : "|integer";
                case 'time':
                    $returnFields .= $this->getTime($key, $rules);
                    break;
                default:
                    $returnFields .= $this->getInput($key, $rules);
            }
        }

        $parsedDataSelect = $this->replaceDataSelect($stub, $model);
        $parsedModel = $this->replaceFieldModel($parsedDataSelect, $model);
        $parsedModel2 = $this->replaceFieldModel2($parsedModel, $model);
        $parsedComputed = $this->replaceFieldComputed($parsedModel2, $model);
        $parsedMethod = $this->replaceFieldMethod($parsedComputed, $model);
        $parsedSubmit = $this->replaceFieldSubmit($parsedMethod, $model);

        return str_replace('{{ fields }}', $returnFields, $parsedSubmit);
    }

    protected function getInput($key, $rules)
    {
        $field = Str::snake($key);
        $label = $this->getTitle($key);

        $input = "";
        $input .= "<div class=\"row formSpace\">" . PHP_EOL;
        $input .= $this->tabs(7) . "<div class=\"col-sm-12\">" . PHP_EOL;
        $input .= $this->tabs(8) . "<ValidationProvider name=\"$label\" rules=\"$rules\" v-slot=\"{ errors }\">" . PHP_EOL;
        $input .= $this->tabs(9) . "<fg-input " . PHP_EOL;
        $input .= $this->tabs(10) . ":placeholder=\"relatorio ? 'Não filtrar' :'Digite $label'\" " . PHP_EOL;
        $input .= $this->tabs(10) . "label=\"$label\"" . PHP_EOL;
        $input .= $this->tabs(10) . "v-model=\"model.$field\">" . PHP_EOL;
        $input .= $this->tabs(9) . "</fg-input>" . PHP_EOL;
        $input .= $this->tabs(9) . "<div v-if=\"!relatorio\" class=\"text-danger\" style=\"font-size: .8271em; margin-top: 4px;\">{{ errors[0] }}</div>" . PHP_EOL;
        $input .= $this->tabs(8) . "</ValidationProvider>" . PHP_EOL;
        $input .= $this->tabs(7) . "</div>" . PHP_EOL;
        $input .= $this->tabs(6) . "</div>" . PHP_EOL;
        $input .= $this->tabs(6) . "";

        return $input;
    }

    protected function getCheckbox($key, $rules)
    {
        $field = Str::snake($key);
        $label = $this->getTitle($key);

        $input = "";
        $input .= "<div class=\"row formSpace\">" . PHP_EOL;
        $input .= $this->tabs(7) . "<div v-if=\"relatorio\" class=\"col-sm-12\">" . PHP_EOL;
        $input .= $this->tabs(8) . "<ValidationProvider name=\"$label\" rules=\"$rules\" v-slot=\"{ errors }\">" . PHP_EOL;
        $input .= $this->tabs(9) . "<div style=\"margin-bottom: 5px; color: #9A9A9A; font-size: .8571em;\">$label</div>" . PHP_EOL;
        $input .= $this->tabs(9) . "<el-select  v-model=\"model.$field\">" . PHP_EOL;
        $input .= $this->tabs(10) . "<el-option label=\"Não filtrar\" value=\"\"></el-option>" . PHP_EOL;
        $input .= $this->tabs(10) . "<el-option label=\"Sim\" value=\"true\"></el-option>" . PHP_EOL;
        $input .= $this->tabs(10) . "<el-option label=\"Não\" value=\"false\"></el-option>" . PHP_EOL;
        $input .= $this->tabs(9) . "</el-select>" . PHP_EOL;
        $input .= $this->tabs(9) . "<div v-if=\"!relatorio\" class=\"text-danger\" style=\"font-size: .8271em; margin-top: 4px;\">{{ errors[0] }}</div>" . PHP_EOL;
        $input .= $this->tabs(8) . "</ValidationProvider>" . PHP_EOL;
        $input .= $this->tabs(7) . "</div>" . PHP_EOL;
        $input .= $this->tabs(8) . "<div v-else class=\"col-sm-12\">" . PHP_EOL;
        $input .= $this->tabs(9) . "<ValidationProvider name=\"$label\" rules=\"$rules\" v-slot=\"{ errors }\">" . PHP_EOL;
        $input .= $this->tabs(9) . "<div style=\"margin-bottom: 5px; color: #9A9A9A; font-size: .9971em;\">&nbsp;⠀⠀</div>" . PHP_EOL;
        $input .= $this->tabs(9) . "<p-checkbox v-model=\"$field\">$label</p-checkbox>" . PHP_EOL;
        $input .= $this->tabs(9) . "<div v-if=\"!relatorio\" class=\"text-danger\" style=\"font-size: .8271em; margin-top: 4px;\">{{ errors[0] }}</div>" . PHP_EOL;
        $input .= $this->tabs(8) . "</ValidationProvider>" . PHP_EOL;
        $input .= $this->tabs(7) . "</div>" . PHP_EOL;
        $input .= $this->tabs(6) . "</div>" . PHP_EOL;
        $input .= $this->tabs(6) . "";

        return $input;
    }

    protected function getDate($key, $rules)
    {
        $field = Str::snake($key);
        $label = $this->getTitle($key);

        $input = "";
        $input .= "<div class=\"row formSpace\">" . PHP_EOL;
        $input .= $this->tabs(7) . "<div class=\"col-sm-12\">" . PHP_EOL;
        $input .= $this->tabs(8) . "<ValidationProvider name=\"$label\" rules=\"$rules\" v-slot=\"{ errors }\">" . PHP_EOL;
        $input .= $this->tabs(9) . "<div v-if=\"!relatorio\" style=\"margin-bottom: 5px; color: #9A9A9A; font-size: .8571em;\">$label</div>" . PHP_EOL;
        $input .= $this->tabs(9) . "<div v-else style=\"margin-bottom: 5px; color: #9A9A9A; font-size: .8571em;\">Período da $label</div>" . PHP_EOL;
        $input .= $this->tabs(9) . "<el-date-picker" . PHP_EOL;
        $input .= $this->tabs(10) . "v-if=\"!relatorio\"" . PHP_EOL;
        $input .= $this->tabs(10) . "v-model=\"model.$field\"" . PHP_EOL;
        $input .= $this->tabs(10) . "style=\"width: 100%;\"" . PHP_EOL;
        $input .= $this->tabs(10) . "type=\"date\"" . PHP_EOL;
        $input .= $this->tabs(10) . "format=\"dd/MM/yyyy\"" . PHP_EOL;
        $input .= $this->tabs(10) . "value-format=\"yyyy-MM-dd\"" . PHP_EOL;
        $input .= $this->tabs(10) . ":placeholder=\"relatorio ? 'Não filtrar' : 'Selecione data'\">" . PHP_EOL;
        $input .= $this->tabs(9) . "</el-date-picker>" . PHP_EOL;
        $input .= $this->tabs(9) . "<el-date-picker" . PHP_EOL;
        $input .= $this->tabs(10) . "v-else" . PHP_EOL;
        $input .= $this->tabs(10) . "v-model=\"model.$field\"_date_range" . PHP_EOL;
        $input .= $this->tabs(10) . "style=\"width: 100%;\"" . PHP_EOL;
        $input .= $this->tabs(10) . "type=\"daterange\"" . PHP_EOL;
        $input .= $this->tabs(10) . "range-separator=\" à \"" . PHP_EOL;
        $input .= $this->tabs(10) . "format=\"dd/MM/yyyy\"" . PHP_EOL;
        $input .= $this->tabs(10) . "value-format=\"yyyy-MM-dd\"" . PHP_EOL;
        $input .= $this->tabs(10) . "start-placeholder=\"Data inicial\"" . PHP_EOL;
        $input .= $this->tabs(10) . "end-placeholder=\"Data final\">" . PHP_EOL;
        $input .= $this->tabs(9) . "</el-date-picker>" . PHP_EOL;
        $input .= $this->tabs(9) . "<div v-if=\"!relatorio\" class=\"text-danger\" style=\"font-size: .8271em; margin-top: 4px;\">{{ errors[0] }}</div>" . PHP_EOL;
        $input .= $this->tabs(8) . "</ValidationProvider>" . PHP_EOL;
        $input .= $this->tabs(7) . "</div>" . PHP_EOL;
        $input .= $this->tabs(6) . "</div>" . PHP_EOL;
        $input .= $this->tabs(6) . "";

        return $input;
    }

    public function getTime($key, $rules)
    {
        $field = Str::snake($key);
        $label = $this->getTitle($key);

        $time = "";
        $time .= "<div class=\"row formSpace\">  <!-- TODO: [build] Change picker options -->" . PHP_EOL;
        $time .= $this->tabs(7) . "<div class=\"col-sm-12\">" . PHP_EOL;
        $time .= $this->tabs(8) . "<ValidationProvider name=\"$label\" rules=\"$rules\" v-slot=\"{ errors }\">" . PHP_EOL;
        $time .= $this->tabs(9) . "<div style=\"margin-bottom: 5px; color: #9A9A9A; font-size: .8571em;\">$label</div>" . PHP_EOL;
        $time .= $this->tabs(9) . "<el-time-select" . PHP_EOL;
        $time .= $this->tabs(10) . "v-model=\"model.$field\"" . PHP_EOL;
        $time .= $this->tabs(10) . "style=\"width: 100%;\"" . PHP_EOL;
        $time .= $this->tabs(10) . ":picker-options=\"{" . PHP_EOL;
        $time .= $this->tabs(11) . "start: '08:00'," . PHP_EOL;
        $time .= $this->tabs(11) . "step: '02:00'," . PHP_EOL;
        $time .= $this->tabs(11) . "end: '18:00'," . PHP_EOL;
        $time .= $this->tabs(10) . "}\"" . PHP_EOL;
        $time .= $this->tabs(10) . "placeholder=\"Selecione hora\">" . PHP_EOL;
        $time .= $this->tabs(9) . "</el-time-select>" . PHP_EOL;
        $time .= $this->tabs(9) . "<div v-if=\"!relatorio\" class=\"text-danger\" style=\"font-size: .8271em; margin-top: 4px;\">{{ errors[0] }}</div>" . PHP_EOL;
        $time .= $this->tabs(8) . "</ValidationProvider>" . PHP_EOL;
        $time .= $this->tabs(7) . "</div>" . PHP_EOL;
        $time .= $this->tabs(6) . "</div>" . PHP_EOL;
        $time .= $this->tabs(6) . "";

        return $time;
    }

    protected function getSelect($key, $rules)
    {
        $keyFields = $this->getModelFieldsFromKey($key);
        $modellabel = $this->getSelectLabel($keyFields);
        $field = Str::snake($key);
        $pluralField = lcfirst(Str::studly($this->pluralize(str_replace("_id", "", $field))));
        $label = $this->getTitle($key);
        $label = str_replace(" Id", "", $label);

        $input = "";
        $input .= "<div class=\"row formSpace\"> " . PHP_EOL;
        $input .= $this->tabs(5) .  "<div class=\"col-sm-12\">" . PHP_EOL;
        $input .= $this->tabs(6) .  "<ValidationProvider name=\"$label\" rules=\"$rules\" v-slot=\"{ errors }\">" . PHP_EOL;
        $input .= $this->tabs(7) .  "<div style=\"margin-bottom: 5px; color: #9A9A9A; font-size: .8571em;\">$label</div>" . PHP_EOL;
        $input .= $this->tabs(7) .  "<el-select" . PHP_EOL;
        $input .= $this->tabs(8) .  "filterable" . PHP_EOL;
        $input .= $this->tabs(8) .  "class=\"baseSelect\"" . PHP_EOL;
        $input .= $this->tabs(8) .  "style=\"width: 100%;\"" . PHP_EOL;
        $input .= $this->tabs(8) .  "size=\"large\"" . PHP_EOL;
        $input .= $this->tabs(8) .  ":placeholder=\"relatorio ? 'Não filtrar' : '$label'\"" . PHP_EOL;
        $input .= $this->tabs(8) .  "v-model=\"model.$key\" >" . PHP_EOL;
        $input .= $this->tabs(9) .  "<el-option v-if=\"relatorio\" label=\"Não filtrar\" value=\"\"></el-option>" . PHP_EOL;
        $input .= $this->tabs(9) .  "<el-option" . PHP_EOL;
        $input .= $this->tabs(10) .  "v-for=\"item in selects.$pluralField\"" . PHP_EOL;
        $input .= $this->tabs(10) .  "class=\"select-danger\"" . PHP_EOL;
        $input .= $this->tabs(10) .  "style=\"width: 100%;\"" . PHP_EOL;
        $input .= $this->tabs(10) .  ":value=\"item.id\"" . PHP_EOL;
        $input .= $this->tabs(10) .  ":label=\"item.$modellabel\"" . PHP_EOL;
        $input .= $this->tabs(10) .  ":key=\"item.id\" >" . PHP_EOL;
        $input .= $this->tabs(9) .  "</el-option>" . PHP_EOL;
        $input .= $this->tabs(7) .  "</el-select>" . PHP_EOL;
        $input .= $this->tabs(7) .  "<div v-if=\"!relatorio\" class=\"text-danger\" style=\"font-size: .8271em; margin-top: 4px;\">{{ errors[0] }}</div>" . PHP_EOL;
        $input .= $this->tabs(6) .  "</ValidationProvider>" . PHP_EOL;
        $input .= $this->tabs(5) .  "</div>" . PHP_EOL;
        $input .= $this->tabs(4) .  "</div>" . PHP_EOL;
        $input .= $this->tabs(4) .  "";

        return $input;
    }

    protected function replaceDataSelect($stub, $model)
    {
        $fields = $this->getFieldsArray($this->option('fields'));
        $fks = array_filter($fields, function ($k) {
            return $this->isFk($k);
        }, ARRAY_FILTER_USE_KEY);

        $return = "";
        foreach ($fks as $key => $value) {
            $selecField = lcfirst(Str::studly($this->pluralize(str_replace("_id", "",  $key))));
            $return .= PHP_EOL;
            $return .= $this->tabs(4) . "$selecField: [],";
        }

        return str_replace('{{ fields:data-selects }}', $return, $stub);
    }

    protected function replaceFieldModel($stub, $model)
    {
        $return = "";
        $index = 0;
        $size = count($this->fields);
        foreach ($this->fields as $key => $value) {
            $index++;
            $type = $this->getType($value);
            switch ($type) {
                case 'boolean':
                    $default = $this->hasDefault($value);
                    $checked = ($default == 1) || ($default == '1') || ($default == true) ? 'true' : 'false';
                    $return .= "$key: this.relatorio ? '' : $checked,";
                    $return .= $this->ending($index, $size, 4);
                    break;
                case 'date':
                case 'datetime':
                    $return .= "$key: '',";
                    $return .= "${key}_date_range: '',";
                    $return .= $this->ending($index, $size, 4);
                    break;
                default:
                    $return .= "$key: '',";
                    $return .= $this->ending($index, $size, 4);
            }
        }

        return str_replace('{{ fields:model }}', $return, $stub);
    }

    protected function replaceFieldModel2($stub, $model)
    {
        $return = "";
        $index = 0;
        $size = count($this->fields);
        foreach ($this->fields as $key => $value) {
            $index++;
            $type = $this->getType($value);
            switch ($type) {
                case 'boolean':
                    $return .= "$key: this.relatorio ? '' : false,";
                    $return .= $this->ending($index, $size, 8);
                    break;
                default:
                    $return .= "$key: '',";
                    $return .= $this->ending($index, $size, 8);
            }
        }

        return str_replace('{{ fields:model2 }}', $return, $stub);
    }

    protected function ending($index, $size, $tabs)
    {
        return $index == $size ? "" : PHP_EOL . $this->tabs($tabs);
    }

    protected function replaceFieldComputed($stub, $model)
    {
        $return = PHP_EOL;
        foreach ($this->fields as $key => $value) {
            $type = $this->getType($value);
            switch ($type) {
                case 'boolean':
                    $return .= $this->tabs(2) . "$key: {" . PHP_EOL;
                    $return .= $this->tabs(3) . "get: function() {" . PHP_EOL;
                    $return .= $this->tabs(4) . "return this.model.$key >= 1 ? true : false;" . PHP_EOL;
                    $return .= $this->tabs(3) . "}," . PHP_EOL;
                    $return .= $this->tabs(3) . "set: function(newValue) {" . PHP_EOL;
                    $return .= $this->tabs(4) . "this.model.$key = newValue ? 1 : 0;" . PHP_EOL;
                    $return .= $this->tabs(3) . "}," . PHP_EOL;
                    $return .= $this->tabs(2) . "},";
                    break;
            }
        }

        return str_replace('{{ fields:computed }}', $return, $stub);
    }

    protected function replaceFieldMethod($stub, $model)
    {
        $fks = array_filter($this->fields, function ($k) {
            return $this->isFk($k);
        }, ARRAY_FILTER_USE_KEY);

        $return = $this->buildLoadSelects($fks);
        $return .= $this->buildSelects($fks);

        return str_replace('{{ fields:method }}', $return, $stub);
    }

    protected function buildLoadSelects($fks)
    {
        if (count($fks) == 0) {
            $methodName = "loadModel";
        } else {
            $methodName = "load" . Str::studly($this->pluralize(str_replace("_id", "", array_key_first($fks))));
        }

        $loadSelect = "loadSelects() {" . PHP_EOL;
        $loadSelect .= $this->tabs(3) . "this.setLoading(true, \"carregando\")" . PHP_EOL;
        $loadSelect .= $this->tabs(3) . "this.$methodName() // cascade calls" . PHP_EOL;
        $loadSelect .= $this->tabs(2) . "}," . PHP_EOL;

        return $loadSelect;
    }

    protected function buildSelects($fks)
    {
        $size = count($fks);
        if ($size == 0) {
            return "";
        }

        $selects = $this->tabs(2) . "{{ methodname }}() {" . PHP_EOL;
        $selects .= $this->tabs(3) . "this.setLoading(true, \"{{ title }}\")" . PHP_EOL;
        $selects .= $this->tabs(3) . "this.\$http" . PHP_EOL;
        $selects .= $this->tabs(4) . ".get(`{{ route }}?per_page=-1&select={{ modelField }}&not_null={{ modelField }}&order_by={{ modelField }}`)" . PHP_EOL;
        $selects .= $this->tabs(4) . ".then(response => {" . PHP_EOL;
        $selects .= $this->tabs(5) . "this.setLoading(false)" . PHP_EOL;
        $selects .= $this->tabs(5) . "this.selects.{{ selectField }} = response.data.data.data" . PHP_EOL;
        $selects .= $this->tabs(5) . "this.{{ nextMethod }}()" . PHP_EOL;
        $selects .= $this->tabs(4) . "})" . PHP_EOL;
        $selects .= $this->tabs(4) . ".catch(e => {" . PHP_EOL;
        $selects .= $this->tabs(5) . "this.setLoading(false)" . PHP_EOL;
        $selects .= $this->tabs(5) . "laravueNotify.failure(this, e)" . PHP_EOL;
        $selects .= $this->tabs(4) . "})" . PHP_EOL;
        $selects .= $this->tabs(2) . "}, {{ last }}";

        $return = "";
        $i = 0;
        foreach ($fks as $key => $value) {
            $keyFields = $this->getModelFieldsFromKey($key);
            $modelField = $this->getSelectLabel($keyFields);
            $methodName = "load" . Str::studly($this->pluralize(str_replace("_id", "", $key)));
            $title = $this->getTitle($key);
            $route = $this->pluralize(str_replace("_", "",  str_replace("_id", "",  $key)));
            $selectField = lcfirst(Str::studly($this->pluralize(str_replace("_id", "",  $key))));
            $nextMethod = $this->getNext($fks, $i++, $size);
            if ($nextMethod != 'loadModel') {
                $nextMethod = 'load' . Str::studly($this->pluralize(str_replace("_id", "", $nextMethod)));
            }

            $methodname = str_replace('{{ methodname }}', $methodName, $selects);
            $title = str_replace('{{ title }}', $title, $methodname);
            $route = str_replace('{{ route }}', $route, $title);
            $selectField = str_replace('{{ selectField }}', $selectField, $route);
            $modelField = str_replace('{{ modelField }}', $modelField, $selectField);
            $nextMethod = str_replace('{{ nextMethod }}', $nextMethod, $modelField);
            $last = $i == $size ? "" : PHP_EOL;
            $lastSelect = str_replace('{{ last }}', $last, $nextMethod);

            $return .= $lastSelect;
        }

        return $return;
    }

    protected function replaceFieldSubmit($stub, $model)
    {
        $return = "";
        foreach ($this->fields as $key => $value) {
            $selecField = lcfirst(Str::studly($this->pluralize(str_replace("_id", "",  $key))));
            $return .= PHP_EOL;
            switch ($value) {
                case 'i':
                case 'bi':
                    $return .= $this->tabs(6) . "$key: parseInt( this.model.$key ),";
                    break;
                case 'mv':
                    $return .= $this->tabs(6) . "$key: this.\$formatNumberToDatabase( this.model.$key ),";
                    break;
                default:
                    $return .= $this->tabs(6) . "$key: this.model.$key,";
            }
        }

        return str_replace('{{ fields:data-submit }}', $return, $stub);
    }

    function getNext($array, $i, $size)
    {
        $values = array_keys($array);
        if (($i + 1) == $size) {
            return 'loadModel';
        }
        return $values[$i + 1];
    }
}
