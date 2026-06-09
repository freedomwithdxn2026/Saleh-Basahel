<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('leads:run-automation', function () {
    $result = app(\App\Services\LeadAutomationService::class)->run();
    $this->info(json_encode($result));
})->purpose('Send lead welcomes, daily guidance, follow-ups, reminders, and admin notifications');

Artisan::command('leads:backfill-communications', function () {
    $count = app(\App\Services\LeadCommunicationBackfillService::class)->run();
    $this->info("Backfilled {$count} communication records.");
})->purpose('Reconstruct known historical lead communications from existing CRM data');

Schedule::command('leads:run-automation')->everyFiveMinutes()->withoutOverlapping();
