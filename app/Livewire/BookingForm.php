<?php

namespace App\Livewire;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BookingForm extends Component
{
    public $excursion;

    public $selectedSchedule = '';

    public $peopleCount = 1;

    public $totalPrice = 0;

    public array $selectedExtras = [];

    protected $rules = [
        'selectedSchedule' => 'required|integer|exists:schedules,id',
        'peopleCount' => 'required|integer|min:1|max:100',
    ];

    public function mount($excursion)
    {
        $this->excursion = $excursion;
        $this->selectedExtras = [];
        $requestedSchedule = request()->query('schedule');
        if ($requestedSchedule && $this->excursion->schedules()
            ->whereKey($requestedSchedule)
            ->upcoming()
            ->exists()) {
            $this->selectedSchedule = (string) $requestedSchedule;
        }
        $this->recalculateTotal();
    }

    public function updatedPeopleCount()
    {
        if (! is_numeric($this->peopleCount) || (int) $this->peopleCount < 1) {
            $this->peopleCount = 1;
        }

        $this->recalculateTotal();
    }

    public function updatedSelectedSchedule()
    {
        $this->recalculateTotal();
    }

    public function updatedSelectedExtras(): void
    {
        $this->recalculateTotal();
    }

    private function recalculateTotal()
    {
        if (! $this->selectedSchedule) {
            $this->totalPrice = 0;

            return;
        }

        $schedule = $this->excursion
            ->schedules()
            ->with(['bookings' => function ($q) {
                $q->whereIn('status', ['pending', 'confirmed']);
            }])
            ->find($this->selectedSchedule);

        if (! $schedule) {
            $this->totalPrice = 0;

            return;
        }

        // 🧮 Сколько уже забронировано
        $booked = $schedule->bookings->sum('people_count');
        $remaining = max(0, $schedule->max_people - $booked);

        if ($this->peopleCount > $remaining) {
            $this->peopleCount = $remaining;
            session()->flash('error', "Доступно только {$remaining} мест(а).");
        }

        $people = (int) $this->peopleCount;
        if ($people <= 0) {
            $this->totalPrice = 0;

            return;
        }

        // 1) Базовая цена за людей
        $basePrice = (float) $this->excursion->priceForPeople($people);

        // 2) Цена доп. услуг
        $extrasPrice = 0.0;
        $extraServices = $this->excursion->extraServices()
            ->whereIn('id', $this->selectedExtras ?: [])
            ->where('is_active', true)
            ->get();

        foreach ($extraServices as $service) {
            if ($service->per_person) {
                $extrasPrice += (float) $service->price * $people;
            } else {
                $extrasPrice += (float) $service->price;
            }
        }

        $this->totalPrice = $basePrice + $extrasPrice;
    }

    public function book()
    {
        $this->validate();

        $schedule = $this->excursion->schedules()
            ->whereKey($this->selectedSchedule)
            ->upcoming()
            ->with(['bookings' => fn ($query) => $query->whereIn('status', ['pending', 'confirmed'])])
            ->first();

        if (! $schedule || $schedule->bookings->sum('people_count') >= $schedule->max_people) {
            $this->addError('selectedSchedule', 'Этот сеанс уже недоступен. Выберите другую дату.');

            return;
        }

        if (! Auth::check()) {
            session()->flash('error', 'Вы должны войти в систему для бронирования.');

            return;
        }

        $people = (int) $this->peopleCount;
        if ($people < 1) {
            session()->flash('error', 'Количество участников должно быть не менее 1.');

            return;
        }

        if (! $this->selectedSchedule) {
            session()->flash('error', 'Выберите дату экскурсии.');

            return;
        }

        // на всякий случай пересчитаем тотал перед созданием
        $this->recalculateTotal();

        // создаём бронирование
        $booking = Booking::create([
            'user_id' => Auth::id(),
            'schedule_id' => $this->selectedSchedule,
            'people_count' => $people,
            'total_price' => $this->totalPrice,
            'status' => 'pending',   // статус заявки
            // 'payment_status' => 'unpaid', // можно не указывать, если в миграции стоит default
        ]);

        // привязываем доп. услуги, если выбраны
        if (! empty($this->selectedExtras)) {
            $extraServices = $this->excursion->extraServices()
                ->whereIn('id', $this->selectedExtras ?: [])
                ->where('is_active', true)
                ->get();

            foreach ($extraServices as $service) {
                $unitPrice = (float) $service->price;
                $quantity = $service->per_person ? $people : 1;
                $total = $unitPrice * $quantity;

                $booking->extraServices()->attach($service->id, [
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $total,
                ]);
            }
        }

        // 👉 вместо флеша и ресета — уводим на страницу "оплаты"
        return redirect()->route('payment.booking.show', $booking);
    }

    public function render()
    {
        return view('livewire.booking-form', [
            'schedules' => $this->excursion->schedules()
                ->upcoming()
                ->with(['bookings' => fn ($query) => $query->whereIn('status', ['pending', 'confirmed'])])
                ->orderBy('date')->orderBy('start_time')->get(),
        ]);
    }
}
