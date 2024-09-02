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
        Schema::table('ss_settings', function (Blueprint $table) {
            $table->integer('free_memberships')->nullable()->after('meta_questions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ss_settings', function (Blueprint $table) {
            $table->dropColumn('free_memberships');
        });
    }
};
