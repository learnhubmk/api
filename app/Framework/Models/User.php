<?php

namespace App\Framework\Models;

use App\Admin\Models\AdminProfile;
use App\Admin\Models\ContentManagerProfile;
use App\Admin\Models\MemberProfile;
use App\Authentication\Notifications\ResetPassword;
use App\Website\Models\Author;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use SensitiveParameter;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 *
 * @property int $id
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string|null $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $status
 * @property string|null $deleted_at
 *
 * @property Collection|Author[] $authors
 * @property Collection|MemberProfile[] $member_profiles
 *
 * @package App\Models
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    protected $table = 'users';

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $fillable = [
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'status',
    ];

    public function authors(): HasMany
    {
        return $this->hasMany(Author::class);
    }

    public function member_profiles(): HasMany
    {
        return $this->hasMany(MemberProfile::class);
    }

    public function guardName(): string
    {
        return 'api';
    }

    /***
     * Determines the proper factory for the model.
     */
    protected static function newFactory(): UserFactory
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

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier(): string
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification(#[SensitiveParameter] $token): void
    {
        $this->notify(new ResetPassword($token));
    }
}
