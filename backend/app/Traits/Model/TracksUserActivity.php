<?php

namespace App\Traits\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

trait TracksUserActivity
{
    protected ?string $createdByColumn = 'created_by';
    protected ?string $updatedByColumn = 'updated_by';

    /**
     * Boot the trait and register model event listeners.
     *
     * Laravel automatically calls boot{TraitName}() for every trait used
     * by a model, so no manual registration is required. This naming
     * convention is not explicitly documented in the official Laravel
     * documentation, but it is an internal framework mechanism that can
     * be verified directly in the source of `Model::bootTraits()`
     * (see `Illuminate\Database\Eloquent\Model`). Built-in features such as
     * SoftDeletes itself rely on the exact same pattern (bootSoftDeletes()).
     *
     * @see https://laravel.com/docs/13.x/eloquent#events Official docs for the
     * Eloquent lifecycle events used below (creating, updating, deleting, restoring).
     */
    public static function bootTracksUserActivity(): void
    {
        static::creating(function (Model $model) {
            if (!auth()->check()) {
                return;
            }

            if ($createdByColumn = $model->createdByColumn) {
                $model->$createdByColumn = auth()->id();
            }

            if ($updatedByColumn = $model->updatedByColumn) {
                $model->$updatedByColumn = auth()->id();
            }
        });

        // Set updated_by on every subsequent update.
        static::updating(function (Model $model) {
            if (auth()->check() && ($updatedByColumn = $model->updatedByColumn)) {
                $model->$updatedByColumn = auth()->id();
            }
        });

        // Set updated_by when a soft delete is performed.
        static::deleting(function (Model $model) {
            if (
                !$model->usesSoftDeletes() ||
                $model->isForceDeleting() ||
                !auth()->check()
            ) {
                return;
            }

            if ($updatedByColumn = $model->updatedByColumn) {
                $model->$updatedByColumn = auth()->id();
                $model->saveQuietly();
            }
        });

        // Set updated_by when the model is restored.
        static::restoring(function (Model $model) {
            if (auth()->check() && ($updatedByColumn = $model->updatedByColumn)) {
                $model->$updatedByColumn = auth()->id();
                $model->saveQuietly();
            }
        });
    }

    /**
     * Determine whether the model uses the SoftDeletes trait.
     */
    public function usesSoftDeletes(): bool
    {
        static $cache = [];
        return $cache[static::class]
            ??= in_array(
                SoftDeletes::class,
                class_uses_recursive(static::class),
                true
            );
    }
}
