<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeApiTest extends TestCase
{
    /** @test */
    function it_makes_a_api_test()
    {
        $model = array('ComplexModel');
        // destination path of the Foo class
        $big_file_resource = str_replace("tests/Unit", "", __DIR__) . "app/Http/Resources/BigFileResource.php";
        $user_resource = str_replace("tests/Unit", "", __DIR__) . "app/Http/Resources/UserResource.php";
        $resource = str_replace("tests/Unit", "", __DIR__) . "app/Http/Resources/" . $model[0] . "Resource.php";

        $fields = "name:s.50u#'John Doe'#,age:i.+.#40#,user_id:i.n,birthday:d,awakeAt:t,foreingCitzen:b,wage:de.5-2,big_file_id";

        // Make auxiliar resources form model attributes test
        Artisan::call('laravue:api', [
            'model' => 'User',
            '--fields' => 'name',
        ]);
        Artisan::call('laravue:api', [
            'model' => 'BigFile',
            '--fields' => 'name',
        ]);

        // Run the make command
        Artisan::call('laravue:api', [
            'model' => $model,
            '--fields' => $fields,
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($user_resource));
        $this->assertTrue(File::exists($big_file_resource));
        $this->assertTrue(File::exists($resource));
    }
}
