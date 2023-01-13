<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeRequestSchemaTest extends TestCase
{
    /** @test */
    function it_makes_a_request_schema_test()
    {
        $model = array('ComplexModel');
        $schema = 'Schema';
        // destination path of the Foo class
        $request = str_replace("tests/Unit", "", __DIR__) . "app/Http/Requests/{$schema}/" . $model[0] . "Request.php";
        $storeRequest = str_replace("tests/Unit", "", __DIR__) . "app/Http/Requests/{$schema}/Store" . $model[0] . "Request.php";
        $updateRequest = str_replace("tests/Unit", "", __DIR__) . "app/Http/Requests/{$schema}/Update" . $model[0] . "Request.php";

        $fields = "name:s.50u#'John Doe'#,age:i.+.#40#uc,user_id:bi.n,birthday:duc,awakeAt:t,foreingCitzen:b.uc,wage:de.+6-2";

        // Run the make command
        Artisan::call('laravue:request', [
            'model' => $model,
            '--fields' => $fields,
            '--schema' => $schema,
        ]);

        Artisan::call('laravue:request', [
            'model' => $model,
            '--fields' => $fields,
            '--store' => true,
            '--schema' => $schema,
        ]);

        Artisan::call('laravue:request', [
            'model' => $model,
            '--fields' => $fields,
            '--update' => true,
            '--schema' => $schema,
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($request));
        $this->assertTrue(File::exists($storeRequest));
        $this->assertTrue(File::exists($updateRequest));
    }
}
