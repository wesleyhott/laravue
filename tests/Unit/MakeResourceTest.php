<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeResourceTest extends TestCase
{
    /** @test */
    function it_makes_a_resource_test()
    {
        $model = array('ComplexModel');
        // destination path of the Foo class
        $resource = str_replace("tests/Unit", "", __DIR__) . "app/Http/Resources/" . $model[0] . "Resource.php";

        $fields = "name:s.50u#'John Doe'#,age:i.+.#40#,user_id:i.n,birthday:d,awakeAt:t,foreingCitzen:b,wage:de.5-2,big_file_id";

        // Run the make command
        Artisan::call('laravue:resource', [
            'model' => $model,
            '--fields' => $fields,
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($resource));
    }
}
