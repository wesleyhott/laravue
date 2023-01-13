<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeSeedSchemaTest extends TestCase
{
    /** @test */
    function it_makes_a_seed_schema_test()
    {
        $model = array('ComplexModel');
        $schema = 'Schema';
        // destination path of the Foo class
        $testClass = str_replace("tests/Unit", "", __DIR__) . "database/seeders/{$schema}{$model[0]}Seeder.php";

        // Run the make command
        Artisan::call('laravue:seed', [
            'model' => $model,
            '--fields' => "name:s.50u#'John Doe'#,age:i.+.#40#,user_id:i.n,birthday:d,awakeAt:t,foreingCitzen:b,wage:de.5-2",
            '--schema' => $schema,
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));
    }
}
