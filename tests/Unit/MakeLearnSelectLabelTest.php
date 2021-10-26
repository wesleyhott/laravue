<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeLearnSelectLabelTest extends TestCase
{
    /** @test */
    function it_make_a_learn_select_label_test()
    {
        $words = array('nom_lapis');
        // destination path of the Foo class
        $testClass = str_replace( "tests/Unit", "", __DIR__) . "config/config.php";

        // Run the make command
        Artisan::call('laravue:learn', [
            'words' => $words,
            '--selectlabel' => true,
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));
    }
}