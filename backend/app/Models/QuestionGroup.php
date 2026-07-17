<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(timestamps: false)]
#[Fillable(['type', 'name', 'weight', 'parent_id'])]
class QuestionGroup extends Model
{
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id')
            ->where('type', 'domain');
    }
}
