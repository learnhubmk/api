<?php

namespace App\Framework\Models;

use App\Admin\Models\AdminProfile;
use App\Admin\Models\ContentManagerProfile;
use App\Framework\Enums\RoleName;
use App\Platform\Models\MemberProfile;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
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
    ];

    public function guardName(): string
    {
        return "web";
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }

    public function adminProfile(): HasOne
    {
        return $this->hasOne(AdminProfile::class);
    }

    public function contentManagerProfile(): HasOne
    {
        return $this->hasOne(ContentManagerProfile::class);
    }

    public function memberProfile(): HasOne
    {
        return $this->hasOne(MemberProfile::class);
    }

    public function profile(RoleName $role): HasOne
    {
        $profile = match($role) {
            RoleName::MEMBER => MemberProfile::class,
            RoleName::CONTENT_MANAGER => ContentManagerProfile::class,
            RoleName::ADMIN => AdminProfile::class,
        };

        return $this->hasOne($profile);
    }

    public function scopeFilter($query, array $filters, string $role): void
    {
        $profileRelation = match(Arr::get($filters, 'role')) {
            RoleName::MEMBER->value => 'memberProfile',
            RoleName::CONTENT_MANAGER->value => 'contentManagerProfile',
            RoleName::ADMIN->value => 'adminProfile',
            default => null,
        };

        $sortBy = $this->getSortByColumnByRole($role, $filters);

        $query
            ->with(['roles', $profileRelation])
            ->whereRelation('roles', 'name', $role)
            ->when(Arr::get($filters, 'first_name'), function ($query, $firstName) use ($profileRelation) {
                return $query->whereRelation($profileRelation, 'first_name', 'LIKE', "$firstName%");
            })->when(Arr::get($filters, 'last_name'), function ($query, $lastName) use ($profileRelation) {
                return $query->whereRelation($profileRelation, 'last_name', 'LIKE', "$lastName%");
            })->orderBy($sortBy, $filters['sortDirection'] ?? 'asc');
    }

    /**
     * @param  string  $role
     * @param  array  $filters
     * @return string
     */
    public function getSortByColumnByRole(string $role, array $filters): string
    {
        return match (true) {
            $role === RoleName::MEMBER->value && Arr::get($filters, 'first_name') => 'member_profiles.first_name',
            $role === RoleName::MEMBER->value && Arr::get($filters, 'last_name') => 'member_profiles.last_name',
            $role === RoleName::ADMIN->value && Arr::get($filters, 'first_name') => 'admin_profiles.first_name',
            $role === RoleName::ADMIN->value && Arr::get($filters, 'last_name') => 'admin_profiles.last_name',
            $role === RoleName::CONTENT_MANAGER->value && Arr::get($filters, 'first_name') => 'content_manager_profiles.first_name',
            $role === RoleName::CONTENT_MANAGER->value && Arr::get($filters, 'last_name') => 'content_manager_profiles.last_name',
            default => 'users.id',
        };
    }
}
