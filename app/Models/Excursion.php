<?php

namespace App\Models;

use App\Models\Route as ExcursionRoute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Excursion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'price',
        'duration',
        'category_id',
        'guide_id',
        'status',
        'location_id',
        'meeting_point_lat',
        'meeting_point_lng',
        'meeting_point_address',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        // decimal-каст вернёт строку — это ок для точных значений
        'meeting_point_lat' => 'decimal:7',
        'meeting_point_lng' => 'decimal:7',

    ];

    public function priceForPeople(int $peopleCount): ?string
    {
        // Находим подходящее правило:
        // min_people <= N AND (max_people IS NULL OR N <= max_people)
        // Приоритет: самое "узкое" и специфичное правило.
        $rule = $this->pricingRules()
            ->where('min_people', '<=', $peopleCount)
            ->where(function ($q) use ($peopleCount) {
                $q->whereNull('max_people')->orWhere('max_people', '>=', $peopleCount);
            })
            // сначала самые большие min_people (чем выше нижняя граница, тем специфичнее),
            // затем минимальный max_people (чтобы интервал был как можно уже),
            // NULL в конце (по умолчанию NULL в MySQL сортируется последним при asc)
            ->orderBy('min_people', 'desc')
            ->orderBy('max_people', 'asc')
            ->first();

        if (! $rule) {
            // правил нет — вернём базовую логику: цена * людей (как per_person)
            // или null, если хочешь явно требовать наличие правил
            return (string) bcmul((string) $this->price, (string) $peopleCount, 2);
        }

        if ($rule->mode === 'per_group') {
            return (string) $rule->price;
        }

        // per_person
        return (string) bcmul((string) $rule->price, (string) $peopleCount, 2);
    }

    protected static function booted()
    {
        static::creating(function ($exc) {
            if (empty($exc->slug) && ! empty($exc->title)) {
                // обычная попытка
                $slug = Str::slug(mb_substr($exc->title, 0, 80));

                // fallback: ascii()->slug() если пусто (нет intl или кириллица)
                if ($slug === '') {
                    $slug = Str::of($exc->title)->ascii()->slug()->limit(80, '')->value();
                }

                // крайний случай — тех. slug
                if ($slug === '') {
                    $slug = 'exc-'.Str::random(8);
                }

                $exc->slug = $slug;
            }
        });

        static::saving(function ($exc) {
            if (empty($exc->slug) && ! empty($exc->title)) {
                $slug = Str::slug(mb_substr($exc->title, 0, 80));
                if ($slug === '') {
                    $slug = 'exc-'.Str::random(8);
                }
                $exc->slug = $slug;
            }
        });

    }

    // ---- Relations ----
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function guide()
    {
        return $this->belongsTo(User::class, 'guide_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function bookings()
    {
        return $this->hasManyThrough(Booking::class, Schedule::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Несколько маршрутов на одну экскурсию
    public function routes()
    {
        return $this->hasMany(ExcursionRoute::class);
    }

    public function pricingRules()
    {
        return $this->hasMany(PricingRule::class);
    }

    public function photos()
    {
        return $this->hasMany(ExcursionPhoto::class)->orderBy('sort_order');
    }

    public function extraServices()
    {
        return $this->hasMany(\App\Models\ExtraService::class);
    }
}
