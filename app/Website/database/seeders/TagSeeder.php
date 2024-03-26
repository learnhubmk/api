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
        $tags = ['php', 'laravel', 'react', 'javascript', 'mysql',
            'postgresql', 'docker', '.net', 'java', 'ux/ui', 'redis',
            'web', 'development', 'marketing', 'design', 'unity', 'c++',
            'c#', 'python', 'algorithms', 'databases', 'mongodb', 'nodejs',
        ];

        $tagData = [];

        foreach ($tags as $tagName) {
            $tagData[] = ['name' => $tagName];
        }

        BlogPostTag::insert($tagData);

    }
}
