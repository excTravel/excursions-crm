<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExcursionPhoto extends Model
{
    use HasFactory;

    protected $fillable = ['excursion_id','path','sort_order'];

    public function excursion()
    {
        return $this->belongsTo(Excursion::class);
    }
}
