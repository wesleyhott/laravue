<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeRouteTest extends TestCase
{
    /** @test */
    function it_makes_a_route_test()
    {
        $model = array('ComplexModel');
        $resource = str_replace("tests/Unit", "", __DIR__) . "routes/api.php";

        // Run the make command
        Artisan::call('laravue:route', [
            'model' => $model,
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($resource));
    }
}
