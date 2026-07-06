<?php

namespace App\Services;

use App\Mail\ExpiringLicenseMail;
use App\Mail\NewContractMail;
use App\Models\AdLicense;
use App\Models\AppNotification;
use App\Models\Contract;
use App\Models\Employee;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * عند إنشاء عقد جديد → إشعار «عقد جديد» لكل الموظفين النشطين،
     * ليقوم كل موظف بإنشاء ترخيصه الخاص لهذا العقد.
     */
    public function contractCreated(Contract $contract): void
    {
        $employees = Employee::where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->where('name', 'employee'))
            ->get();

        foreach ($employees as $employee) {
            AppNotification::create([
                'employee_id'     => $employee->id,
                'type'            => 'new_contract',
                'message'         => "عقد جديد بانتظار إنشاء ترخيصك: «{$contract->project_name}» (رقم {$contract->contract_number}).",
                'notifiable_type' => Contract::class,
                'notifiable_id'   => $contract->id,
            ]);

            if ($employee->email) {
                Mail::to($employee->email)->queue(new NewContractMail($contract));
            }
        }
    }

    /**
     * ترخيص يقترب من الانتهاء → إشعار «expiring_license» لمنشئ الترخيص + بريد.
     * يمنع التكرار خلال 24 ساعة.
     */
    public function licenseExpiring(AdLicense $license): void
    {
        $employee = $license->employee;
        if (! $employee) {
            return;
        }

        $alreadySent = AppNotification::where('employee_id', $employee->id)
            ->where('type', 'expiring_license')
            ->where('notifiable_type', AdLicense::class)
            ->where('notifiable_id', $license->id)
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if ($alreadySent) {
            return;
        }

        $days = max((int) $license->days_remaining, 0);
        $project = $license->contract?->project_name ?? '';

        AppNotification::create([
            'employee_id'     => $employee->id,
            'type'            => 'expiring_license',
            'message'         => "ترخيصك رقم {$license->license_number} لعقد «{$project}» سينتهي خلال {$days} يوم/أيام.",
            'notifiable_type' => AdLicense::class,
            'notifiable_id'   => $license->id,
        ]);

        if ($employee->email) {
            Mail::to($employee->email)->queue(new ExpiringLicenseMail($license));
        }
    }

    /** عقد بلا ترخيص → تذكير الموظف المسؤول (مرة كل 24 ساعة) */
    public function missingLicense(Contract $contract): void
    {
        $employee = $contract->employee;
        if (! $employee) {
            return;
        }

        if ($this->recentlySent($employee->id, 'missing_license', Contract::class, $contract->id)) {
            return;
        }

        AppNotification::create([
            'employee_id'     => $employee->id,
            'type'            => 'missing_license',
            'message'         => "العقد «{$contract->project_name}» (رقم {$contract->contract_number}) لا يوجد له ترخيص بعد.",
            'notifiable_type' => Contract::class,
            'notifiable_id'   => $contract->id,
        ]);
    }

    /** ترخيص غير منشور بالكامل → تنبيه منشئه (مرة كل 24 ساعة) */
    public function publishGap(AdLicense $license, int $activeCount): void
    {
        $employee = $license->employee;
        if (! $employee) {
            return;
        }

        if ($this->recentlySent($employee->id, 'publish_gap', AdLicense::class, $license->id)) {
            return;
        }

        $count   = $license->published_count;
        $project = $license->contract?->project_name ?? '';
        $message = $count === 0
            ? "ترخيصك رقم {$license->license_number} لعقد «{$project}» لم يُنشر على أي منصة."
            : "ترخيصك رقم {$license->license_number} لعقد «{$project}» منشور على {$count} من {$activeCount} منصات فقط.";

        AppNotification::create([
            'employee_id'     => $employee->id,
            'type'            => 'publish_gap',
            'message'         => $message,
            'notifiable_type' => AdLicense::class,
            'notifiable_id'   => $license->id,
        ]);
    }

    private function recentlySent(int $employeeId, string $type, string $notifiableType, int $notifiableId): bool
    {
        return AppNotification::where('employee_id', $employeeId)
            ->where('type', $type)
            ->where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $notifiableId)
            ->where('created_at', '>=', now()->subDay())
            ->exists();
    }
}
