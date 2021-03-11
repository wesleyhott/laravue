<?php

namespace Mpmg\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Mpmg\Laravue\Tests\TestCase;

class MakeModelTestFileTest extends TestCase
{
    /** @test */
    function it_creates_a_model_test_file()
    {
        $model = array('TestFieldOption');
        // destination path of the Foo class
        $testClass = str_replace( "tests/Unit", "", __DIR__) . "app/Models/" . $model[0] . ".php";

        // Run the make command
        Artisan::call('laravue:model', [
            'model' => 'Userfake',
            '--fields' => 'name:s',
        ]);

        // Run the make command
        Artisan::call('laravue:model', [
            'model' => 'TypeProject',
            '--fields' => 'name:s',
        ]);

        // Run the make command
        Artisan::call('laravue:model', [
            'model' => $model,
            '--fields' => 'name:s.n40,age:i,userfake_id:i,type_project_id:i',
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));
    }
}