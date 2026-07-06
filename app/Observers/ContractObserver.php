<?php

namespace App\Observers;

use App\Models\Contract;
use App\Services\NotificationService;

class ContractObserver
{
    public function __construct(private NotificationService $notifications) {}

    /** عند إنشاء عقد جديد → إشعار + بريد للموظف المعني */
    public function created(Contract $contract): void
    {
        $this->notifications->contractCreated($contract);
    }
}
