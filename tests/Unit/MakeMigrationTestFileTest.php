<?php

namespace Mpmg\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Mpmg\Laravue\Tests\TestCase;

class MakeMigrationTestFileTest extends TestCase
{
    /** @test */
    function it_creates_a_migration_test_file()
    {
        $prefix = date('Y_m_d_His');
        $model = 'TestFieldOption';
        // destination path of the Foo class
        $testClass = str_replace( "tests/Unit", "", __DIR__) . "database/migrations/$prefix"."_create_test_field_option"."_table.php";

        // Run the make command
        Artisan::call('laravue:migration', [
            'model' => $model,
            '--fields' => 'name:s.50n,age:i,user_id:i.n,file_id:i',
        ]);

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));
    }
}