<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

if (config('app.enable_auto_surveys')) {
    Schedule::command('surveys:send-queued-auto --limit=50')
        ->everyFiveMinutes();
}