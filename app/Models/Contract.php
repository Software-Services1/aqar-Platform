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
        'neighborhood', 'contract_type', 'employee_id', 'representative_id',
        'created_by', 'start_date', 'end_date', 'approval_status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
        ];
    }

    public const TYPES = [
        'exclusive' => 'حصري',
        'brokerage' => 'وساطة',
        'marketing' => 'تسويق',
    ];

    public const STATUSES = [
        'pending'   => 'في انتظار الموافقة',
        'approved'  => 'تمت الموافقة',
        'finished'  => 'منتهي',
        'expired'   => 'انتهت المدة دون موافقة',
        'cancelled' => 'ملغي',
    ];

    /* ----------------------- العلاقات ----------------------- */

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function representative(): BelongsTo
    {
        return $this->belongsTo(Representative::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(AdLicense::class);
    }

    /* ----------------------- الحسابات ----------------------- */

    public function getDaysRemainingAttribute(): int
    {
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
            && $this->days_remaining >= 0
            && $this->days_remaining < $days;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->contract_type] ?? $this->contract_type;
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
        return match ($this->approval_status) {
            'cancelled' => 'cancelled',
            'expired'   => 'expired',
            'finished'  => 'finished',
            'pending'   => $this->days_remaining < 0 ? 'expired' : 'pending',
            default     => $this->days_remaining < 0
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

    public function scopeForEmployee(Builder $q, int $employeeId): Builder
    {
        return $q->where('employee_id', $employeeId);
    }

    public function scopeOfType(Builder $q, ?string $type): Builder
    {
        return blank($type) ? $q : $q->where('contract_type', $type);
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
