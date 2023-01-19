<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeRequestTest extends TestCase
{
    /** @test */
    function it_makes_a_request_test()
    {
        $model = array('ComplexModel');
        // destination path of the Foo class
        $request = str_replace("tests/Unit", "", __DIR__) . "app/Http/Requests/" . $model[0] . "Request.php";
        $storeRequest = str_replace("tests/Unit", "", __DIR__) . "app/Http/Requests/Store" . $model[0] . "Request.php";
        $updateRequest = str_replace("tests/Unit", "", __DIR__) . "app/Http/Requests/Update" . $model[0] . "Request.php";

        $fields = "name:s.50u#'John Doe'#,age:i.+.#40#,user_id:i.n,birthday:d.uc,awakeAt:t,foreingCitzen:b,wage:de.5-2uc,big_file_id:bi.uc";

        // Run the make command
        Artisan::call('laravue:request', [
            'model' => $model,
            '--fields' => $fields,
        ]);

        Artisan::call('laravue:request', [
            'model' => $model,
            '--fields' => $fields,
            '--store' => true,
        ]);

        Artisan::call('laravue:request', [
            'model' => $model,
            '--fields' => $fields,
            '--update' => true,
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($request));
        $this->assertTrue(File::exists($storeRequest));
        $this->assertTrue(File::exists($updateRequest));
    }
}
