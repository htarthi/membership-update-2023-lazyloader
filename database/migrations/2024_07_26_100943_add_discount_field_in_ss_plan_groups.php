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
        Schema::table('ss_plan_groups', function (Blueprint $table) {
            $table->boolean('discount_type')->default(false)->after('tag_order');
            $table->boolean('activate_product_discount')->default(false)->after('discount_type');
            $table->boolean('activate_shipping_discount')->default(false)->after('activate_product_discount');
            $table->float('shipping_discount_code')->nullable()->after('activate_shipping_discount');
            $table->string('active_shipping_dic',255)->nullable()->after('shipping_discount_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ss_plan_groups', function (Blueprint $table) {
            $table->dropColumn('discount_type');
            $table->dropColumn('activate_product_discount');
            $table->dropColumn('activate_shipping_discount');
            $table->dropColumn('shipping_discount_code');
            $table->dropColumn('active_shipping_dic');
        });
    }
};
