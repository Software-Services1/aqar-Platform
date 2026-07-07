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

        // 4) فجوات الترخيص/النشر
        $activeCount = Platform::active()->count();
        $missing = 0;
        $gaps = 0;

        // عقود معتمدة بلا أي ترخيص → تذكير منشئ العقد
        Contract::approved()->doesntHave('licenses')->with('creator')->chunk(100, function ($contracts) use ($notifications, &$missing) {
            foreach ($contracts as $contract) {
                $notifications->missingLicense($contract);
                $missing++;
            }
        });

        // تراخيص غير منشورة بالكامل → تنبيه منشئ الترخيص
        AdLicense::notFullyPublished($activeCount)->with(['employee', 'contract'])->chunk(100, function ($licenses) use ($notifications, $activeCount, &$gaps) {
            foreach ($licenses as $license) {
                $notifications->publishGap($license, $activeCount);
                $gaps++;
            }
        });

        $this->info("تنبيهات: {$missing} عقد بلا ترخيص، {$gaps} ترخيص غير منشور بالكامل.");

        return self::SUCCESS;
    }
}
