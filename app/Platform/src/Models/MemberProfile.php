<?php

namespace App\Platform\Models;

use App\Framework\Models\User;
use App\Platform\Database\factories\MemberProfileFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Class MemberProfile
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $image
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property User $user
 */
class MemberProfile extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'member_profiles';

    protected $casts = [
        'user_id' => 'int',
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'image',
        'user_id',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return MemberProfileFactory::new();
    }
}
