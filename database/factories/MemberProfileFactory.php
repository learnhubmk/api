<?php

namespace Database\Factories;

use App\Platform\Models\User;
use App\Platform\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class MemberProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'first_name' => fake()->name(),
            'last_name' => fake()->lastName()
        ];
    }
}
