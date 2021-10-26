<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeLearnAccentuationTest extends TestCase
{
    /** @test */
    function it_make_a_learn_accentuation_test()
    {
        $words = array('Lapis', 'Lápis');
        // destination path of the Foo class
        $testClass = str_replace( "tests/Unit", "", __DIR__) . "config/config.php";

        // Run the make command
        Artisan::call('laravue:learn', [
            'words' => $words,
            '--accentuation' => true,
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));
    }
}