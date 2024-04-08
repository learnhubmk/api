<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Admin\Database\Seeders\AdminSeeder;
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
        \App\Models\User::factory(10)->create();

        Artisan::call('db:seed', [
            '--class' => AdminSeeder::class,
            '--module' => 'Admin',
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
