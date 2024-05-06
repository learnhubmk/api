<?php

namespace App\Admin\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Admin\Database\factories\AdminUserFactory;
use App\Admin\Enums\UserStatusName;
use App\Platform\Models\MemberProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
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

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return AdminUserFactory::new();
    }

    public function guardName(): string
    {
        return "web";
    }

    public function profile(): HasOne
    {
        return $this->hasOne(MemberProfile::class);
    }

    public function scopeFilter($query, array $filters): void
    {
        $query->when(Arr::get($filters, 'first_name'), function ($query, $firstName) {
            return $query->whereRelation('profile', 'first_name', 'LIKE', "{$firstName}%");
        })
            ->when(Arr::get($filters, 'last_name'), function ($query, $lastName) {
                return $query->whereRelation('profile', 'last_name', 'LIKE', "{$lastName}%");
            })
            ->when(Arr::get($filters, 'role'), function ($query, $role) {
                return $query->whereRelation('roles', 'name', $role);
            });
    }
}
