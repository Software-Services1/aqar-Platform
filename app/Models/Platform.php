<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Platform extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (Platform $platform) {
            $platform->slug ??= Str::slug($platform->name);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
