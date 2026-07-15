<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

#[Fillable(['name','sname','latitude','longitude','address','is_active','organization_id'])]
#[Hidden(['organization_id'])]
class Location extends Model
{
    /**
     * Bootstrap model event listeners.
     */
    protected static function booted(): void
    {
        static::creating(function (Location $loc) {
            if (Auth::check()) {
                $loc->created_by ??= Auth::id();
                $loc->updated_by ??= Auth::id();
            }
        });

        static::updating(function (Location $loc) {
            if (Auth::check()) {
                $loc->updated_by = Auth::id();
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

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    #[Scope]
    protected function active(Builder $query, bool $isActive): void
    {
        $query->where('is_active', $isActive);
    }
}