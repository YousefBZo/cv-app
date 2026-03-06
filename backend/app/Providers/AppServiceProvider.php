<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * OPTIMIZATION #3 — Prevent N+1 Queries Globally
         *
         * WHY: The N+1 problem happens when code accesses a relation that
         *      wasn't eager-loaded. Laravel silently fires an extra query
         *      for each item in a collection. Example:
         *        foreach ($profile->experiences as $exp) {
         *            $exp->profile->user; // ← extra query per experience!
         *        }
         *
         * HOW: preventLazyLoading() is enabled in non-production, and
         *      handleLazyLoadingViolationUsing() LOGS violations as warnings
         *      instead of throwing exceptions. This is critical because
         *      patterns like $request->user()->profile (used by every
         *      service in this app) are technically lazy loads, and throwing
         *      would break all endpoints in dev.
         *
         *      The logged warnings let you spot true N+1 problems (inside
         *      loops) without crashing single-relation access patterns.
         *
         * SAFETY: Only enabled in non-production so it never affects the
         *         live site. In production, lazy loading is silently allowed.
         */
        Model::preventLazyLoading(! app()->isProduction());

        Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
            Log::warning("Lazy loading detected: {$model}::{$relation}");
        });

        /**
         * BONUS: Prevent silently discarding attributes not in $fillable.
         * Throws an exception if you try to mass-assign a non-fillable field.
         * Catches bugs like typos in field names immediately.
         */
        Model::preventSilentlyDiscardingAttributes(! app()->isProduction());

        /**
         * OPTIMIZATION #12 — Rate Limiting Definition
         *
         * WHY: This defines the 'api' rate limiter referenced by the
         *      'throttle:api' middleware in routes/api.php.
         *
         * HOW: For authenticated users, limit by user ID (so each user
         *      gets their own 60 req/min bucket). For unauthenticated
         *      requests, limit by IP address.
         *
         * The `by()` method creates a unique key per user/IP so one
         * user hitting the limit doesn't affect others.
         */
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(
                $request->user()?->id ?: $request->ip()
            );
        });
    }
}
