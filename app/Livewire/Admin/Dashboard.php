<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\Excursion;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Dashboard extends Component
{
    public function render()
    {
        // --- Экскурсии ---
        $totalExcursions = Excursion::count();
        $approvedExcursions = Excursion::where('status', 'approved')->count();
        $pendingExcursions = Excursion::where('status', 'pending')->count();
        $archivedExcursions = Excursion::where('status', 'archived')->count();

        // --- Пользователи ---
        $totalUsers = User::count();
        $guides = User::where('role', 'guide')->count();
        $clients = User::where('role', 'client')->count();
        $blockedUsers = User::where('is_blocked', true)->count();

        // --- Бронирования ---
        $totalBookings = Booking::count();
        $completedBookings = Booking::where('status', 'completed')->count();
        $cancelledBookings = Booking::where('status', 'cancelled')->count();
        $totalRevenue = Booking::where('status', 'completed')->sum('total_price');

        // --- Отзывы ---
        $reviewsCount = Review::count();
        $avgRating = round(Review::avg('rating'), 2);

        // --- График роста пользователей по месяцам ---
        $userGrowth = User::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as total')
        )->groupBy('month')->orderBy('month')->get();

        // --- График бронирований по неделям ---
        $bookingTrends = Booking::select(
            DB::raw('WEEK(created_at) as week'),
            DB::raw('COUNT(*) as total')
        )
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('week')
            ->orderBy('week')
            ->get();

        // --- Топ-5 популярных экскурсий (через расписания) ---
        $topExcursions = Excursion::select('excursions.title', DB::raw('COUNT(bookings.id) as bookings_count'))
            ->leftJoin('schedules', 'schedules.excursion_id', '=', 'excursions.id')
            ->leftJoin('bookings', 'bookings.schedule_id', '=', 'schedules.id')
            ->groupBy('excursions.id', 'excursions.title')
            ->orderByDesc('bookings_count')
            ->limit(5)
            ->get();


        return view('livewire.admin.dashboard', [
            'totalExcursions' => $totalExcursions,
            'approvedExcursions' => $approvedExcursions,
            'pendingExcursions' => $pendingExcursions,
            'archivedExcursions' => $archivedExcursions,
            'totalUsers' => $totalUsers,
            'guides' => $guides,
            'clients' => $clients,
            'blockedUsers' => $blockedUsers,
            'totalBookings' => $totalBookings,
            'completedBookings' => $completedBookings,
            'cancelledBookings' => $cancelledBookings,
            'totalRevenue' => $totalRevenue,
            'reviewsCount' => $reviewsCount,
            'avgRating' => $avgRating,
            'userGrowth' => $userGrowth,
            'bookingTrends' => $bookingTrends,
            'topExcursions' => $topExcursions,
        ])->layout('layouts.app');
    }
}
