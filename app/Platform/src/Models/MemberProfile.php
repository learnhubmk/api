<?php

namespace App\Platform\Models;

use App\Platform\Database\factories\MemberProfileFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberProfile extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return MemberProfileFactory::new();
    }
}
