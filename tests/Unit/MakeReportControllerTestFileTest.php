<?php

namespace Mpmg\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Mpmg\Laravue\Tests\TestCase;

class MakeReportControllerTestFileTest extends TestCase
{
    /** @test */
    function it_creates_a_controller_test_file()
    {
        $model = 'TestFieldOption';
        // destination path of the Foo class
        $testClass = str_replace( "tests/Unit", "", __DIR__) . "app/Http/Controllers/Reports/TestFieldOptionReportController.php";

        // Run the make command
        Artisan::call('laravue:report', [
            'model' => $model,
            '--fields' => 'name:s.n40,age:i.+,descricao:s.u,modelo_id:i.u*,fabrica_id:i.u*,ativo:b',
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));
    }
}