<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add ip_address to profile_views for anonymous visitor de-duplication.
 *
 * WHY: Without this, every anonymous page reload creates a new view row,
 *      inflating view counts. With IP-based de-dup, we limit anonymous
 *      visitors to 1 view per (IP, profile) per day.
 *
 * PRIVACY: IP is stored only for anonymous views (viewer_id IS NULL).
 *          Authenticated users are de-duplicated by user ID — their IP
 *          is never stored. IPs are used solely for counting, not tracking.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_views', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('viewer_id');

            // Composite index for fast anonymous de-dup queries
            $table->index(['profile_id', 'ip_address', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('profile_views', function (Blueprint $table) {
            $table->dropIndex(['profile_id', 'ip_address', 'viewed_at']);
            $table->dropColumn('ip_address');
        });
    }
};
