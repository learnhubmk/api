<?php

namespace App\Admin\Database\factories;

use App\Admin\Models\AdminProfile;
use App\Framework\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdminProfile>
 */
class AdminProfileFactory extends Factory
{
    protected $model = AdminProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'first_name' => fake()->name(),
            'last_name' => fake()->lastName(),
            'image' => fake()->imageUrl(),
        ];
    }
}
