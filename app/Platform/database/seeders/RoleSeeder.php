<?php

namespace App\Platform\Database\Seeders;

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
       $admin = Role::create(['name' => 'Admin']);
       $member = Role::create(['name' => 'Member']);
    }
}
