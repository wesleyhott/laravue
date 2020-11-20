<?php

namespace Mpmg\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Mpmg\Laravue\Tests\TestCase;

class MakeFrontModelTestFileTest extends TestCase
{
    /** @test */
    function it_creates_a_front_model_test_file()
    {
        $model = 'TestFieldOption';
        // destination path of the Foo class
        $testClass = str_replace( "tests/Unit", "", __DIR__) . "Frontend/LaravueTest/Views/Pages/TestFieldOption/forms/Model.vue";

        // Run the make command
        Artisan::call('laravue:frontmodel', [
            'model' => $model,
            '--fields' => 'name:s.n40,age:i,data_inicio:d,data_fim:d.n,ativo:b,hora:t',
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));
    }
}