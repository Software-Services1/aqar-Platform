<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SettingsController extends Controller
{
    public function edit()
    {
        Gate::authorize('manage-settings');

        $settings = [
            'alert_days'          => Setting::get('alert_days', 7),
            'pending_expiry_days' => Setting::get('pending_expiry_days', 7),
            'color_active'        => Setting::get('color_active', '#16a34a'),
            'color_pending'       => Setting::get('color_pending', '#2563eb'),
            'color_expiring'      => Setting::get('color_expiring', '#dc2626'),
            'color_finished'      => Setting::get('color_finished', '#0d9488'),
            'color_expired'       => Setting::get('color_expired', '#6b7280'),
            'color_cancelled'     => Setting::get('color_cancelled', '#9333ea'),
        ];

        return view('settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        Gate::authorize('manage-settings');

        $data = $request->validate([
            'alert_days'          => ['required', 'integer', 'min:1', 'max:90'],
            'pending_expiry_days' => ['required', 'integer', 'min:1', 'max:90'],
            'color_active'        => ['required', 'string'],
            'color_pending'       => ['required', 'string'],
            'color_expiring'      => ['required', 'string'],
            'color_finished'      => ['required', 'string'],
            'color_expired'       => ['required', 'string'],
            'color_cancelled'     => ['required', 'string'],
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', 'تم حفظ الإعدادات.');
    }
}
