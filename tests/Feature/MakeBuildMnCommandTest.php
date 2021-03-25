<?php

namespace Mpmg\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Mpmg\Laravue\Tests\TestCase;

class MakeBuildMnCommandTest extends TestCase
{
    /** @test */
    function it_creates_a_build_mn_command_test_file()
    {
        $deleteAfterCreation = false;
        $model = array('BigFile', 'User');
        // $pivots = 'name:s.n40,age:i,data_inicio:d,data_fim:d.n,ativo:b,hora:t';
        $keys = '';
        $pivots = '';

        // destination path of the Controller class
        $controllerM = $this->makeCleanStateTest( "app/Http/Controllers/". $model[0] ."Controller.php" );
        $controllerN = $this->makeCleanStateTest( "app/Http/Controllers/". $model[1] ."Controller.php" );
        // destination path of the FrontModel class
        $frontModelM = $this->makeCleanStateTest( "frontend/src/components/Laravel/Views/Pages/". $model[0] ."/forms/Model.vue" );
        $frontModelN = $this->makeCleanStateTest( "frontend/src/components/ProjetoBase/Views/Pages/". $model[1] ."/forms/Model.vue" );

        // Run the make command
        Artisan::call('laravue:build', [
            'model' => $model,
            '--keys' => $keys,
            '--pivots' => $pivots,
        ]);

        // Assert a new files were created
        $this->makeTest( $controllerM, $deleteAfterCreation );
        $this->makeTest( $controllerN, $deleteAfterCreation ); 
        $this->makeTest( $frontModelM, $deleteAfterCreation ); 
        $this->makeTest( $frontModelN, $deleteAfterCreation );
    }

    function makeCleanStateTest( $path ) {
        $testClass = str_replace( "tests/Feature", "", __DIR__) . $path;
        // make sure we're starting from a clean state
        // if (File::exists($testClass)) {
        //     unlink($testClass);
        // }

        // $this->assertFalse( File::exists($testClass) );
        return $testClass;
    }

    function makeTest( $file, $deleteAfter = true ) {
        // Assert a new file is created
        $this->assertTrue(File::exists($file));

        if( $deleteAfter && File::exists( $file ) ) {
            unlink($file);
        }
    }
}