<?php

namespace Mpmg\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Mpmg\Laravue\Tests\TestCase;

class MakeFrontIndexTestFileTest extends TestCase
{
    /** @test */
    function it_creates_a_front_index_test_file()
    {
        $model = 'TestFieldOption';
        // destination path of the FrontModel class
        $testClass = str_replace( "tests/Unit", "", __DIR__) . "Frontend/LaravueTest/Views/Pages/TestFieldOption/Index.vue";

        // Run the make command
        Artisan::call('laravue:frontindex', [
            'model' => $model,
            '--fields' => 'name:s.n40,age:i,data_inicio:d,data_fim:d.n,ativo:b,hora:t',
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));
    }
}