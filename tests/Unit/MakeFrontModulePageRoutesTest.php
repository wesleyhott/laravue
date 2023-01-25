<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeFrontModulePageRoutesTest extends TestCase
{
    /** @test */
    function it_creates_a_front_module_page_routes_test()
    {
        $model = 'TestFieldOption';
        $module = 'NewModule';
        // destination path of the FrontModel class
        $testClass = str_replace("tests/Unit", "", __DIR__) . "front/src/router/modules/{$module}.ts";

        // Run the make command
        Artisan::call('laravue:front-module-page-routes', [
            'model' => $model,
            '--module' => $module,
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));
    }
}
