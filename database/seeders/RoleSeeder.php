<?php

namespace Database\Seeders;

use App\Framework\Enums\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => RoleName::ADMIN->value, 'guard_name' => 'api']);
        Role::create(['name' => RoleName::MEMBER->value, 'guard_name' => 'api']);
    }
}
