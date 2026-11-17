<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'excursion_id',
        'coordinates',
        'description',
    ];

    protected $casts = [
        'coordinates' => 'array', // Laravel автоматически преобразует JSON в массив
    ];

    public function excursion()
    {
        return $this->belongsTo(Excursion::class);
    }
}
