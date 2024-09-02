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
        Schema::table('ss_cancellation_reasons', function (Blueprint $table) {
            $table->string('reason',255)->nullable()->after('shop_id');
            $table->boolean('is_enabled')->default(false)->after('reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ss_cancellation_reasons', function (Blueprint $table) {
            $table->dropColumn('reason');
            $table->dropColumn('is_enabled');
        });
    }
};
