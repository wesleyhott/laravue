<?php

namespace wesleyhott\Laravue\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use wesleyhott\Laravue\Tests\TestCase;

class MakeBuildCommandTest extends TestCase
{
    /** @test */
    function it_creates_a_build_command_test_file()
    {
        $deleteAfterCreation = false;
        $prefix = date('Y_m_d_His');
        $model = array('TestFieldOption');
        $model2 = array('Nutrition');
        $schema2 = 'recipe';
        $fields = 'name:s.n40,age:i,data_inicio:d,data_fim:d.n,ativo:b,hora:t';
        $fields2 = 'abbreviation:s.50u,name:s.100u,description:s.n';

        // destination path of the Migration class
        $migrationPath = "database/migrations/{$prefix}_create_test_field_options_table.php";
        $migrationPath2 = "database/migrations/{$prefix}_recipe_nutrition_table.php";

        // destination path of the Seeder class
        $seeder = "database/seeders/" . $model[0] . "Seeder.php";
        $seeder2 = "database/seeders/{$schema2}" . $model2[0] . "Seeder.php";

        // destination path of the Controller class
        // $controller = $this->makeCleanStateTest( "app/Http/Controllers/{$model}Controller.php" );
        // destination path of the FrontModel class
        // $frontModel = $this->makeCleanStateTest( "Frontend/LaravueTest/Views/Pages/{$model}/forms/Model.vue" );

        // Run the make command
        // Artisan::call('laravue:build', [
        //     'model' => $model,
        //     '--fields' => $fields,
        //     '--view' => true,
        // ]);

        Artisan::call('laravue:build', [
            'model' => $model2,
            '--schema' => $schema2,
            '--fields' => $fields2,
        ]);

        // Assert a new files were created
        $this->makeTest($migrationPath, $deleteAfterCreation);
        $this->makeTest($migrationPath2, $deleteAfterCreation);

        // $this->makeTest($seeder, $deleteAfterCreation);
        $this->makeTest($seeder2, $deleteAfterCreation);
    }

    // function makeCleanStateTest($path)
    // {
    //     $testClass = str_replace("tests/Feature", "", __DIR__) . $path;
    //     // make sure we're starting from a clean state
    //     if (File::exists($path)) {
    //         unlink($testClass);
    //     }

    //     $this->assertFalse(File::exists($testClass));

    //     return $testClass;
    // }

    function makeTest($path, $deleteAfter = true)
    {
        $file = str_replace("tests/Feature", "", __DIR__) . $path;

        // Assert a new file is created
        $this->assertTrue(File::exists($file));

        if ($deleteAfter && File::exists($file)) {
            unlink($file);
            $this->assertFalse(File::exists($file));
        }
    }
}
