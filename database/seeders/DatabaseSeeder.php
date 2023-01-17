<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        // {{ laravue-insert:seed }}
        /** LARAVUE SEEDER MUST BE THE LAST ONE ->  */ $this->call(LaravueSeeder::class);
    }
}
