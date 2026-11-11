<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Month end (needs the hosting cron: * * * * * php artisan schedule:run). The "Générer les mensualités" button does the same.
Illuminate\Support\Facades\Schedule::command('sigma:close-month')->dailyAt('23:55')->when(fn () => now()->day === now()->daysInMonth);
