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
        Schema::table('ss_plans', function (Blueprint $table) {
            $table->tinyInteger('store_credit')->default(0)->after('trial_days');
            $table->decimal('store_credit_amount')->nullable()->after('store_credit');
            $table->string('store_credit_frequency')->nullable()->after('store_credit_amount')->comment('first_order','all_orders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ss_plans', function (Blueprint $table) {
            $table->dropColumn('store_credit');
            $table->dropColumn('store_credit_amount');
            $table->dropColumn('store_credit_frequency');
        });
    }
};
