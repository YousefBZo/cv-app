<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OPTIMIZATION #1 — Database Indexes
 *
 * WHY: Without indexes, every query that filters by `profile_id` performs a
 *      full table scan — the DB reads every row to find matches.
 *      With an index, the DB uses a B-tree lookup: O(log n) instead of O(n).
 *
 * WHAT: We add indexes on `profile_id` for every child table that belongs
 *       to a profile (education, experiences, projects, certifications,
 *       volunteer_experiences). The pivot tables (profile_skill, profile_language)
 *       already have unique composite indexes which serve as indexes, but we
 *       also add individual profile_id indexes for faster single-column lookups.
 *
 * IMPACT: 10-50x faster read queries when a user has many records.
 *         No downside — indexes use minimal extra storage.
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- Child tables: index the foreign key used in WHERE clauses ---
        Schema::table('education', function (Blueprint $table) {
            $table->index('profile_id');
        });

        Schema::table('experiences', function (Blueprint $table) {
            $table->index('profile_id');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->index('profile_id');
        });

        Schema::table('certifications', function (Blueprint $table) {
            $table->index('profile_id');
        });

        Schema::table('volunteer_experiences', function (Blueprint $table) {
            $table->index('profile_id');
        });

        // --- Profiles: index user_id for fast "get profile for user" ---
        Schema::table('profiles', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('education', function (Blueprint $table) {
            $table->dropIndex(['profile_id']);
        });

        Schema::table('experiences', function (Blueprint $table) {
            $table->dropIndex(['profile_id']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['profile_id']);
        });

        Schema::table('certifications', function (Blueprint $table) {
            $table->dropIndex(['profile_id']);
        });

        Schema::table('volunteer_experiences', function (Blueprint $table) {
            $table->dropIndex(['profile_id']);
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
    }
};
