<?php

namespace App\Providers;

use App\Models\Contract;
use App\Observers\ContractObserver;
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
        // ربط الـ Observer (إنشاء إشعارات + بريد عند إنشاء عقد)
        Contract::observe(ContractObserver::class);

        // الصلاحيات ديناميكية عبر Spatie (تُدار من تبويبة الأدوار).
        // المدير يملك صلاحية الوصول الكامل (مشرف أعلى).
        Gate::before(fn ($user) => $user->isManager() ? true : null);
    }
}
