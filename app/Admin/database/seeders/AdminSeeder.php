<?php

namespace App\Admin\Database\Seeders;

use App\Admin\Models\AdminProfile;
use App\Admin\Models\Author;
use App\Framework\Enums\RoleName;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Framework\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->create([
            'email' => 'admin@learnhub.mk',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now()
        ]);

        $user->assignRole(RoleName::ADMIN->value);

        AdminProfile::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'Adminovski',
            'user_id' => $user->id
        ]);

        Author::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'Adminovski',
            'user_id' => $user->id
        ]);
    }
}
