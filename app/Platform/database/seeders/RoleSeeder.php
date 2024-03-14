<?php

namespace App\Platform\Database\Seeders;

use App\Enums\RolesEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $AdminRole =Role::findOrCreate(RolesEnum::ADMIN->value);
        $memberRole =Role::findOrCreate(RolesEnum::MEMBER->value);
    }
}

