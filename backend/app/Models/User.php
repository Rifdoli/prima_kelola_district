<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name', 'email', 'password', 'role_id',
    'username', 'nik', 'phone_number', 'password_valid_until',
    'is_ldap', 'is_active', 'allow_be_login',
    'organization_id', 'parent_user_id',
    'photo', 'device_token', 'telegram_id',
    'mfa_type', 'tag',
])]
#[Hidden(['password', 'remember_token', 'device_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    /**
     * Bootstrap model event listeners.
     */
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            $user->uuid ??= (string) Str::uuid();

            if (Auth::check()) {
                $user->created_by ??= Auth::id();
                $user->updated_by ??= Auth::id();
            }
        });

        static::updating(function (User $user) {
            if (Auth::check()) {
                $user->updated_by = Auth::id();
            }
        });
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    /**
     * Atasan/induk user ini (self-referencing).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_user_id');
    }

    /**
     * User-user yang berada di bawah user ini (self-referencing).
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_user_id');
    }

    /**
     * Disiapkan untuk relasi ke tabel organizations (belum ada, dibuat di issue terpisah).
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
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
            'password' => 'hashed',
            'is_ldap' => 'boolean',
            'is_active' => 'boolean',
            'allow_be_login' => 'boolean',
            'last_update_password' => 'datetime',
            'last_login_at' => 'datetime',
            'password_valid_until' => 'datetime',
            'last_verify_mfa_at' => 'datetime',
        ];
    }
}
