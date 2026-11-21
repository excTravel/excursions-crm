<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Location extends Model
{
    use HasFactory;

    protected $fillable = ['city', 'country', 'slug'];

    protected static function booted()
    {
        static::creating(function ($loc) {
            if (empty($loc->slug)) {
                $loc->slug = Str::slug(trim($loc->city.'-'.($loc->country ?? '')));
            }
        });
    }

    public function excursions()
    {
        return $this->hasMany(Excursion::class);
    }
}
