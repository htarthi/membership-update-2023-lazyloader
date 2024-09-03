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
            $table->tinyInteger('theme_app_embed')->after('send_account_invites')->nullable()->default(null);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ss_settings', function (Blueprint $table) {
            $table->dropColumn('theme_app_embed');

        });
    }
};
