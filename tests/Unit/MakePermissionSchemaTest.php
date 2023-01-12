<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakePermissionSchemaTest extends TestCase
{
    /** @test */
    function it_makes_a_permission_schema_test()
    {
        $resource = str_replace("tests/Unit", "", __DIR__) . "database/seeders/LaravueSeeder.php";

        // Run the make command
        Artisan::call('laravue:permission', [
            'model' => array('ComplexModel'),
            '--schema' => 'Schema'
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($resource));
    }
}
