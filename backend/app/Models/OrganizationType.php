<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

#[Fillable(['name', 'level', 'is_active'])]
class OrganizationType extends Model
{
    protected $primaryKey = 'organization_type_id';

    /**
     * Bootstrap model event listeners.
     */
    protected static function booted(): void
    {
        static::creating(function (OrganizationType $organizationType) {
            $organizationType->uuid ??= (string) Str::uuid();

            if (Auth::check()) {
                $organizationType->created_by ??= Auth::id();
                $organizationType->updated_by ??= Auth::id();
            }
        });

        static::updating(function (OrganizationType $organizationType) {
            if (Auth::check()) {
                $organizationType->updated_by = Auth::id();
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
            'level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'organization_type_id', 'organization_type_id');
    }
}
