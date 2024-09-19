<?php

namespace App\Website\Models;

use App\Framework\Models\User;
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
 */
class Author extends Model
{
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function blog_posts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }
}
