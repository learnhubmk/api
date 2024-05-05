<?php

namespace App\Website\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 *
 *
 * @property int $id
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Website\Models\BlogPost> $blogPosts
 * @property-read int|null $blog_posts_count
 * @method static \Illuminate\Database\Eloquent\Builder|BlogPostTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BlogPostTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BlogPostTag query()
 * @method static \Illuminate\Database\Eloquent\Builder|BlogPostTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlogPostTag whereName($value)
 * @mixin \Eloquent
 */
class BlogPostTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public $timestamps = null;

    public function blogPosts(): BelongsToMany
    {
        return $this->belongsToMany(BlogPost::class, 'blog_post_tag_pivot', 'tag_id', 'blog_post_id');
    }
}
