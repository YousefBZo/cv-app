<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes for sorting, as CVService always orders by these dates
        Schema::table('education', function (Blueprint $table) {
            $table->index('start_date');
        });

        Schema::table('experiences', function (Blueprint $table) {
            $table->index('start_date');
        });

        Schema::table('volunteer_experiences', function (Blueprint $table) {
            $table->index('start_date');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->index('start_date');
        });

        Schema::table('certifications', function (Blueprint $table) {
            $table->index('issue_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education', function (Blueprint $table) {
            $table->dropIndex(['start_date']);
        });

        Schema::table('experiences', function (Blueprint $table) {
            $table->dropIndex(['start_date']);
        });

        Schema::table('volunteer_experiences', function (Blueprint $table) {
            $table->dropIndex(['start_date']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['start_date']);
        });

        Schema::table('certifications', function (Blueprint $table) {
            $table->dropIndex(['issue_date']);
        });
    }
};
