<?php

namespace App\Platform\Database\Seeders;

use App\Platform\Enums\RoleName;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //        Http::post()->
        Role::create(['name' => RoleName::ADMIN->value]);
        Role::create(['name' => RoleName::MEMBER->value]);
        Role::create(['name' => RoleName::CONTENT_MANAGER->value]);
    }
}
