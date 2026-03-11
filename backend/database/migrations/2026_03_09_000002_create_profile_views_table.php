<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Profile views table — tracks who viewed which profile and when.
 *
 * DESIGN:
 *   - Records every visit (viewer_id can be NULL for anonymous visitors).
 *   - Unique constraint on (viewer_id, profile_id, DATE(viewed_at)) prevents
 *     the same logged-in user from inflating views with repeated visits on the same day.
 *     This is handled at the application level (check before insert).
 *   - Indexed by profile_id for fast "how many views does this profile have" queries.
 *   - Indexed by viewer_id for "profiles I've visited" lookups.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('viewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('viewed_at')->useCurrent();

            $table->index('profile_id');
            $table->index('viewer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_views');
    }
};
