<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'body', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'receiver_id');
    }

    /** رسائل محادثة بين مستخدمين اثنين */
    public function scopeBetween(Builder $q, int $a, int $b): Builder
    {
        return $q->where(function ($x) use ($a, $b) {
            $x->where(fn ($y) => $y->where('sender_id', $a)->where('receiver_id', $b))
              ->orWhere(fn ($y) => $y->where('sender_id', $b)->where('receiver_id', $a));
        });
    }
}
