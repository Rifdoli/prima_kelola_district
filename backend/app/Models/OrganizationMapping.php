<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

#[Fillable(['ancestor_id', 'descendant_id', 'depth', 'is_active'])]
class OrganizationMapping extends Model
{
    protected $primaryKey = 'organization_mapping_id';

    /**
     * Bootstrap model event listeners.
     */
    protected static function booted(): void
    {
        static::creating(function (OrganizationMapping $mapping) {
            if (Auth::check()) {
                $mapping->created_by ??= Auth::id();
                $mapping->updated_by ??= Auth::id();
            }
        });

        static::updating(function (OrganizationMapping $mapping) {
            if (Auth::check()) {
                $mapping->updated_by = Auth::id();
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
            'depth' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function ancestor(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'ancestor_id', 'organization_id');
    }

    public function descendant(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'descendant_id', 'organization_id');
    }
}
