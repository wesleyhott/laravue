<?php

namespace Mpmg\Laravue\Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Mpmg\Laravue\Tests\TestCase;

class MakeLaravueModelCommandTest extends TestCase
{
    /** @test */
    function it_creates_a_new_model_class()
    {
        // destination path of the Foo class
        $testClass = str_replace( "tests/Feature", "", __DIR__) . "app/Models/Test.php";

        // make sure we're starting from a clean state
        if (File::exists($testClass)) {
            unlink($testClass);
        }

        $this->assertFalse(File::exists($testClass));

        // Run the make command
        Artisan::call('laravue:model Test');

        // Assert a new file is created
        $this->assertTrue(File::exists($testClass));

        // Assert the file contains the right contents
        $expectedContents = <<<CLASS
        <?php

        namespace App\Models;
        
        class Test extends LaravueModel
        {
            // Redefinir nome da tabela quando ele não seguir o padrão de pluralização.
            // protected \$table = '';
        
            /**
             * Implementação do método da classe abstrata em BaseModel
             *
             * @return array ('nome da coluna do banco', 'nome mapeado')
             */
            public function mapColumns() {
                return [];
            }

            // {{ laravue-insert:relationship }}
        }
        CLASS;

        $this->assertEquals($expectedContents, file_get_contents($testClass));
    }
}