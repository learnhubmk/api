<?php

namespace App\Website\Database\Seeders;

use App\Website\Models\BlogPost;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BlogPost::factory(50)->create();
    }
}
