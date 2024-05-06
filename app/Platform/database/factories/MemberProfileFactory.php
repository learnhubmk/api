<?php

namespace App\Platform\Database\factories;

use App\Platform\Models\User;
use App\Platform\Models\MemberProfile;
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
            'last_name' => fake()->lastName()
        ];
    }
}
