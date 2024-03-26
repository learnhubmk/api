<?php

namespace App\Website\Database\Seeders;

use App\Website\Models\BlogPostTag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = ['php', 'laravel', 'react', 'javascript', 'mysql', 'postgresql', 'docker'];

        $tagData = [];

        foreach ($tags as $tagName) {
            $tagData[] = ['name' => $tagName];
        }

        BlogPostTag::insert($tagData);

    }
}
