<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // الصلاحيات ديناميكية عبر Spatie (تُدار من تبويبة الأدوار).
        // المدير يملك صلاحية الوصول الكامل (مشرف أعلى).
        Gate::before(fn ($user) => $user->isManager() ? true : null);
    }
}
