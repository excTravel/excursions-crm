<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
