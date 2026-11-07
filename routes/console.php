<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Nonaktifkan langganan yang sudah berakhir setiap hari (butuh scheduler: php artisan schedule:work / cron schedule:run).
Schedule::command('subscriptions:expire')->dailyAt('00:05');
