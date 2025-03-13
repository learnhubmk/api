<?php

namespace App\Website\Database\Factories;

use App\Framework\Enums\BlogPostStatus;
use App\Website\Models\Author;
use App\Website\Models\BlogPost;
use App\Website\Models\BlogPostTag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @extends Factory<BlogPost>
 */
class BlogPostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = BlogPost::class;

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
            'excerpt' => $this->faker->sentence,
            'author_id' => Author::query()->inRandomOrder()->value('id'),
            'content' => $this->faker->text,
            'status' => BlogPostStatus::PUBLISHED->value,
            'publish_date' => now(),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return static
     */
    public function configure(): static
    {
        return $this->afterCreating(function (BlogPost $blogPost): void {
            $tags = BlogPostTag::query()->inRandomOrder()->limit(3)->pluck('id')->toArray();
            $blogPost->tags()->sync($tags);
        });
    }
}

