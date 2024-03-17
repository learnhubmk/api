<?php

namespace App\Website\Database\Factories;

use App\Website\Enums\BlogPostStatus;
use App\Website\Models\Author;
use App\Website\Models\BlogPost;
use App\Website\Models\BlogPostTag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Website\Models\Model>
 */
class BlogPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence;
        $slug = Str::slug($title, '-');

        return [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $this->faker->text,
            'author_id' => Author::all()->random()->id,
            'status' => BlogPostStatus::PUBLISHED,
            'publish_date' => now()
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (BlogPost $blogPost) {
            $tags = BlogPostTag::all()->random()->limit(3)->get();
            $blogPost->tags()->sync($tags);
        });
    }
}
