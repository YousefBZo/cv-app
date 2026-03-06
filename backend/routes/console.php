<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Laravel\Sanctum\PersonalAccessToken;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * OPTIMIZATION #13 — Sanctum Token Pruning
 *
 * WHY: Every time a user logs in, Sanctum creates a row in the
 *      `personal_access_tokens` table. When users logout, the current
 *      token is deleted — but if they close the browser without logging
 *      out, the token stays forever. Over time, this table grows to
 *      thousands of stale rows.
 *
 * IMPACT ON PERFORMANCE:
 *      - Every authenticated request queries this table to validate the
 *        token: SELECT * FROM personal_access_tokens WHERE token = ?
 *      - More rows = slower lookups (even with an index, more data to scan)
 *      - More disk space wasted
 *
 * HOW: Schedule a daily job that deletes tokens older than 30 days.
 *      Users who haven't logged in for 30 days will need to log in again.
 *
 * TO ACTIVATE: Run `php artisan schedule:work` in development, or add
 *              a cron entry in production:
 *              * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
 */
Schedule::call(function () {
    PersonalAccessToken::where('created_at', '<', now()->subDays(30))->delete();
})->daily()->description('Prune expired Sanctum tokens older than 30 days');
