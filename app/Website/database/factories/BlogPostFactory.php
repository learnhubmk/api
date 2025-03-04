<?php

namespace App\Website\Database\Factories;

use App\Framework\Enums\BlogPostStatus;
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
        $imageUrl = 'https://images.pexels.com/photos/147411/italy-mountains-dawn-daybreak-147411.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2';

        return [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $this->faker->sentence,
            'author_id' => Author::all()->random()->id,
            'content' => $this->faker->text,
            'image' => $imageUrl,
            'status' => BlogPostStatus::PUBLISHED,
            'publish_date' => now(),
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
