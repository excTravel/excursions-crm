<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'excursion_id',
        'rating',
        'comment',
    ];

    // пользователь, оставивший отзыв
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // экскурсия, к которой относится отзыв
    public function excursion()
    {
        return $this->belongsTo(Excursion::class);
    }
}
