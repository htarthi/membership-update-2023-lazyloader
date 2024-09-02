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
        Schema::table('ss_languages', function (Blueprint $table) {
            // $table->text('portal_member_id')->nullable()->after('portal_title_subscriptions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ss_languages', function (Blueprint $table) {
            $table->dropColumn('portal_member_id');
        });
    }
};
