<?php

namespace wesleyhott\Laravue\Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeControllerUserPermissionFile extends TestCase
{
    /** @test */
    function it_creates_a_user_permission_controller_file()
    {
        // destination path of the Foo class
        $testClass = str_replace( "tests/Feature", "", __DIR__) . "app/Http/Controllers/UserPermissionController.php";

        // make sure we're starting from a clean state
        if (File::exists($testClass)) {
            unlink($testClass);
        }

        $this->assertFalse(File::exists($testClass));

        // Run the make command
        Artisan::call('laravue:install');

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));
    }
}