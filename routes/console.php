<?php

use Illuminate\Support\Facades\Schedule;

// فحص يومي للعقود والتراخيص
Schedule::command('contracts:check')->dailyAt('08:00');
