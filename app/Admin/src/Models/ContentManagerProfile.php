<?php

namespace App\Admin\Models;

use App\Admin\Database\factories\ContentManagerProfileFactory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentManagerProfile extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = 'content_manager_profiles';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return ContentManagerProfileFactory::new();
    }
}
