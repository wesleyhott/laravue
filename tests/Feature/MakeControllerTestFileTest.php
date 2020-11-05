<?php

namespace Mpmg\Laravue\Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Mpmg\Laravue\Tests\TestCase;

class MakeControllerTestFileTest extends TestCase
{
    /** @test */
    function it_creates_a_test_controller_file()
    {
        // destination path of the Foo class
        $testClass = str_replace( "tests/Feature", "", __DIR__) . "app/Http/Controllers/TestController.php";

        // make sure we're starting from a clean state
        if (File::exists($testClass)) {
            unlink($testClass);
        }

        $this->assertFalse(File::exists($testClass));

        // Run the make command
        Artisan::call('laravue:controller', [
            "model" => "Test"
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));
    }
}