<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeMigrationTest extends TestCase
{
    /** @test */
    function it_makes_a_migration_test()
    {
        $prefix = date('Y_m_d_His');
        $model = array('TestFieldOption');
        // destination path of the Foo class
        $testClass = str_replace("tests/Unit", "", __DIR__) . "database/migrations/{$prefix}_create_test_field_options_table.php";

        // Run the make command
        Artisan::call('laravue:migration', [
            'model' => $model,
            '--fields' => "name:s.50u#'Fulano'#,age:i.#40#,user_id:i.n,file_id:i,descricao:s.nu,modelo_id:i.uc,fabrica_id:i.nuc,idade:i.+",
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));
    }
}
// /Users/wesley/desenvolvimento/laravue/laravue/database/migrations/2022_08_30_205857_create__big_file_user_table.php