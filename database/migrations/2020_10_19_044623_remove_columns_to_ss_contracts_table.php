<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsToSsContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_contracts', function (Blueprint $table) {
            $table->dropColumn(['ts_created', 'ts_last_updated']);
            $table->char('currency_code', 3)->after('ship_phone')->nullable()->comment('e.g. USD, CAD, INR');
            $table->string('cc_brand', 100)->after('currency_code')->nullable()->comment('e.g. Visa, American Express');
            $table->integer('cc_expiryMonth')->after('cc_brand')->nullable()->comment('e.g. 12');
            $table->integer('cc_expiryYear')->after('cc_expiryMonth')->nullable()->comment('e.g. 2022');
            $table->integer('cc_firstDigits')->after('cc_expiryYear')->nullable();
            $table->integer('cc_lastDigits')->after('cc_firstDigits')->nullable();
            $table->integer('cc_maskedNumber')->after('cc_lastDigits')->nullable();
            $table->string('cc_name', 255)->after('cc_maskedNumber')->nullable()->comment('Name on credit card');
            $table->string('shipping_carrier', 255)->after('cc_name')->nullable();
            $table->string('shipping_code', 255)->after('shipping_carrier')->nullable();
            $table->string('shipping_description', 255)->after('shipping_code')->nullable();
            $table->string('shipping_presentmentTitle', 255)->after('shipping_description')->nullable();
            $table->string('shipping_title', 255)->after('shipping_presentmentTitle')->nullable();
            $table->string('lastPaymentStatus', 10)->after('shipping_title')->nullable()->comment('
FAILED or SUCCEEDED');
        });

        Schema::table('ss_contract_line_items', function (Blueprint $table) {
            $table->decimal('price_discounted')->after('price')->nullable()->comment('price after discounts');
            $table->string('discount_code', 100)->after('quantity')->nullable();
            $table->string('selling_plan_id', 100)->after('discount_code')->nullable();
            $table->string('selling_plan_name', 255)->after('selling_plan_id')->nullable();
            $table->string('sku', 255)->after('selling_plan_name')->nullable();
            $table->boolean('taxable')->after('sku')->default(0);
            $table->string('title', 255)->after('taxable')->nullable();
            $table->string('shopify_variant_image', 255)->after('title')->nullable();
            $table->string('shopify_variant_title', 255)->after('shopify_variant_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_contracts', function (Blueprint $table) {
            //
        });
    }
}
