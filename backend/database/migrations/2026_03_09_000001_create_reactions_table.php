<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reactions table — stores user reactions (like, love) on profiles.
 *
 * DESIGN:
 *   - A user can have at most ONE active reaction per profile (enforced by unique constraint).
 *   - Changing reaction type = UPDATE, not INSERT.
 *   - Removing reaction = DELETE row.
 *   - Composite unique on (user_id, profile_id) prevents duplicates.
 *   - Indexed by profile_id for fast "get reactions for this profile" queries.
 *   - Indexed by user_id for fast "get my reactions" lookups.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // 'like', 'love'
            $table->timestamps();

            $table->unique(['user_id', 'profile_id']);
            $table->index('profile_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
