<?php

namespace Mpmg\Laravue\Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Mpmg\Laravue\Tests\TestCase;

class MakeMpmpModelTest extends TestCase
{
    /** @test */
    function it_creates_a_new_model_class()
    {
        // destination path of the Foo class
        // $testClass = app_path('app/Test.php');
        $testClass = str_replace( "tests/Feature", "", __DIR__) . "app/Test.php";

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

        namespace App;
        
        class Test extends LaravueModel
        {
            // Redefinir nome da tabela, uma vez que o plural não é somente acrescentar 's'.
            // protected \$table = '';
        
            /**
             * Implementação do método da classe abstrata em BaseModel
             *
             * @return array ('nome da coluna do banco', 'nome mapeado')
             */
            public function mapColumns() {
                return [];
            }
        }
        CLASS;

        $this->assertEquals($expectedContents, file_get_contents($testClass));
    }
}