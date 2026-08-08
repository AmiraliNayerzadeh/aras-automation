<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Settings\Setting;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings.manage'),
        ];
    }

    public function index(SettingsService $settingsService): View
    {
        return view('admin.settings.index', [
            'settings' => Setting::orderBy('group')->orderBy('key')->get(),
            'themeColors' => config('theme.colors'),
            'currentColor' => $settingsService->get('primary_color', config('theme.default_color')),
        ]);
    }

    public function updateColor(Request $request, SettingsService $settingsService): RedirectResponse
    {
        $data = $request->validate([
            'primary_color' => ['required', Rule::in(array_keys(config('theme.colors')))],
        ]);

        $settingsService->set('primary_color', $data['primary_color'], 'string', 'appearance');

        return redirect()->route('admin.settings.index')->with('status', 'settings-updated');
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
