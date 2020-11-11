<?php

namespace Mpmg\Laravue\Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Mpmg\Laravue\Tests\TestCase;

class MakeLaravueInstallCommandTest extends TestCase
{
    /** @test */
    function it_executes_laravue_install_command_file()
    {
        $deleteAfterCreation = true;

        // destination path of the Test class
        // Config
        $ConfigAuthClass = $this->makeTestClass( str_replace( "tests/Feature", "", __DIR__) . "config/auth.php" );
        // Provider
        $ProviderAuthClass = $this->makeTestClass( str_replace( "tests/Feature", "", __DIR__) . "app/Providers/AuthServiceProvider.php" );

        // Run the make command
        Artisan::call('laravue:install');

        // Assert a new files are created
        // Config
        $this->makeTest($ConfigAuthClass, $deleteAfterCreation);
        // Provider
        $this->makeTest($ProviderAuthClass, $deleteAfterCreation);
    }

    function makeTestClass($testClass){
        // make sure we're starting from a clean state
        if (File::exists($testClass)) {
            unlink($testClass);
        }

        $this->assertFalse(File::exists($testClass));

        return $testClass;
    }

    function makeTest($file, $deleteAfter = true){
        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));

        if( $deleteAfter && File::exists( $file ) ) {
            unlink($file);
        }
    }
}