<?php

namespace App\Observers;

use App\Models\Contract;
use App\Services\NotificationService;

class ContractObserver
{
    public function __construct(private NotificationService $notifications) {}

    /** عند إنشاء عقد جديد → إشعار الموظفين لإنشاء تراخيصهم (يُستثنى العقد الفرعي) */
    public function created(Contract $contract): void
    {
        // العقد الفرعي (لشركة خارجية) لا يتطلّب ترخيصاً، فلا نُشعر الموظفين
        if ($contract->parent_id) {
            return;
        }

        $this->notifications->contractCreated($contract);
    }
}
