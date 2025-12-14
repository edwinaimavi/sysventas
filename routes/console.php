<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;




Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reminders:run')
    ->everyMinute()
    ->timezone('America/Lima')
    ->withoutOverlapping();

Schedule::command('reminders:generate-from-loans --due_before_days=5')
    /* ->dailyAt('07:00') */
    ->everyMinute()
    ->withoutOverlapping()
    ->timezone('America/Lima');
