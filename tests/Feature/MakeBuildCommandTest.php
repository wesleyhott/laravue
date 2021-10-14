<?php

namespace Mpmg\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Mpmg\Laravue\Tests\TestCase;

class MakeBuildCommandTest extends TestCase
{
    /** @test */
    function it_creates_a_build_command_test_file()
    {
        $deleteAfterCreation = true;
        $prefix = date('Y_m_d_His');
        $model = array('TestFieldOption');
        $fields = 'name:s.n40,age:i,data_inicio:d,data_fim:d.n,ativo:b,hora:t';

        // destination path of the Migration class
        $migration = $this->makeCleanStateTest( "database/migrations/$prefix"."_create_test_field_option"."_table.php" );
        // destination path of the Seeder class
        $seeder = $this->makeCleanStateTest( "database/seeders/" . $model[0]. "Seeder.php" );
        // destination path of the Controller class
        // $controller = $this->makeCleanStateTest( "app/Http/Controllers/${model}Controller.php" );
        // destination path of the FrontModel class
        // $frontModel = $this->makeCleanStateTest( "Frontend/LaravueTest/Views/Pages/${model}/forms/Model.vue" );

        // Run the make command
        Artisan::call('laravue:build', [
            'model' => $model,
            '--fields' => $fields,
            '--view' => true,
        ]);

        // Assert a new files were created
        $this->makeTest( $migration, $deleteAfterCreation );
        $this->makeTest( $seeder, $deleteAfterCreation );
    }

    function makeCleanStateTest( $path ) {
        $testClass = str_replace( "tests/Feature", "", __DIR__) . $path;
        // make sure we're starting from a clean state
        if (File::exists($testClass)) {
            unlink($testClass);
        }

        $this->assertFalse( File::exists($testClass) );

        return $testClass;
    }

    function makeTest( $file, $deleteAfter = true ) {
        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));

        if( $deleteAfter && File::exists( $file ) ) {
            unlink($file);
        }
    }
}