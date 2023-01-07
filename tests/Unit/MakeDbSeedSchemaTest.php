<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeDbSeedSchemaTest extends TestCase
{
    /** @test */
    function it_makes_a_dbseed_schema_test()
    {
        $model = array('ComplexModel');
        // destination path of the Foo class
        $testClass = str_replace("tests/Unit", "", __DIR__) . "database/seeders/DatabaseSeeder.php";

        // Run the make command
        Artisan::call('laravue:dbseeder', [
            'model' => $model,
            '--schema' => 'schema',
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));
    }
}
