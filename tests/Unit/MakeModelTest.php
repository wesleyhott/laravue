<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeModelTest extends TestCase
{
    /** @test */
    function it_makes_a_model_test()
    {
        $model = array('ComplexModel');
        // destination path of the Foo class
        $testClass1 = str_replace("tests/Unit", "", __DIR__) . "app/Models/Userfake.php";
        $testClass2 = str_replace("tests/Unit", "", __DIR__) . "app/Models/TypeProject.php";
        $testClass = str_replace("tests/Unit", "", __DIR__) . "app/Models/" . $model[0] . ".php";

        // Run the make command
        Artisan::call('laravue:model', [
            'model' => 'Userfake',
            '--fields' => 'name',
        ]);

        // Run the make command
        Artisan::call('laravue:model', [
            'model' => 'TypeProject',
            '--fields' => 'name',
        ]);

        // Run the make command
        Artisan::call('laravue:model', [
            'model' => $model,
            '--fields' => 'name:s.n40,age:i,wage:de.5-2,user_fake_id,type_project_id',
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass1));
        $this->assertTrue(File::exists($testClass2));
        $this->assertTrue(File::exists($testClass));
    }
}
