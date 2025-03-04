<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeServiceTest extends TestCase
{
    /** @test */
    function it_makes_a_service_test()
    {
        $model = array('ComplexModel');
        $resource = str_replace("tests/Unit", "", __DIR__) . "app/Services/" . $model[0] . "Service.php";

        // Run the make command
        Artisan::call('laravue:service', [
            'model' => $model,
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($resource));
    }
}
