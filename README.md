# Laravue
Code generation for MPMG projects that use Laravel and VueJs tecnologies.

## Instalation
```
composer require mpmg/laravue
```

```
php artisan laravue:install
```

```
php artisan vendor:publish --provider="Mpmg\Laravue\LaravueServiceProvider"
```

```
php artisan migrate
```

```
php artisan passport:install
```

## Documentation

### Commands
#### Build
```
php artisan laravue:build <model>
```
Generates code for backend (Laravel) and frontend (VueJs) for model \<model\>

#### Build with options
```
php artisan laravue:build <model> <-f|--fields>
```
#### Comand Description
Generates code for backend (Laravel) and frontend (VueJs) for model \<model\> with fields <-f|--fields>

### Examples
Supose we have the situation that users have posts and models are
**Users**
| Field | Type    | Detais                |
| :---: | ------- | --------------------- |
|  id   | integer | not null; primary key |
| name  | string  | not null              |
|  age  | integer | nullable              |

**Posts**
|   Field    | Type     | Detais                          |
| :--------: | -------- | ------------------------------- |
|     id     | integer  | not null; primary key           |
|  user_id   | int      | not null; foreing key for users |
|    text    | string   | not null                        |
| created_at | datetime | not null                        |
|  approved  | boolean  | not null                        |

**Command for generate User model**
```
php artisan laravue:build User -f name:s,age:i.n
```

**Command for generate Post model**
```
php artisan laravue:build Post -f user_id:i,text:s,age:i.n,created_at:dt,approved:b
```
### Fields types and shortcus

| Shortcut  | Generate                                      | Description                                                                        |
| :-------: | --------------------------------------------- | ---------------------------------------------------------------------------------- |
|    bpk    | $table->bigIncrements('id');                  | Incrementing ID using a "big integer" equivalent.                                  |
|    bi     | $table->bigInteger('votes');                  | BIGINT equivalent to the table                                                     |
|    by     | $table->binary('data');                       | BLOB equivalent to the table                                                       |
|     b     | $table->boolean('confirmed');                 | BOOLEAN equivalent to the table                                                    |
|    c.4    | $table->char('name', 4);                      | CHAR equivalent with a length                                                      |
|     d     | $table->date('created_at');                   | DATE equivalent to the table                                                       |
|    dt     | $table->dateTime('created_at');               | DATETIME equivalent to the table                                                   |
|  de.5;2   | $table->decimal('amount', 5, 2);              | DECIMAL equivalent with a precision and scale                                      |
|  db.15;8  | $table->double('column', 15, 8);              | DOUBLE equivalent with precision, 15 digits in total and 8 after the decimal point |
| e.foo;bar | $table->enum('choices', array('foo', 'bar')); | ENUM equivalent to the table                                                       |
|     f     | $table->float('amount');                      | FLOAT equivalent to the table                                                      |
|    pk     | $table->increments('id');                     | Incrementing ID to the table (primary key).                                        |
|     i     | $table->integer('votes');                     | INTEGER equivalent to the table                                                    |
|    lt     | $table->longText('description');              | LONGTEXT equivalent to the table                                                   |
|    mi     | $table->mediumInteger('numbers');             | MEDIUMINT equivalent to the table                                                  |
|    mt     | $table->mediumText('description');            | MEDIUMTEXT equivalent to the table                                                 |
|     m     | $table->morphs('taggable');                   | Adds INTEGER taggable_id and STRING taggable_type                                  |
|    si     | $table->smallInteger('votes');                | SMALLINT equivalent to the table                                                   |
|    ti     | $table->tinyInteger('numbers');               | TINYINT equivalent to the table                                                    |
|    sd     | $table->softDeletes();                        | Adds deleted_at column for soft deletes                                            |
|     s     | $table->string('email');                      | VARCHAR equivalent column                                                          |
|   s.100   | $table->string('name', 100);                  | VARCHAR equivalent with a length                                                   |
|    tx     | $table->text('description');                  | TEXT equivalent to the table                                                       |
|     t     | $table->time('sunrise');                      | TIME equivalent to the table                                                       |
|    ts     | $table->timestamp('added_on');                | TIMESTAMP equivalent to the table                                                  |
|    tt     | $table->timestamps();                         | Adds created_at and updated_at columns                                             |
|    rt     | $table->rememberToken();                      | Adds remember_token as VARCHAR(100) NULL                                           |
|    .n     | ->nullable()                                  | Designate that the column allows NULL values                                       |
|   .#5#    | ->default(5)                                  | Declare a default value for a column                                               |
|    .+     | ->unsigned()                                  | Unsigned integer                                                                   |
|    .u     | ->unique()                                    | Unique field                                                                       |
|    .u*    | $table->unique['un1','unN'];                  | Fields that are unique together                                                    |

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.