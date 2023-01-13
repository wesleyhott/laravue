<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeControllerSchemaTest extends TestCase
{
    /** @test */
    function it_makes_a_controller_schema_test()
    {
        $model = array('ComplexModel');
        $schema = 'Schema';
        $resource = str_replace("tests/Unit", "", __DIR__) . "app/Http/Controllers/{$schema}/" . $model[0] . "Controller.php";

        // Run the make command
        Artisan::call('laravue:controller', [
            'model' => $model,
            '--schema' => $schema,
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($resource));
    }
}
