<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;
use Illuminate\Support\Str;

class MakeMigrationSchemaTest extends TestCase
{
    /** @test */
    function it_makes_a_migration_schema_test()
    {
        $prefix = date('Y_m_d_His');
        $model = array('ComplexName');
        $schema = 'Schema';
        $name = Str::snake($model[0]);
        // destination path of the Foo class
        $testClass = str_replace("tests/Unit", "", __DIR__) . "database/migrations/{$prefix}_create_{$schema}_{$name}s_table.php";

        // Run the make command
        Artisan::call('laravue:migration', [
            'model' => $model,
            '--schema' => $schema,
            '--fields' => "name:s.50u#'Fulano'#,nick_name,age:i.#40#,user_id,file_id:i,descricao:s.nu,modelo_id:i.uc,fabrica_id:i.nuc,idade:i.+",
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));
    }
}
