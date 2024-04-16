<?php

namespace App\Admin\Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use App\Platform\Enums\RoleName;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'email' => 'admin@learnhub.mk',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now()
        ]);

        $user->assignRole(RoleName::ADMIN);

        Profile::create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }
}
