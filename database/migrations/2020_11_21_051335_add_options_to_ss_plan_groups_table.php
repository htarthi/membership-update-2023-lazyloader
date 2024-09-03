<?php

// use Doctrine\DBAL\Types\StringType;
// use Doctrine\DBAL\Types\Type;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOptionsToSsPlanGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // if (!Type::hasType('char')) {
        //     Type::addType('char', StringType::class);
        // }

        Schema::table('ss_orders', function (Blueprint $table) {
            $table->dropColumn('ts_created');
            $table->dropColumn('ts_updated');

            $table->decimal('tx_fee_percentage', 8, 4)->change();
            $table->decimal('tx_fee_amount', 8, 4)->change();
            $table->decimal('conversion_rate',8, 6)->change();
            $table->char('order_currency', 3)->change();
            $table->string('currency_symbol', 3)->change();
        });

        Schema::table('ss_plans', function (Blueprint $table) {
            $table->dropColumn('ts_created');
            $table->dropColumn('ts_updated');

            $table->dropColumn('billing_anchors');

            $table->integer('billing_anchor_day')->nullable()->after('billing_max_cycles');
            $table->integer('billing_anchor_month')->nullable()->after('billing_anchor_day');
            $table->string('billing_anchor_type', 8)->nullable()->after('billing_anchor_month');

            $table->dropColumn('delivery_anchors');

            $table->integer('delivery_anchor_day')->nullable()->after('delivery_pre_cutoff_behaviour');
            $table->integer('delivery_anchor_month')->nullable()->after('delivery_anchor_day');
            $table->string('delivery_anchor_type', 8)->nullable()->after('delivery_anchor_month');
            $table->decimal('pricing_adjustment_value', 8, 2)->change();
        });

        Schema::table('ss_plan_groups', function (Blueprint $table) {
            $table->dropColumn('ts_created');
            $table->dropColumn('label');
            $table->string('options')->after('description')->nullable();
        });

        Schema::table('ss_settings', function (Blueprint $table) {
            $table->decimal('transaction_fee', 8, 4)->change();
        });

        Schema::table('ss_customers', function (Blueprint $table) {
            $table->dropColumn('ts_created');
            $table->char('total_spend_currency', 3)->change();
            $table->string('currency_symbol', 3)->change();
            $table->decimal('avg_order_value', 8,2)->default(0)->change();
        });

        Schema::table('ss_contracts', function (Blueprint $table) {
            $table->renameColumn('ts_next_order', 'next_order_date');
            $table->timestamp('next_processing_date')->after('ts_next_order')->nullable();
            $table->bigInteger('origin_order_id')->after('ss_customer_id')->nullable();

            $table->dropColumn('billing_anchors');

            $table->integer('billing_anchor_day')->nullable()->after('billing_max_cycles');
            $table->integer('billing_anchor_month')->nullable()->after('billing_anchor_day');
            $table->string('billing_anchor_type', 8)->nullable()->after('billing_anchor_month');

            $table->dropColumn('delivery_anchors');

            $table->integer('delivery_anchor_day')->nullable()->after('delivery_pre_cutoff_behaviour');
            $table->integer('delivery_anchor_month')->nullable()->after('delivery_anchor_day');
            $table->string('delivery_anchor_type', 8)->nullable()->after('delivery_anchor_month');

            $table->string('ship_company', 255)->nullable()->after('pricing_after_cycle');
            $table->string('ship_firstName', 255)->nullable()->after('ship_company');
            $table->string('ship_lastName', 255)->nullable()->after('ship_firstName');
            $table->renameColumn('ship_state', 'ship_province');
            $table->char('ship_provinceCode', 2)->nullable()->after('ship_lastName');
            $table->decimal('delivery_price', 8,2)->nullable()->after('delivery_pre_cutoff_behaviour');

            $table->dropColumn('pricing_adjustment_type');
            $table->dropColumn('pricing_adjustment_value');
            $table->dropColumn('pricing_after_cycle');

            $table->bigInteger('cc_id')->nullable()->after('currency_code');
            $table->boolean('is_multicurrency')->default(0)->after('cc_name');
            $table->string('status_billing', 50)->nullable()->after('lastPaymentStatus');
            $table->integer('failed_payment_count')->nullable()->after('status_billing');
        });

        Schema::table('ss_contract_line_items', function (Blueprint $table) {
            $table->boolean('requiresShipping')->default(0)->after('shopify_variant_title');
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('ts_created');
            $table->dropColumn('ts_lastupdated');
        });

        Schema::table('ss_events', function (Blueprint $table) {
            $table->dropColumn('ts_created');
        });

        Schema::table('ss_webhooks', function (Blueprint $table) {
            $table->unsignedBigInteger('shop_id')->after('user_id')->nullable()->comment('ID from shops table');

            $table->foreign('shop_id')->on('shops')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });

//        Schema::table('ss_deleted_products', function (Blueprint $table) {
//            $table->dropColumn('ts_created');
//        });

        Schema::table('ss_activity_logs', function (Blueprint $table) {
            $table->dropColumn('ts_created');
        });

        Schema::create('ss_billing_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->nullable()->comment('ID from shops table');
            $table->bigInteger('shopify_id')->nullable();
            $table->string('status', 100)->nullable();
            $table->dateTime('completedAt')->nullable();
            $table->string('errorMessage')->nullable();
            $table->string('idempotencyKey')->nullable();
            $table->string('nextActionUrl')->nullable();
            $table->bigInteger('shopify_contract_id')->nullable();
            $table->bigInteger('shopify_order_id')->nullable();
            $table->timestamps();

            $table->foreign('shop_id')->on('shops')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
        Schema::dropIfExists('ss_failed_payments');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_plan_groups', function (Blueprint $table) {
            //
        });
    }
}
