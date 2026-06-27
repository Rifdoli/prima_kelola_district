<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

#[Fillable(['name', 'sname', 'description', 'is_active'])]
class Role extends Model
{
    protected $primaryKey = 'role_id';

    /**
     * Bootstrap model event listeners.
     */
    protected static function booted(): void
    {
        static::creating(function (Role $role) {
            $role->uuid ??= (string) Str::uuid();

            if (Auth::check()) {
                $role->created_by ??= Auth::id();
                $role->updated_by ??= Auth::id();
            }
        });

        static::updating(function (Role $role) {
            if (Auth::check()) {
                $role->updated_by = Auth::id();
            }
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
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
