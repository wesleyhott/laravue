<?php

namespace wesleyhott\Laravue\Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeLaravueInstallCommandTest extends TestCase
{
    /** @test */
    function it_executes_laravue_install_command_file()
    {
        $deleteAfterCreation = true;

        // destination path of the Test class
        // .gitignore
        $dotGitIgnoreStorageApp = $this->makeTestClass( str_replace( "tests/Feature", "", __DIR__) . "storage/app/.gitignore" );
        $dotGitIgnoreStorageAppReports = $this->makeTestClass( str_replace( "tests/Feature", "", __DIR__) . "storage/app/reports/.gitignore" );
        // Config
        $ConfigAuthClass = $this->makeTestClass( str_replace( "tests/Feature", "", __DIR__) . "config/auth.php" );
        // Model
        $modelFileGenerator = $this->makeTestClass( str_replace( "tests/Feature", "", __DIR__) . "app/Models/FileGenerator.php" );
        // Provider
        $ProviderAppClass = $this->makeTestClass( str_replace( "tests/Feature", "", __DIR__) . "app/Providers/AppServiceProvider.php" );
        $ProviderAuthClass = $this->makeTestClass( str_replace( "tests/Feature", "", __DIR__) . "app/Providers/AuthServiceProvider.php" );
        // Rule
        $ruleIsCpf = $this->makeTestClass( str_replace( "tests/Feature", "", __DIR__) . "app/Rules/IsCpf.php" );
        $ruleIsCnpj = $this->makeTestClass( str_replace( "tests/Feature", "", __DIR__) . "app/Rules/IsCnpj.php" );
        $ruleIsCpfOrCnpj = $this->makeTestClass( str_replace( "tests/Feature", "", __DIR__) . "app/Rules/IsCpfOrCnpj.php" );
        // Seeder
        // Filter
        $AbstractFilter = $this->makeTestClass( str_replace( "tests/Feature", "", __DIR__) . "app/Filters/AbstractFilter.php" );
        $LaravueFilter = $this->makeTestClass( str_replace( "tests/Feature", "", __DIR__) . "app/Filters/LaravueFilter.php" );
        $ModelFilter = $this->makeTestClass( str_replace( "tests/Feature", "", __DIR__) . "app/Filters/ModelFilter.php" );
        $FieldLikeFilter = $this->makeTestClass( str_replace( "tests/Feature", "", __DIR__) . "app/Filters/FieldLike.php" );
        $ActiveEditionFilter = $this->makeTestClass( str_replace( "tests/Feature", "", __DIR__) . "app/Filters/AtivoEdicao.php" );
        $OrderByIdFilter = $this->makeTestClass( str_replace( "tests/Feature", "", __DIR__) . "app/Filters/OrderById.php" );
        $OrderByNameFilter = $this->makeTestClass( str_replace( "tests/Feature", "", __DIR__) . "app/Filters/OrderByName.php" );

        // Run the make command
        Artisan::call('laravue:install');

        // Assert a new files are created
        // .gitignore
        $this->makeTest($dotGitIgnoreStorageApp, $deleteAfterCreation);
        $this->makeTest($dotGitIgnoreStorageAppReports, $deleteAfterCreation);
        // Config
        $this->makeTest($ConfigAuthClass, $deleteAfterCreation);
        // Model
        $this->makeTest($modelFileGenerator, $deleteAfterCreation);
        // Provider
        $this->makeTest($ProviderAppClass, $deleteAfterCreation);
        $this->makeTest($ProviderAuthClass, $deleteAfterCreation);
        // Rule
        $this->makeTest($ruleIsCpf, $deleteAfterCreation);
        $this->makeTest($ruleIsCnpj, $deleteAfterCreation);
        $this->makeTest($ruleIsCpfOrCnpj, $deleteAfterCreation);
        // Seeders
        // Filter
        $this->makeTest($AbstractFilter, $deleteAfterCreation);
        $this->makeTest($LaravueFilter, $deleteAfterCreation);
        $this->makeTest($ModelFilter, $deleteAfterCreation);
        $this->makeTest($FieldLikeFilter, $deleteAfterCreation);
        $this->makeTest($ActiveEditionFilter, $deleteAfterCreation);
        $this->makeTest($OrderByIdFilter, $deleteAfterCreation);
        $this->makeTest($OrderByNameFilter, $deleteAfterCreation);
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
        $this->assertTrue(File::exists($file));

        if( $deleteAfter && File::exists( $file ) ) {
            unlink($file);
        }
    }
}