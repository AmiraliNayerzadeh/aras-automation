<?php

namespace App\Http\Controllers;

use App\Models\Organization\Company;
use App\Models\Organization\Department;
use App\Models\User;
use App\Support\DashboardWidgetRegistry;

class DashboardController extends Controller
{
    public function index(DashboardWidgetRegistry $widgets)
    {
        $birthdaysToday = User::query()
            ->whereNotNull('date_of_birth')
            ->whereMonth('date_of_birth', now()->month)
            ->whereDay('date_of_birth', now()->day)
            ->get();

        $stats = [
            'users' => User::count(),
            'companies' => Company::count(),
            'departments' => Department::count(),
            'active_users' => User::where('status', 'active')->count(),
        ];

        return view('dashboard', [
            'birthdaysToday' => $birthdaysToday,
            'actionItems' => $widgets->resolve(),
            'stats' => $stats,
        ]);
    }
}
