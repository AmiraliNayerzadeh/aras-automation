<?php

namespace Database\Seeders;

use App\Models\Settings\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'company_name', 'value' => 'Aras Automation', 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'default_locale', 'value' => 'en', 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'timezone', 'value' => 'Asia/Yerevan', 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'logo_path', 'value' => null, 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'attendance_send_event_logs', 'value' => '1', 'type' => 'boolean', 'group' => 'attendance', 'is_public' => false],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
