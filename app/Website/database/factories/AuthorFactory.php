<?php

namespace App\Website\Database\Factories;

use App\Models\User;
use App\Website\Models\BlogPost;
use App\Website\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Website\Models\Model>
 */
class AuthorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->name,
            'last_name' => fake()->lastName,
            'user_id' => User::all()->random()->id,
        ];
    }

}
