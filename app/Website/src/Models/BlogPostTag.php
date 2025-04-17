<?php

namespace App\Website\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Class BlogPostTag
 *
 * @property int $id
 * @property string $name
 * @property string|null $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, BlogPost> $blogPosts
 * @property-read int|null $blog_posts_count
 */
class BlogPostTag extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    public $timestamps = false;

    /**
     * Get the blog posts associated with this tag.
     *
     * @return BelongsToMany<BlogPost, BlogPostTag>
     */
    public function blogPosts(): BelongsToMany
    {
        return $this->belongsToMany(
            BlogPost::class,
            'blog_post_tag_pivot',
            'tag_id',
            'blog_post_id'
        );
    }
}
