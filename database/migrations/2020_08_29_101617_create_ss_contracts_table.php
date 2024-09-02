<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->nullable()->comment('ID from shops table');
            $table->unsignedBigInteger('user_id')->nullable()->comment('ID from users table');
            $table->bigInteger('shopify_contract_id')->nullable();
            $table->bigInteger('shopify_customer_id')->nullable()->comment('Shopify customer reference');
            $table->unsignedBigInteger('ss_customer_id')->nullable()->comment('ID from ss_customers table');
            $table->string('status')->nullable()->comment('ACTIVE, PAUSED, EXPIRED, CANCELED, FAILED');
            $table->string('error_state')->nullable()->comment('Additional info about the subscription: Billing Failed, Renewal Canceled');
            $table->timestamp('ts_created')->nullable();
            $table->timestamp('ts_last_updated')->nullable();
            $table->timestamp('ts_next_order')->nullable();
            $table->boolean('is_prepaid')->nullable()->comment('Is this a prepaid subscription? Is the billing interval and delivery interval different?');
            $table->string('last_billing_order_number')->nullable()->comment('The order number of the last successful billing interval');
            $table->boolean('prepaid_renew')->nullable()->comment('Should this subscription renew after the fulfillment orders are completed?');
            $table->string('billing_interval')->nullable()->comment('DAY, MONTH, WEEK, YEAR');
            $table->integer('billing_interval_count')->nullable();
            $table->integer('billing_min_cycles')->nullable();
            $table->integer('billing_max_cycles')->nullable();
            $table->string('billing_anchors')->nullable();
            $table->string('delivery_intent')->nullable();
            $table->string('delivery_interval')->nullable()->comment('DAY, MONTH, WEEK, YEAR');
            $table->integer('delivery_interval_count')->nullable();
            $table->integer('delivery_cutoff')->nullable();
            $table->string('delivery_pre_cutoff_behaviour')->nullable();
            $table->string('delivery_anchors')->nullable();
            $table->string('pricing_adjustment_type')->nullable()->default('%');
            $table->string('pricing_adjustment_value')->nullable();
            $table->string('pricing_after_cycle')->nullable();
            $table->string('ship_name')->nullable();
            $table->string('ship_address1')->nullable();
            $table->string('ship_address2')->nullable();
            $table->string('ship_city')->nullable();
            $table->string('ship_state')->nullable();
            $table->string('ship_zip')->nullable();
            $table->string('ship_country')->nullable();
            $table->string('ship_phone')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('shop_id')->on('shops')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign('user_id')->on('users')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign('ss_customer_id')->on('ss_customers')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ss_contracts');
    }
}
