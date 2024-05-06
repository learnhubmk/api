<?php

namespace App\Admin\Database\Seeders;

use App\Admin\Models\User;
use App\Admin\Enums\RoleName;
use App\Admin\Enums\UserStatusName;
use App\Admin\Models\AdminProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            'email_verified_at' => now(),
            'status' => UserStatusName::ACTIVE
        ]);

        $user->assignRole(RoleName::ADMIN);

        AdminProfile::query()->create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }
}
