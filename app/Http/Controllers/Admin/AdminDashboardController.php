<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', 'active')->count();
        $expiredCustomers = Customer::where('status', 'expired')->count();
        $pendingPayments = Payment::where('status', 'pending')->count();
        $expiringSoon = Subscription::where('status', 'active')
            ->whereBetween('end_date', [Carbon::today(), Carbon::today()->addDays(7)])
            ->count();

        $monthlyRevenue = Payment::where('status', 'approved')
            ->whereMonth('paid_at', Carbon::now()->month)
            ->whereYear('paid_at', Carbon::now()->year)
            ->sum('amount');

        $totalRevenue = Payment::where('status', 'approved')->sum('amount');

        $recentCustomers = Customer::query()
            ->latest()
            ->take(8)
            ->get(['id', 'name', 'phone', 'plan', 'status', 'created_at']);

        return view('admins.admin-dashboard', compact(
            'totalCustomers',
            'activeCustomers',
            'expiredCustomers',
            'pendingPayments',
            'expiringSoon',
            'monthlyRevenue',
            'totalRevenue',
            'recentCustomers'
        ));
    }
}

