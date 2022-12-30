<?php

return [
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
