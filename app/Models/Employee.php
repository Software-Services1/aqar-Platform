<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * الموظف = المستخدم القابل لتسجيل الدخول.
 * الأدوار (manager / employee) تُدار عبر Spatie.
 */
class Employee extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password'  => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /** العقود التي أنشأها الموظف/المستخدم */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'created_by');
    }

    /** التراخيص التي أنشأها الموظف */
    public function licenses(): HasMany
    {
        return $this->hasMany(AdLicense::class);
    }

    /** العقود المصرّح له برؤيتها */
    public function assignedContracts(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Contract::class, 'contract_employee');
    }

    public function messagesSent(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function messagesReceived(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /** عدد الرسائل غير المقروءة */
    public function unreadMessagesCount(): int
    {
        return $this->messagesReceived()->whereNull('read_at')->count();
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(AppNotification::class)->latest();
    }

    /** الإشعارات غير المقروءة */
    public function unreadNotifications(): HasMany
    {
        return $this->hasMany(AppNotification::class)->where('is_read', false)->latest();
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }
}
