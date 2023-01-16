<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeFrontModuleRouteTest extends TestCase
{
    /** @test */
    function it_creates_a_front_module_route_test()
    {
        $model = 'TestFieldOption';
        // destination path of the FrontModel class
        $testClass = str_replace("tests/Unit", "", __DIR__) . "front/src/router/routes.ts";

        // Run the make command
        Artisan::call('laravue:front-module-route', [
            'model' => $model,
            '--module' => 'NewModule',
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));
    }
}
