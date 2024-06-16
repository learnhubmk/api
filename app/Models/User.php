<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Admin\Database\factories\ContentManagerProfileFactory;
use App\Admin\Models\AdminProfile;
use App\Admin\Models\MemberProfile;
use App\Enums\RoleName;
use App\Framework\Enums\UserStatusName;
use Arr;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use SoftDeletes;
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status' => UserStatusName::class,
    ];

    public function guardName(): string
    {
        return "web";
    }

    public function adminProfile(): HasOne
    {
        return $this->hasOne(AdminProfile::class);
    }

    public function memberProfile(): HasOne
    {
        return $this->hasOne(MemberProfile::class);
    }

    public function contentManagerProfile(): HasOne
    {
        return $this->hasOne(ContentManagerProfileFactory::class);
    }

    public function scopeFilter($query, array $filters): void
    {
        $relation = match(Arr::get($filters, 'role')) {
            RoleName::MEMBER->value => 'memberProfile',
            RoleName::CONTENT_MANAGER->value => 'contentManagerProfile',
            RoleName::ADMIN->value => 'adminProfile',
            default => null,
        };

        $query->when(Arr::get($filters, 'first_name'), function ($query, $firstName) use ($relation) {
            return $query->whereRelation($relation, 'first_name', 'LIKE', "{$firstName}%");
        })
        ->when(Arr::get($filters, 'last_name'), function ($query, $lastName) use ($relation) {
            return $query->whereRelation($relation, 'last_name', 'LIKE', "{$lastName}%");
        });
    }
}
