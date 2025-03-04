<?php

namespace App\Website\Database\Factories;

use App\Website\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Framework\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Model>
 */
class AuthorFactory extends Factory
{
    protected $model = Author::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $imageUrl = 'https://images.pexels.com/photos/3772623/pexels-photo-3772623.jpeg';
        return [
            'first_name' => fake()->name,
            'last_name' => fake()->lastName,
            'image' => $imageUrl,
            'bio' => fake()->text,
            'website_url' => fake()->url(),
            'linkedin_url' => fake()->url(),
            'user_id' => User::all()->random()->id,
        ];
    }
}
