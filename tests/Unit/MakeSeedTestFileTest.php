<?php

namespace Mpmg\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Mpmg\Laravue\Tests\TestCase;

class MakeSeedTestFileTest extends TestCase
{
    /** @test */
    function it_creates_a_seed_test_file()
    {
        $model = array('TestFieldOption');
        // destination path of the Foo class
        $testClass = str_replace( "tests/Unit", "", __DIR__) . "database/seeders/". $model[0] . "Seeder.php";

        // Run the make command
        Artisan::call('laravue:seed', [
            'model' => $model,
            '--fields' => "name:s.50u#'Fulano'#,age:i.#40#,user_id:i.n,file_id:i,descricao:s.nu,modelo_id:i.u*,fabrica_id:i.nu*,idade:i.+",
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));
    }
}