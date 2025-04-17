<?php

namespace App\Admin\Models;

use App\Admin\Database\factories\ContentManagerProfileFactory;
use App\Framework\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ContentManagerProfile
 *
 * @extends Model<ContentManagerProfile>
 */
class ContentManagerProfile extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'content_manager_profiles';

    /**
     * @return BelongsTo<User, ContentManagerProfile>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory<ContentManagerProfile>
     */
    protected static function newFactory(): Factory
    {
        return ContentManagerProfileFactory::new();
    }
}
