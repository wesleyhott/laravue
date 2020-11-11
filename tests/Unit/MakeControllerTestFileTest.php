<?php

namespace Mpmg\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Mpmg\Laravue\Tests\TestCase;

class MakeControllerTestFileTest extends TestCase
{
    /** @test */
    function it_creates_a_controller_test_file()
    {
        $model = 'TestFieldOption';
        // destination path of the Foo class
        $testClass = str_replace( "tests/Unit", "", __DIR__) . "app/Http/Controllers/TestFieldOptionController.php";

        // Run the make command
        Artisan::call('laravue:controller', [
            'model' => $model,
            '--fields' => 'name:s.n40,age:i',
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));
    }
}