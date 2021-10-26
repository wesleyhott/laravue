<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeControllerTestFileTest extends TestCase
{
    /** @test */
    function it_creates_a_controller_test_file()
    {
        $model = array('TestFieldOption');
        // destination path of the Foo class
        $testClass = str_replace( "tests/Unit", "", __DIR__) . "app/Http/Controllers/TestFieldOptionController.php";

        // Run the make command
        Artisan::call('laravue:controller', [
            'model' => $model,
            '--fields' => 'name:s.n40,age:i.+,descricao:s.u,modelo_id:i.u*,fabrica_id:i.u*,ativo:b,data_inicio:d',
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));
    }
}