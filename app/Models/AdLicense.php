<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdLicense extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id', 'employee_id', 'license_number', 'issue_date',
        'expiry_date', 'platforms', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'issue_date'  => 'date',
            'expiry_date' => 'date',
            'platforms'   => 'array', // [{ name, url }]
        ];
    }

    public const STATUSES = [
        'pending'             => 'قيد الإنجاز',
        'created_unpublished' => 'تم الإنشاء ولم يتم النشر',
        'complete'            => 'مكتمل',
    ];

    /* مزامنة عدد المنصات المنشور عليها (لفلاتر حالة النشر) */
    protected static function booted(): void
    {
        static::saving(function (AdLicense $license) {
            $license->platform_count = is_array($license->platforms) ? count($license->platforms) : 0;
        });
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /* ----------------------- المنصات والنشر ----------------------- */

    /** أسماء المنصات المنشور عليها فقط */
    public function getPlatformNamesAttribute(): array
    {
        return collect($this->platforms ?? [])->pluck('name')->filter()->values()->all();
    }

    public function getPublishedCountAttribute(): int
    {
        return count($this->platforms ?? []);
    }

    /** أسماء المنصات النشطة — مُخزّنة مؤقتاً خلال الطلب لتفادي استعلامات N+1 */
    protected static ?array $activeNamesMemo = null;

    public static function activePlatformNames(): array
    {
        return static::$activeNamesMemo ??= Platform::active()->pluck('name')->all();
    }

    public static function clearActivePlatformNamesCache(): void
    {
        static::$activeNamesMemo = null;
    }

    public function getIsFullyPublishedAttribute(): bool
    {
        $active = static::activePlatformNames();
        if (empty($active)) {
            return $this->published_count > 0;
        }
        return empty(array_diff($active, $this->platform_names));
    }

    /** none | partial | full */
    public function getPublishStateAttribute(): string
    {
        if ($this->published_count === 0) {
            return 'none';
        }
        return $this->is_fully_published ? 'full' : 'partial';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (! $this->expiry_date) {
            return null;
        }
        return (int) now()->startOfDay()->diffInDays($this->expiry_date->startOfDay(), false);
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        $days = (int) Setting::get('alert_days', 7);
        return $this->days_remaining !== null
            && $this->days_remaining >= 0
            && $this->days_remaining < $days;
    }

    /* ----------------------- النطاقات ----------------------- */

    public function scopeExpiringSoon(Builder $q, ?int $days = null): Builder
    {
        $days ??= (int) Setting::get('alert_days', 7);
        return $q->whereNotNull('expiry_date')
                 ->whereDate('expiry_date', '>=', now()->toDateString())
                 ->whereDate('expiry_date', '<=', now()->addDays($days)->toDateString());
    }

    /** تراخيص غير منشورة بالكامل (تعتمد عدد المنصات النشطة) */
    public function scopeNotFullyPublished(Builder $q, int $activeCount): Builder
    {
        return $q->where('platform_count', '<', max($activeCount, 1));
    }

    public function scopeBetweenIssueDates(Builder $q, ?string $from, ?string $to): Builder
    {
        if ($from) $q->whereDate('issue_date', '>=', $from);
        if ($to)   $q->whereDate('issue_date', '<=', $to);
        return $q;
    }
}
