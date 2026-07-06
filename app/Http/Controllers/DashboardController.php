<?php

namespace App\Http\Controllers;

use App\Models\AdLicense;
use App\Models\Contract;
use App\Models\Platform;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user      = auth()->user();
        $isManager = $user->isManager();
        $activeCount = Platform::active()->count();

        $contracts = fn () => $isManager
            ? Contract::query()
            : Contract::query()->where('employee_id', $user->id);

        $licenses = fn () => $isManager
            ? AdLicense::query()
            : AdLicense::query()->where('employee_id', $user->id);

        // عدّ حالات العقود ديناميكياً
        $statusCounts = [];
        foreach (array_keys(Contract::STATUSES) as $key) {
            $statusCounts[$key] = (clone $contracts())->where('approval_status', $key)->count();
        }

        $stats = [
            'contracts_total'   => $contracts()->count(),
            'approved'          => $statusCounts['approved'],
            'pending'           => $statusCounts['pending'],
            'finished'          => $statusCounts['finished'],
            'expired'           => $statusCounts['expired'],
            'cancelled'         => $statusCounts['cancelled'],
            'expiring_soon'     => $contracts()->active()->expiringSoon()->count(),
            'without_license'   => (clone $contracts())->withoutLicense()->count(),
            'unpublished'       => (clone $contracts())->notFullyPublished($activeCount)->count(),
            'licenses_total'    => $licenses()->count(),
            'licenses_expiring' => $licenses()->expiringSoon()->count(),
            'platforms'         => $activeCount,
        ];

        $statusChart = [
            'labels' => array_values(Contract::STATUSES),
            'data'   => array_values($statusCounts),
        ];

        // العقود حسب الشهر (آخر 6 أشهر)
        $monthly = $contracts()
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get()
            ->groupBy(fn ($c) => $c->created_at->format('Y-m'))
            ->map->count();

        $monthlyChart = ['labels' => [], 'data' => []];
        for ($i = 5; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $monthlyChart['labels'][] = now()->subMonths($i)->translatedFormat('M Y');
            $monthlyChart['data'][]   = $monthly[$key] ?? 0;
        }

        // استخدام المنصات (من بنية [{name,url}])
        $usage = [];
        foreach ($licenses()->whereNotNull('platforms')->pluck('platforms') as $list) {
            foreach ((array) $list as $entry) {
                $name = is_array($entry) ? ($entry['name'] ?? null) : $entry;
                if ($name) {
                    $usage[$name] = ($usage[$name] ?? 0) + 1;
                }
            }
        }
        arsort($usage);
        $platformChart = ['labels' => array_keys($usage), 'data' => array_values($usage)];

        $notifications = $user->unreadNotifications()->take(8)->get();

        return view('dashboard.index', compact(
            'stats', 'statusChart', 'monthlyChart', 'platformChart', 'notifications', 'isManager'
        ));
    }
}
