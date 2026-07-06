<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'alert_days'          => 7,
            'pending_expiry_days' => 7,
            // ألوان حالات العقود
            'color_active'    => '#16a34a',
            'color_pending'   => '#2563eb',
            'color_expiring'  => '#dc2626',
            'color_finished'  => '#0d9488',
            'color_expired'   => '#6b7280',
            'color_cancelled' => '#9333ea',
        ];

        foreach ($defaults as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
