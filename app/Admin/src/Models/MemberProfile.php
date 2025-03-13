<?php

namespace App\Admin\Models;

use App\Admin\Database\factories\MemberProfileFactory;
use App\Framework\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MemberProfile
 *
 * @extends Model<MemberProfile>
 */
class MemberProfile extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'member_profiles';

    protected $fillable = [
        'first_name',
        'last_name',
        'image',
        'user_id',
    ];
    /**
     * @return BelongsTo<User, MemberProfile>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory<MemberProfile>
     */
    protected static function newFactory(): Factory
    {
        return MemberProfileFactory::new();
    }
}
