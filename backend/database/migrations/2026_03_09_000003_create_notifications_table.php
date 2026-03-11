<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notifications table — stores in-app notifications for users.
 *
 * DESIGN:
 *   - `type` indicates the kind: 'reaction' | 'profile_view'
 *   - `data` is a JSON blob carrying contextual information so the
 *     frontend can render rich notifications without extra API calls.
 *     Examples:
 *       reaction  → { "reactor_name": "Ali", "reactor_slug": "ali-123", "reaction_type": "like", "profile_id": 7 }
 *       view      → { "viewer_name": "Sara", "viewer_slug": "sara-456", "profile_id": 7 }
 *   - `read_at` is NULL until the user marks it read.
 *   - Indexed for the most common query: "give me this user's unread notifications, newest first".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);            // 'reaction' | 'profile_view'
            $table->json('data');                   // contextual payload
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);  // unread-first listing
            $table->index('created_at');            // newest-first ordering
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
