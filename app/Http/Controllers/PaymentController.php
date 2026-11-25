<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function show(Booking $booking)
    {
        // Проверяем, что бронирование принадлежит текущему пользователю
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        // Если уже оплачено, перенаправляем в личный кабинет
        if ($booking->payment_status === 'paid') {
            return redirect()
                ->route('bookings.my')
                ->with('success', 'Это бронирование уже оплачено.');
        }

        return view('payment.booking', compact('booking'));
    }

    public function pay(Request $request, Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->payment_status === 'paid') {
            return redirect()
                ->route('bookings.my')
                ->with('success', 'Это бронирование уже оплачено.');
        }

        // Валидация платежных данных
        $data = $request->validate([
            'card_number' => ['required', 'digits_between:16,19'],
            'card_name' => ['required', 'string', 'max:255'],
            'card_expiry' => ['required', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'], // Формат MM/YY
            'card_cvv' => ['required', 'digits_between:3,4'],
        ]);

        $digits = preg_replace('/\D/', '', $data['card_number']);
        $last4 = substr($digits, -4);

        // Автоматическое подтверждение после успешной оплаты
        $booking->payment_status = 'paid';
        $booking->payment_method = 'card';
        $booking->payment_last4 = $last4;
        $booking->paid_at = now();

        // Подтверждаем бронь, только если она еще не отменена
        if ($booking->status === 'pending') {
            $booking->status = 'confirmed';
        }

        $booking->save();

        return redirect()
            ->route('bookings.my')
            ->with('success', 'Оплата успешно выполнена, бронирование подтверждено.');
    }
}
