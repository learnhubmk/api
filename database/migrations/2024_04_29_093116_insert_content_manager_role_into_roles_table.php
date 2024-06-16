<?php

use App\Framework\Enums\RoleName;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Role::create(['name' => RoleName::CONTENT_MANAGER->value]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Role::where('name', RoleName::CONTENT_MANAGER->value)->delete();
    }
};
