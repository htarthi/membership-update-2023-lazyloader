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
        Schema::table('ss_store_credits', function (Blueprint $table) {
            $table->bigInteger('shop_id')->after('id');
            $table->bigInteger('shopify_customer_id')->nullable()->after('ss_customer_id')->comment('ID of the customer account on Shopify');
            $table->bigInteger('shopify_storecreditaccount_id')->nullable()->after('shopify_customer_id')->comment('ID of the StoreCreditAccount for this customer');
            $table->bigInteger('shopify_contract_id')->nullable()->after('shopify_storecreditaccount_id')->comment('ID of the contract');
            $table->decimal('amount')->nullable()->after('shopify_contract_id');
            $table->decimal('balance')->nullable()->after('amount')->comment('the current balance of the store credit account');
            $table->date('expiry_date')->nullable()->after('balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ss_store_credits', function (Blueprint $table) {
            $table->dropColumn('shop_id');
            $table->dropColumn('shopify_customer_id');
            $table->dropColumn('shopify_storecreditaccount_id');
            $table->dropColumn('shopify_contract_id');
            $table->dropColumn('amount');
            $table->dropColumn('balance');
            $table->dropColumn('expiry_date');
        });
    }
};
