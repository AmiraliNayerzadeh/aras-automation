<?php

namespace App\Http\Controllers;

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

        return view('dashboard', [
            'birthdaysToday' => $birthdaysToday,
            'actionItems' => $widgets->resolve(),
        ]);
    }
}
