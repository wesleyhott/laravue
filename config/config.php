<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Server URI index
    |--------------------------------------------------------------------------
    |
    | Server URI index determina em qual posição está o nome da rota que é o 
    | plural do modelo. Esse nome é usado para determinar as permissões do 
    | usuário no sistema.
    | Ex: http://localhost/laravue/api/api/users Para o modelo User, o 
    | SERVER_URI_INDEX é 3: 0 => "laravue"; 1 => "api"; 2 => "api"; 3 => "users"
    */

    'uri_index' => env('SERVER_URI_INDEX', 3),

    /*
    |--------------------------------------------------------------------------
    | Accentuation
    |--------------------------------------------------------------------------
    | Por não existir regras sem exceções no Português para acentuação gráfica,
    | precisamos fazer manualmente. De tal forma que é necessário ensinar ao  
    | Laravue as acentuações para que ele gere as acentuações corretamente. 
    | Para tanto, basta inserir a palavra sem aentuação e logo após a palavra
    | acentuada. Precisamos fazer tanto para o singula quanto para o plural.
    | Outra alternativa é usar o comando:
    |     php artisan laravue:learn PalavraSemAcento PalavraAcentuada -a
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

    /*
    |--------------------------------------------------------------------------
    | Plural
    |--------------------------------------------------------------------------
    | Por não existir regras sem exceções no Português para pluralização,
    | precisamos fazer manualmente. De tal forma que é necessário ensinar ao  
    | Laravue as exeções para que ele gere as palavras corretamente. 
    | Para tanto, basta inserir a palavra no singular *** sem aentuação *** e
    | logo após a palavra no plural, também sem acentuação.
    | Outra alternativa é usar o comando:
    |     php artisan laravue:learn PalavraSingularSemAcento PalavraPluralSemAcento -p
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

    /*
    |--------------------------------------------------------------------------
    | Select Label
    |--------------------------------------------------------------------------
    | Quando de um relacionamento 1xN ou MxN, é gerado um select no formulário.
    | O Laravue procura possíveis campos que podem servir de label para melhor 
    | representar o modelo. Ele procura na listagem abaixo e retorna a primeira
    | ocorrência encontrada. Pode-se trocar a ordem conforme necessidade.
    | Outra alternativa é usar o comando:
    |     php artisan laravue:learn Label -s
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