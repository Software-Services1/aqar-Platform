<?php

namespace App\Console\Commands;

use App\Models\AdLicense;
use App\Models\Contract;
use App\Models\Platform;
use App\Models\Setting;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckContracts extends Command
{
    protected $signature = 'contracts:check';
    protected $description = 'تحويل العقود المتأخرة إلى منتهية وإرسال تنبيهات التراخيص القريبة من الانتهاء';

    public function handle(NotificationService $notifications): int
    {
        // 1) pending → expired إذا تجاوزت المهلة دون اعتماد
        $pendingDays = (int) Setting::get('pending_expiry_days', 7);
        $expiredCount = Contract::where('approval_status', 'pending')
            ->whereDate('start_date', '<=', now()->subDays($pendingDays)->toDateString())
            ->update(['approval_status' => 'expired']);
        $this->info("تم تحويل {$expiredCount} عقد معلّق إلى منتهٍ.");

        // 2) أي عقد (غير ملغي) تجاوز تاريخ نهايته → منتهٍ
        $byEndDate = Contract::whereIn('approval_status', ['pending', 'approved'])
            ->whereDate('end_date', '<', now()->toDateString())
            ->update(['approval_status' => 'expired']);
        $this->info("تم تحويل {$byEndDate} عقد متجاوز لتاريخ الانتهاء.");

        // 3) التراخيص القريبة من الانتهاء → إشعار لمنشئ كل ترخيص
        $alertDays = (int) Setting::get('alert_days', 7);
        $soon = AdLicense::expiringSoon($alertDays)->with(['employee', 'contract'])->get();

        foreach ($soon as $license) {
            $notifications->licenseExpiring($license);
        }
        $this->info("تم فحص {$soon->count()} ترخيص قريب من الانتهاء.");

        // 4) فجوات الترخيص/النشر: عقود معتمدة بلا ترخيص أو غير منشورة بالكامل
        $activeCount = Platform::active()->count();
        $gaps = 0;
        Contract::with(['licenses', 'employee'])->approved()->chunk(100, function ($contracts) use ($notifications, $activeCount, &$gaps) {
            foreach ($contracts as $contract) {
                $emp = $contract->employee;
                if (! $emp) {
                    continue;
                }
                $license = $contract->licenses->firstWhere('employee_id', $emp->id);
                if (! $license) {
                    $notifications->missingLicense($contract);
                    $gaps++;
                } elseif ($license->publish_state !== 'full') {
                    $notifications->publishGap($license, $activeCount);
                    $gaps++;
                }
            }
        });
        $this->info("تم إرسال {$gaps} تنبيه فجوة ترخيص/نشر.");

        return self::SUCCESS;
    }
}
