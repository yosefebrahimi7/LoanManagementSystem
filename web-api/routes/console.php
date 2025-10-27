<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule loan penalty processing daily
Schedule::command('loans:process-penalties')
    ->daily()
    ->at('00:00')
    ->withoutOverlapping()
    ->runInBackground();
