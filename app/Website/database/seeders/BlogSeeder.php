<?php

namespace App\Website\Database\Seeders;

use App\Website\Models\Blog;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Blog::factory(50)->create();
    }
}
