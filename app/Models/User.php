<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\ActivityLog\Models\Device;
use Modules\PushNotification\Models\FirebaseToken;
use OwenIt\Auditing\Auditable;
use Spatie\Permission\Traits\HasRoles;
use Str;

/**
 * @property int $id
 * @property string $uuid
 * @property string|null $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $phone
 * @property string|null $name
 * @property \Illuminate\Support\Carbon|null $phone_verified_at
 * @property string $password
 * @property bool $is_active
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Device> $devices
 * @property-read int|null $devices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FirebaseToken> $firebaseTokens
 * @property-read int|null $firebase_tokens_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User filter(array $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User notUser()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhoneVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable implements \OwenIt\Auditing\Contracts\Auditable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'phone',
        // 'name' removed, using accessor
        'is_active',
        'last_login_at',
        'provider',
        'provider_id',
        // 'avatar' removed, merged into image
        // Profile fields
        'first_name',
        'last_name',
        'image',
        'gender',
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => trim(($attributes['first_name'] ?? '') . ' ' . ($attributes['last_name'] ?? '')),
        );
    }

    public function getImageAttribute($value): ?string
    {
        if ($value && filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        
        return \App\Services\FileManagerService::getImage($value, default: 'images/person.png');
    }

    public function setImageAttribute($value): void
    {
        if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
            $this->attributes['image'] = $value;
            return;
        }

        $this->attributes['image'] = \App\Services\FileManagerService::uploadFile($value, $this->getRawOriginal('image'), 'images/users');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            $user->uuid = (string) Str::uuid();
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function scopeFilter($query, array $filters): void
    {
        $query->when($filters['search'] ?? null, static function ($query, $search) {
            $query->where(static function ($query) use ($search) {
                $query->orWhereRaw('LOWER(email) like ?', ['%' . strtolower($search) . '%'])
                    ->orWhereRaw('LOWER(phone) like ?', ['%' . strtolower($search) . '%']);
            });
        });
        $query->when(isset($filters['is_active']), static function ($query) use ($filters) {
            $query->where('is_active', (bool)$filters['is_active']);
        });

        $query->when($filters['role_id'] ?? null, static function ($query, $role_id) {
            $query->whereHas('roles', static function ($query) use ($role_id) {
                $query->where('id', $role_id);
            });
        });
    }

    public function scopeNotUser($query)
    {
        return $query->whereHas('roles', static function ($query) {
            $query->where('name', '!=', 'user');
        });
    }

    public function firebaseTokens(): HasMany
    {
        return $this->hasMany(FirebaseToken::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(\Modules\User\Models\UserDocument::class);
    }
}
