<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeServiceSchemaTest extends TestCase
{
    /** @test */
    function it_makes_a_service_schema_test()
    {
        $model = array('ComplexModel');
        $schema = 'Schema';
        $resource = str_replace("tests/Unit", "", __DIR__) . "app/Services/{$schema}/" . $model[0] . "Service.php";

        $fields = "name";

        // Run the make command
        Artisan::call('laravue:service', [
            'model' => $model,
            '--schema' => $schema,
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($resource));
    }
}
