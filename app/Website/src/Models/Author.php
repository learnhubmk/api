<?php

namespace App\Website\Models;

use App\Framework\Models\User;
use App\Website\Database\Factories\AuthorFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Class Author
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $image
 * @property string|null $bio
 * @property string|null $website_url
 * @property string|null $linkedin_url
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User $user
 * @property Collection|BlogPost[] $blog_posts
 *
 * @template TFactory of Factory<Author>
 * @use HasFactory<TFactory>
 */
class Author extends Model
{
    use HasFactory;

    protected $table = 'authors';

    protected $casts = [
        'user_id' => 'int',
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'image',
        'bio',
        'website_url',
        'linkedin_url',
        'user_id',
    ];

    /**
     * Get the user that owns the author.
     *
     * @return BelongsTo<User, Author>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the blog posts associated with the author.
     *
     * @return HasMany<BlogPost, Author>
     */
    public function blog_posts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    /**
     * @return TFactory
     */
    protected static function newFactory()
    {
        return AuthorFactory::new();
    }
}
