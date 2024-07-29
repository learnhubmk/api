<?php

namespace Database\Seeders;

use App\Admin\Database\Seeders\AdminSeeder;
use App\Admin\Models\AdminProfile;
use App\Admin\Models\ContentManagerProfile;
use App\Admin\Models\MemberProfile;
use App\Content\Database\Seeders\ContentManagerSeeder;
use App\Framework\Enums\RoleName;
use App\Framework\Models\User;
use App\Website\Database\Seeders\AuthorSeeder;
use App\Website\Database\Seeders\BlogPostSeeder;
use App\Website\Database\Seeders\TagSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory(20)->create();

        $users->map(function (User $user) {
            $role = Arr::random([RoleName::ADMIN, RoleName::MEMBER, RoleName::CONTENT_MANAGER]);

            $user->assignRole($role->value);

            $profile = match($role) {
                RoleName::MEMBER => new MemberProfile(),
                RoleName::CONTENT_MANAGER => new ContentManagerProfile(),
                RoleName::ADMIN => new AdminProfile(),
            };

            $profile::factory()->create([
                'user_id' => $user->id,
            ]);

            return $user;
        });

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
