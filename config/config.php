<?php

return [
    /**
     * Language
     * 
     * Determines the language for generation rules.
     * Supported languages: en, pt-BR
     */
    'language' => 'en',

    /**
     * Use Soft Deletes
     * 
     * Determines if models will be generated with soft deltes
     */
    'use_soft_deletes' => false,

    /**
     * Form Request Connection
     * 
     * When generating unique rules for table in a specific schema (Like Postgres schemas),
     * Laravel demands to wirte explicit connection for this schema table.
     * Example: 'required|unique:connection_name.schema_name.table_name,column,NULL,id',
     */
    'form_request_connection' => '',

    /**
     * Accentuation
     * 
     * As there are no rules without exceptions in Portuguese for graphic 
     * accentuation, we need to do it manually. In such a way that it is 
     * necessary to teach Laravue the graphic accents so that it generates the 
     * accents correctly. To do so, just insert the word without accentuation and 
     * right after the accented word. We need to do this for both the singular and 
     * the plural.
     * Another alternative is to use the command:
     * 
     * php artisan laravue:learn AccentlessWord AccentedWord -a
     * 
     */
    'accentuation' => [
        'Acordao' => 'Acórdao',
        'Acordaos' => 'Acórdaos',
        'Analise' => 'Análise',
        'Analises' => 'Análises',
        'Ausencia' => 'Ausência',
        'Ausencias' => 'Ausências',
        'Codigo' => 'Código',
        'Codigos' => 'Códigos',
        'Funcionario' => 'Funcionário',
        'Funcionarios' => 'Funcionários',
        'Horaria' => 'Horária',
        'Horarias' => 'Horárias',
        'Inicio' => 'Início',
        'Inicios' => 'Inícios',
        'Matricula' => 'Matrícula',
        'Matriculas' => 'Matrículas',
        'Mes' => 'Mês',
        'Numero' => 'Número',
        'Numeros' => 'Números',
        'Obrigatoria' => 'Obrigatória',
        'Obrigatorias' => 'Obrigatórias',
        'Ocorrencia' => 'Ocorrência',
        'Ocorrencias' => 'Ocorrências',
        'Repositorio' => 'Repositório',
        'Repositorios' => 'Repositórios',
        'Responsavel' => 'Responsável',
        'Responsaveis' => 'Responsáveis',
        'Tacita' => 'Tácita',
        'Tacitas' => 'Tácitas',
        'Usuario' => 'Usuário',
        'Usuarios' => 'Usuários',
        // {{ laravue-insert:accentuation }}
    ],

    /**
     * Plural
     * 
     * Because there are no rules without exceptions in Portuguese for pluralization,
     * we need to do it manually. In such a way that it is necessary to teach the
     * Laravue how to fix the exceptions, so it generates the words correctly.
     * To do so, just insert the word in the singular - without accentuation - and
     * right after the word in the plural, also without accentuation.
     * Another alternative is to use the command:
     * 
     * php artisan laravue:learn SingularNotAccentedWord PluralNotAccentedWord -p
     */
    'plural' => [
        'Acordao' => 'Acordaos',
        'Cidadao' => 'Cidadaos',
        'Orgao' => 'Orgaos',
        'Vao' => 'Vaos',
        'Cao' => 'Caes',
        'Mal' => 'Males',
        'Missil' => 'Misseis',
        'Reptil' => 'Repteis',
        'User' => 'Users',
        'Roof' => 'Roofs',
        'Belief' => 'Beliefs',
        'Chef' => 'Chefs',
        'Chief' => 'Chiefs',
        'Photo' => 'Photos',
        'Child' => 'Children',
        'Man' => 'Men',
        'Woman' => 'women',
        'Goose' => 'Geese',
        'Person' => 'People',
        'Tooth' => 'Teeth',
        'Tooth' => 'Teeth',
        'Foot' => 'Feet',
        'Mouse' => 'Mice',
        // {{ laravue-insert:plural }}
    ],

    /**
     * Laravue select Label
     * 
     * When a 1xN or MxN relationship is created, a select component (laravue-select) 
     * is generated in the form.
     * Laravue looks for possible fields that can serve as a label to better 
     * represent the model. It searches the listing below and returns the first
     * occurrence found. You can change the order as needed.
     * Another alternative is to use the command:
     * 
     * php artisan laravue:learn Label -s
     */
    'select_label' => [
        'label',
        'name',
        'nome',
        'title',
        'titulo',
        'description',
        'descricao',
        'desc',
        'text',
        'texto',
        'sigla',
        'uf',
        'code',
        'codigo',
        // {{ laravue-insert:selectlabel }}
    ],
];
