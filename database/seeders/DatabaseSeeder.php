<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::factory(100)->create();
        \App\Models\Category::factory(10)->create();
        \App\Models\Progress_list::factory(2500)->create();
        \App\Models\Image::factory(2500)->create();
        \App\Models\Video::factory(500)->create();
        \App\Models\Task::factory(1500)->create();
    }
}
