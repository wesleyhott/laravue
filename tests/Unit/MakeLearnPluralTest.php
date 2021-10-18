<?php

namespace Mpmg\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Mpmg\Laravue\Tests\TestCase;

class MakeLearnPluralTest extends TestCase
{
    /** @test */
    function it_make_a_learn_plural_test()
    {
        $words = array('Lapis', 'Lapis');
        // destination path of the Foo class
        $testClass = str_replace( "tests/Unit", "", __DIR__) . "config/config.php";

        // Run the make command
        Artisan::call('laravue:learn', [
            'words' => $words,
            '--plural' => true,
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));
    }
}