<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeFrontModelIndexTest extends TestCase
{
    /** @test */
    function it_creates_a_front_model_index_test()
    {
        $model = 'ComplexModel';
        $module = 'NewModule';
        // destination path of the FrontModel class
        $testClass = str_replace("tests/Unit", "", __DIR__) . "front/src/pages/{$module}/{$model}/{$model}IndexPage.vue";

        // Run the make command
        Artisan::call('laravue:front-model-index', [
            'model' => $model,
            '--module' => $module,
            '--fields' => 'name:s.n40,age:i,data_inicio:d,data_fim:d.n,ativo:b,hora:t,user_id',
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));
    }
}
