<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = \App\Models\User::count();
        $activeUsers = \App\Models\User::where('is_active', 1)->count();
        $newUsersToday = \App\Models\User::whereDate('created_at', \Carbon\Carbon::today())->count();
        
        // Fetch Recent Activities from OwenIt Auditing
        if (class_exists(\OwenIt\Auditing\Models\Audit::class)) {
             $recentActivities = \OwenIt\Auditing\Models\Audit::with('user')->latest()->take(5)->get();
        } else {
             $recentActivities = collect([]);
        }
        
        // Stats by Role for the chart
        $rolesStats = \Spatie\Permission\Models\Role::withCount('users')->get();

        // Recent 5 users
        $recentUsers = \App\Models\User::latest()->take(5)->get();

        // Growth Chart Data (Last 7 Days)
        $endDate = \Carbon\Carbon::today();
        $startDate = \Carbon\Carbon::today()->subDays(6);
        $growthDataRaw = \App\Models\User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->groupBy('date')
            ->pluck('count', 'date');

        $growthLabels = [];
        $growthData = [];

        for ($i = 0; $i <= 6; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dateString = $date->toDateString();
            $growthLabels[] = $date->format('D'); // Mon, Tue, etc.
            $growthData[] = $growthDataRaw[$dateString] ?? 0;
        }

        return view('dashboard', compact('totalUsers', 'activeUsers', 'newUsersToday', 'recentActivities', 'recentUsers', 'rolesStats', 'growthLabels', 'growthData'));
    }
}
