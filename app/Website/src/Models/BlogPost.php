<?php

namespace App\Website\Models;

use App\Website\Database\Factories\BlogPostFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $excerpt
 * @property string $content
 * @property string $status
 * @property int $author_id
 * @property string|null $publish_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Author                       $author
 * @property-read Collection<int, BlogPostTag> $tags
 * @property-read int|null                     $tags_count
 *
 * @method static BlogPostFactory factory($count = null, $state = [])
 * @method static Builder|BlogPost newModelQuery()
 * @method static Builder|BlogPost newQuery()
 * @method static Builder|BlogPost query()
 * @method static Builder|BlogPost whereAuthorId($value)
 * @method static Builder|BlogPost whereContent($value)
 * @method static Builder|BlogPost whereCreatedAt($value)
 * @method static Builder|BlogPost whereExcerpt($value)
 * @method static Builder|BlogPost whereId($value)
 * @method static Builder|BlogPost wherePublishDate($value)
 * @method static Builder|BlogPost whereSlug($value)
 * @method static Builder|BlogPost whereStatus($value)
 * @method static Builder|BlogPost whereTitle($value)
 * @method static Builder|BlogPost whereUpdatedAt($value)
 *
 * @mixin Eloquent
 *
 * @template TFactory of Factory<BlogPost>
 *
 * @use HasFactory<TFactory>
 */
class BlogPost extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'status',
        'author_id',
        'publish_date',
        'content',
        'image',
    ];

    protected $casts = [
        'publish_date' => 'date',
    ];

    /**
     * @return BelongsTo<Author, BlogPost>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    /**
     * @return BelongsToMany<BlogPostTag, BlogPost>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogPostTag::class, 'blog_post_tag_pivot', 'blog_post_id', 'tag_id');
    }

    /**
     * @return Factory<BlogPost>
     */
    protected static function newFactory(): Factory
    {
        return BlogPostFactory::new();
    }
}
