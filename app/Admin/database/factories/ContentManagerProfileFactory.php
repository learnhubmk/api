<?php

namespace App\Admin\Database\factories;

use App\Admin\Models\ContentManagerProfile;
use App\Admin\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentManagerProfile>
 */
class ContentManagerProfileFactory extends Factory
{
    protected $model = ContentManagerProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'first_name' => fake()->name(),
            'last_name' => fake()->lastName()
        ];
    }
}
