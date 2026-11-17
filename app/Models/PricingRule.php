<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'excursion_id',
        'min_people',
        'max_people',
        'price',
        'mode',
    ];

    public function excursion()
    {
        return $this->belongsTo(Excursion::class);
    }
}
