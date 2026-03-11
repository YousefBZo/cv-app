<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Add a unique `slug` column to the users table.
 *
 * WHY: Public CV URLs should use human-readable slugs (/u/yousef-bzo)
 *      instead of numeric IDs (/u/3). This is better for:
 *        - SEO (search engines prefer readable URLs)
 *        - Privacy (doesn't expose auto-increment IDs)
 *        - Shareability (looks professional on LinkedIn, email, etc.)
 *
 * The slug is auto-generated from the user's name on registration,
 * with a numeric suffix if duplicates exist (yousef-bzo, yousef-bzo-2).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        // Backfill slugs for existing users
        $users = DB::table('users')->orderBy('id')->get();
        foreach ($users as $user) {
            $baseSlug = Str::slug($user->name);
            if (! $baseSlug) {
                $baseSlug = 'user';
            }
            $slug = $baseSlug;
            $counter = 2;
            while (DB::table('users')->where('slug', $slug)->where('id', '!=', $user->id)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            DB::table('users')->where('id', $user->id)->update(['slug' => $slug]);
        }

        // Now make the column NOT NULL
        Schema::table('users', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
