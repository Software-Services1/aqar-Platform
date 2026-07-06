<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = ['عقار', 'بيوت', 'حراج', 'X (تويتر)', 'إنستجرام', 'سناب شات', 'الموقع الرسمي'];

        foreach ($platforms as $name) {
            Platform::firstOrCreate(['name' => $name]);
        }
    }
}
