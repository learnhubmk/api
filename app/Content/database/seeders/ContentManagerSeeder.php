<?php

namespace App\Content\Database\Seeders;

use App\Framework\Models\User;
use App\Framework\Enums\RoleName;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ContentManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'email' => 'content@learnhub.mk',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now()
        ]);

        $user->assignRole(RoleName::CONTENT_MANAGER->value);
    }
}
