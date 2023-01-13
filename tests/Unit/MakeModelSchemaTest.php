<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeModelSchemaTest extends TestCase
{
    /** @test */
    function it_makes_a_model_schema_test()
    {
        $model = array('ComplexModel');
        $schema = 'Schema';
        // destination path of the Foo class
        $testClass1 = str_replace("tests/Unit", "", __DIR__) . "app/Models/{$schema}/Userfake.php";
        $testClass2 = str_replace("tests/Unit", "", __DIR__) . "app/Models/{$schema}/TypeProject.php";
        $testClass = str_replace("tests/Unit", "", __DIR__) . "app/Models/{$schema}/{$model[0]}.php";

        // Run the make command
        Artisan::call('laravue:model', [
            'model' => 'Userfake',
            '--schema' => $schema,
            '--fields' => 'name',
        ]);

        // Run the make command
        Artisan::call('laravue:model', [
            'model' => 'TypeProject',
            '--schema' => $schema,
            '--fields' => 'name',
        ]);

        // Run the make command
        Artisan::call('laravue:model', [
            'model' => $model,
            '--schema' => $schema,
            '--fields' => 'name:s.n40,age:i,wage:de.5-2,user_fake_id,type_project_id',
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass1));
        $this->assertTrue(File::exists($testClass2));
        $this->assertTrue(File::exists($testClass));
    }
}
