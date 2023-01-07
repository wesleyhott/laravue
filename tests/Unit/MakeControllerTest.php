<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeControllerTest extends TestCase
{
    /** @test */
    function it_makes_a_controller_test()
    {
        $model = array('ComplexModel');
        $resource = str_replace("tests/Unit", "", __DIR__) . "app/Http/Controllers/" . $model[0] . "Controller.php";

        // Run the make command
        Artisan::call('laravue:controller', [
            'model' => $model,
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($resource));
    }
}
