<?php

namespace App\Website\Models;

use App\Models\BlogPostTagPivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
 * @property BlogPostTagPivot $blog_post_tag_pivot
 */
class BlogPostTag extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    public $timestamps = null;

    public function blogPosts(): BelongsToMany
    {
        return $this->belongsToMany(BlogPost::class, 'blog_post_tag_pivot', 'tag_id', 'blog_post_id');
    }
}
