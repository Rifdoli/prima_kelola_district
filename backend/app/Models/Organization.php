<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

#[Fillable(['name', 'sname', 'organization_type_id', 'parent_organization_id', 'timezone', 'is_active', 'notes'])]
class Organization extends Model
{
    protected $primaryKey = 'organization_id';

    /**
     * Bootstrap model event listeners.
     */
    protected static function booted(): void
    {
        static::creating(function (Organization $organization) {
            $organization->uuid ??= (string) Str::uuid();

            if (Auth::check()) {
                $organization->created_by ??= Auth::id();
                $organization->updated_by ??= Auth::id();
            }
        });

        static::updating(function (Organization $organization) {
            if (Auth::check()) {
                $organization->updated_by = Auth::id();
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

    public function type(): BelongsTo
    {
        return $this->belongsTo(OrganizationType::class, 'organization_type_id', 'organization_type_id');
    }

    /**
     * Induk organisasi ini (self-referencing).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_organization_id', 'organization_id');
    }

    /**
     * Organisasi-organisasi yang berada langsung di bawah organisasi ini (self-referencing).
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_organization_id', 'organization_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'organization_id', 'organization_id');
    }
}
