<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtraService extends Model
{
    protected $fillable = [
        'excursion_id',
        'name',
        'description',
        'price',
        'per_person',
        'is_active',
    ];

    public function excursion(): BelongsTo
    {
        return $this->belongsTo(Excursion::class);
    }
}
