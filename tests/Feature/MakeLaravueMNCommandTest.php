<?php

namespace wesleyhott\Laravue\Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeLaravueMNCommandTest extends TestCase
{
    /** @test */
    function it_executes_laravue_mxn_command_file()
    {
        $deleteAfterCreation = true;

        // destination path of the Test class
        $prefix = date('Y_m_d_His');
        $migrationFile = $this->makeTestClass( str_replace( "tests/Feature", "", __DIR__) . "database/migrations/${prefix}_create_big_file_user_table.php" );
        $seederFile = $this->makeTestClass( str_replace( "tests/Feature", "", __DIR__) . "database/seeders/BigFileUserSeeder.php" );

        // Run the make command
        Artisan::call('laravue:mxn BigFile User -k user_id:s.n -p ativo:b.n');

        // Assert a new files are created
        $this->makeTest($migrationFile, $deleteAfterCreation);
        $this->makeTest($seederFile, $deleteAfterCreation);
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