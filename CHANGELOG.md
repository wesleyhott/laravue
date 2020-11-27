# Roteiro de Mudanças

Todas as mudanças importantes  em `Laravue` são documentadas neste arquivo.

## 8.5.97 - 27/nov/2020
### Adicionado
- Geração de código com suporte a VeeValidate 3.x.
- Transformação para CamelCase no modelo passado para o comando build.
- Suporte Unique (.u) nas migrations e controllers.
- Suporte Unique Array (.u*) nas migrations e controllers.
- Filtro de Modelo genérico para geração de relatório.
- Geração da pasta de relatórios.
### Alterado
- Atualização do VeeValidate para a versão 3.x.
- Ajuste na geração de filtros.
- Suporte ao FieldLike em index para campos booleanos.
- Correções menores.

## 8.4.83 - 16/nov/2020
### Adicionado
- Geração de relacionamentos 1*n nos modelos.
- Plural de palavras terminadas em 'al', 'il', 'ol', 'm'.
- Plural e acentuação nos títulos dos relatórios.

## 8.4.80 - 13/nov/2020
### Adicionado
- Suporte a geração no frontend de campos do tipo 'time'.
- Suporte a inserção de relacionamentos nos modelos.

### Alterado
- Geração de campo input como padrão no caso de não haver previsão no tipo selecionado.
- Correções menores.

## 8.3.79 - 12/nov/2020
### Adicionado
- Suporte Nullable (.n) nas migrations e controllers.
- Suporte a tamanho de string (s.#) nas migrations e Controllers.
- Adicionado Nullable nas chaves estrangeiras nas migrations.
### Alterado
- Correções diversas.

## 8.0.0 - 27/out/2020
### Adicionado
- Tudo, versão inicial.
