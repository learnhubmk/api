<?php

namespace App\Admin\Database\factories;

use App\Admin\Models\MemberProfile;
use App\Framework\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemberProfile>
 */
class MemberProfileFactory extends Factory
{
    protected $model = MemberProfile::class;

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
