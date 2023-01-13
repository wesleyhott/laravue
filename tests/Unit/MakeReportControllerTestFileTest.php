<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeReportControllerTestFileTest extends TestCase
{
    /** @test */
    function it_creates_a_controller_test_file()
    {
        $model = array('TestFieldOption');
        // destination path of the Foo class
        $testClass = str_replace("tests/Unit", "", __DIR__) . "app/Http/Controllers/Reports/TestFieldOptionReportController.php";

        // Run the make command
        Artisan::call('laravue:report', [
            'model' => $model,
            '--fields' => 'name:s.n40,age:i.+,descricao:s.u,modelo_id:i.uc,fabrica_id:i.uc,ativo:b,data_inicio:d',
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));
    }
}
