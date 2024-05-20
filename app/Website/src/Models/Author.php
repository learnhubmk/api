<?php

namespace App\Website\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $image
 * @property string|null $bio
 * @property string|null $website_url
 * @property string|null $linkedin_url
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @method static \App\Website\Database\Factories\AuthorFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Author newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Author newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Author query()
 * @method static \Illuminate\Database\Eloquent\Builder|Author whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Author whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Author whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Author whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Author whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Author whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Author whereLinkedinUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Author whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Author whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Author whereWebsiteUrl($value)
 * @mixin \Eloquent
 */
class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'image',
        'bio',
        'website_url',
        'linkedin_url',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
