<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Settings\Setting;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class SettingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings.manage'),
        ];
    }

    public function index(): View
    {
        return view('admin.settings.index', [
            'settings' => Setting::orderBy('group')->orderBy('key')->get(),
        ]);
    }

    public function update(Request $request, Setting $setting, SettingsService $settingsService): RedirectResponse
    {
        $data = $request->validate([
            'value' => ['nullable', 'string'],
        ]);

        $settingsService->set($setting->key, $data['value'] ?? null, $setting->type, $setting->group);

        return redirect()->route('admin.settings.index')->with('status', 'settings-updated');
    }
}
