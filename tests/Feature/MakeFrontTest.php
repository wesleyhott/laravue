<?php

namespace wesleyhott\Laravue\Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeFrontTest extends TestCase
{
    /** @test */
    function it_makes_a_front_test()
    {
        $delete_file_after_test = true;

        $model = 'TestFieldOption';
        // destination path of classes
        $base_url = str_replace("tests/Unit", "", __DIR__);
        $test_class_route =  $base_url . "front/src/router/routes.ts";
        $test_class_index = $base_url . "front/src/router/modules/index.ts";

        // Run the make command
        Artisan::call('laravue:front', [
            'model' => $model,
            '--module' => 'NewModule',
        ]);

        // Assert a new file is created
        $this->doTest($test_class_route);
        $this->doTest($test_class_index);
    }

    function doTest($path, $delete_file = true)
    {
        // Assert a new file is created
        $this->assertTrue(File::exists($path));

        if ($delete_file && File::exists($path)) {
            unlink($path);
            $this->assertFalse(File::exists($path));
        }
    }
}
