<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Platform\Enums\UserStatusName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status' => UserStatusName::class,
    ];

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function scopeFilter($query, array $filters)
    {
        $query
            ->when(Arr::get($filters, 'first_name'), function ($query, $firstName) {
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
