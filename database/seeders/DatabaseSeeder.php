<?php

namespace Database\Seeders;

use App\Admin\Database\Seeders\AdminSeeder;
use App\Content\Database\Seeders\ContentManagerSeeder;
use App\Framework\Models\User;
use App\Website\Database\Seeders\AuthorSeeder;
use App\Website\Database\Seeders\BlogPostSeeder;
use App\Website\Database\Seeders\TagSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        Artisan::call('db:seed', [
            '--class' => AdminSeeder::class,
            '--module' => 'Admin',
        ]);

        Artisan::call('db:seed', [
            '--class' => ContentManagerSeeder::class,
            '--module' => 'Content',
        ]);

        Artisan::call('db:seed', [
            '--class' => AuthorSeeder::class,
            '--module' => 'Website',
        ]);

        Artisan::call('db:seed', [
            '--class' => TagSeeder::class,
            '--module' => 'Website',
        ]);

        Artisan::call('db:seed', [
            '--class' => BlogPostSeeder::class,
            '--module' => 'Website',
        ]);

    }
}
