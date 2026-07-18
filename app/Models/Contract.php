<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_number', 'project_name', 'developer_name', 'developer_phone',
        'neighborhood', 'contract_type', 'transaction_type', 'responsible_name', 'responsible_phone', 'representative_id',
        'created_by', 'parent_id', 'external_company_id', 'start_date', 'end_date', 'approval_status', 'is_draft', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
            'is_draft'   => 'boolean',
        ];
    }

    public const TYPES = [
        'exclusive' => 'حصري',
        'brokerage' => 'وساطة',
        'marketing' => 'تسويق',
    ];

    public const TRANSACTION_TYPES = [
        'rent' => 'إيجار',
        'sale' => 'بيع',
    ];

    public const STATUSES = [
        'pending'   => 'في انتظار الموافقة',
        'approved'  => 'تمت الموافقة',
        'finished'  => 'منتهي',
        'expired'   => 'انتهت المدة دون موافقة',
        'cancelled' => 'ملغي',
    ];

    /* ----------------------- العلاقات ----------------------- */

    public function representative(): BelongsTo
    {
        return $this->belongsTo(Representative::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    /** الشركة الخارجية (للعقود الفرعية) */
    public function externalCompany(): BelongsTo
    {
        return $this->belongsTo(ExternalCompany::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(AdLicense::class);
    }

    /** الموظفون المصرّح لهم برؤية العقد */
    public function assignedEmployees(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'contract_employee');
    }

    /** العقد الأصل (إن كان هذا عقداً فرعياً) */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'parent_id');
    }

    /** العقود الفرعية المتفرّعة من هذا العقد */
    public function subContracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'parent_id');
    }

    /* ----------------------- الحسابات ----------------------- */

    public function getDaysRemainingAttribute(): ?int
    {
        if (! $this->end_date) {
            return null;
        }
        return (int) now()->startOfDay()->diffInDays($this->end_date->startOfDay(), false);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->approval_status === 'expired'
            || (! in_array($this->approval_status, ['cancelled', 'finished', 'pending'], true) && $this->days_remaining < 0);
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        $days = (int) Setting::get('alert_days', 7);

        return $this->approval_status === 'approved'
            && $this->days_remaining !== null
            && $this->days_remaining >= 0
            && $this->days_remaining < $days;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->contract_type] ?? $this->contract_type;
    }

    public function getTransactionLabelAttribute(): string
    {
        return self::TRANSACTION_TYPES[$this->transaction_type] ?? '—';
    }

    public function getIsSubcontractAttribute(): bool
    {
        return ! is_null($this->parent_id);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->approval_status] ?? $this->approval_status;
    }

    /**
     * حالة العرض المرئي:
     * cancelled > expired > finished > pending > expiring > active
     */
    public function getVisualStateAttribute(): string
    {
        if ($this->is_draft) {
            return 'draft';
        }
        $dr = $this->days_remaining;
        return match ($this->approval_status) {
            'cancelled' => 'cancelled',
            'expired'   => 'expired',
            'finished'  => 'finished',
            'pending'   => ($dr !== null && $dr < 0) ? 'expired' : 'pending',
            default     => ($dr !== null && $dr < 0)
                ? 'expired'
                : ($this->is_expiring_soon ? 'expiring' : 'active'),
        };
    }

    /* ----------------------- حالة الترخيص/النشر على مستوى العقد ----------------------- */

    /** أعلى حالة نشر بين تراخيص العقد: none | partial | full ; أو null إن لا ترخيص */
    public function getPublishSummaryAttribute(): ?string
    {
        if ($this->licenses->isEmpty()) {
            return null;
        }
        $states = $this->licenses->map->publish_state;
        if ($states->contains('none'))    return 'none';
        if ($states->contains('partial')) return 'partial';
        return 'full';
    }

    /* ----------------------- النطاقات ----------------------- */

    public function scopeApproved(Builder $q): Builder { return $q->where('approval_status', 'approved'); }
    public function scopePending(Builder $q): Builder { return $q->where('approval_status', 'pending'); }
    public function scopeDraft(Builder $q): Builder { return $q->where('is_draft', true); }

    /**
     * العقود التي يُسمح للموظف (غير المدير) برؤيتها:
     * - ما أنشأه بنفسه، أو
     * - عقود «تمت الموافقة/منتهية» فقط (تُخفى: بانتظار الموافقة، ملغي، انتهت دون موافقة)
     *   وهو مصرّح له برؤيتها (مُسنَد إليه/له ترخيص عليها/غير مُسنَدة لأحد).
     * المسودّات مستثناة دائماً.
     */
    public function scopeVisibleToEmployee(Builder $q, int $employeeId): Builder
    {
        return $q->where('is_draft', false)->where(function ($w) use ($employeeId) {
            $w->where('created_by', $employeeId)
              ->orWhere(function ($v) use ($employeeId) {
                  $v->whereIn('approval_status', ['approved', 'finished'])
                    ->where(function ($x) use ($employeeId) {
                        $x->whereHas('assignedEmployees', fn ($e) => $e->whereKey($employeeId))
                          ->orWhereHas('licenses', fn ($l) => $l->where('employee_id', $employeeId))
                          ->orWhereDoesntHave('assignedEmployees');
                    });
              });
        });
    }
    public function scopeCancelled(Builder $q): Builder { return $q->where('approval_status', 'cancelled'); }
    public function scopeFinished(Builder $q): Builder { return $q->where('approval_status', 'finished'); }

    public function scopeExpired(Builder $q): Builder
    {
        return $q->where(function ($x) {
            $x->where('approval_status', 'expired')
              ->orWhere(function ($y) {
                  $y->whereNotIn('approval_status', ['cancelled', 'finished'])
                    ->whereDate('end_date', '<', now()->toDateString());
              });
        });
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('approval_status', 'approved')
                 ->whereDate('end_date', '>=', now()->toDateString());
    }

    public function scopeExpiringSoon(Builder $q, ?int $days = null): Builder
    {
        $days ??= (int) Setting::get('alert_days', 7);
        return $q->whereDate('end_date', '>=', now()->toDateString())
                 ->whereDate('end_date', '<=', now()->addDays($days)->toDateString());
    }

    /** عقود بلا أي ترخيص */
    public function scopeWithoutLicense(Builder $q): Builder
    {
        return $q->doesntHave('licenses');
    }

    /** عقود لها ترخيص لكنه غير منشور بالكامل (لا منصات أو منصات ناقصة) */
    public function scopeNotFullyPublished(Builder $q, int $activeCount): Builder
    {
        return $q->has('licenses')
                 ->whereHas('licenses', fn ($l) => $l->where('platform_count', '<', max($activeCount, 1)));
    }

    /** عقود كل تراخيصها منشورة بالكامل */
    public function scopeFullyPublished(Builder $q, int $activeCount): Builder
    {
        return $q->has('licenses')
                 ->whereDoesntHave('licenses', fn ($l) => $l->where('platform_count', '<', max($activeCount, 1)));
    }

    public function scopeOfType(Builder $q, ?string $type): Builder
    {
        return blank($type) ? $q : $q->where('contract_type', $type);
    }

    public function scopeOfTransaction(Builder $q, ?string $t): Builder
    {
        return blank($t) ? $q : $q->where('transaction_type', $t);
    }

    public function scopeInNeighborhood(Builder $q, ?string $n): Builder
    {
        return blank($n) ? $q : $q->where('neighborhood', 'like', "%{$n}%");
    }

    public function scopeBetweenDates(Builder $q, ?string $from, ?string $to): Builder
    {
        if ($from) $q->whereDate('start_date', '>=', $from);
        if ($to)   $q->whereDate('start_date', '<=', $to);
        return $q;
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (blank($term)) return $q;
        return $q->where(function ($x) use ($term) {
            $x->where('project_name', 'like', "%{$term}%")
              ->orWhere('developer_name', 'like', "%{$term}%")
              ->orWhere('contract_number', 'like', "%{$term}%")
              ->orWhere('developer_phone', 'like', "%{$term}%");
        });
    }
}
