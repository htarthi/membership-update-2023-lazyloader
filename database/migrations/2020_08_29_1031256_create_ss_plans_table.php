<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->nullable()->comment('ID from shops table');
            $table->unsignedBigInteger('user_id')->nullable()->comment('ID from users table');
            $table->unsignedBigInteger('ss_plan_group_id')->nullable()->comment('ID from ss_plan_groups table');
            $table->timestamp('ts_created')->nullable();
            $table->timestamp('ts_updated')->nullable();
            $table->string('name')->nullable();
            $table->string('description')->nullable()->comment('text which will be displayed to the customer on the storefront');
            $table->string('status')->nullable()->comment('active, deleted, paused');
            $table->integer('position')->nullable();
            $table->string('billing_interval')->nullable()->comment('DAY, MONTH, WEEK, YEAR');
            $table->integer('billing_interval_count')->nullable();
            $table->integer('billing_min_cycles')->nullable();
            $table->integer('billing_max_cycles')->nullable();
            $table->string('billing_anchors')->nullable();
            $table->string('delivery_intent')->nullable();
            $table->string('delivery_interval')->nullable()->comment('DAY, MONTH, WEEK, YEAR');
            $table->string('delivery_interval_count')->nullable()->comment('DAY, MONTH, WEEK, YEAR');
            $table->integer('delivery_cutoff')->nullable()->comment('Number of days before fixed order data to cut off ordres');
            $table->string('delivery_pre_cutoff_behaviour')->nullable();
            $table->integer('delivery_anchors')->nullable()->comment('fixed day of week, month, or year to create orders');
            $table->string('pricing_adjustment_type')->nullable()->comment('FIXED_AMOUNT, PERCENTAGE, PRICE')->default('%');
            $table->string('pricing_adjustment_value')->nullable()->comment('Decimal or Float depending on type');
            $table->string('pricing_after_cycle')->nullable()->comment('optional, only if pricing applies after X cycles');
            $table->string('pricing2_adjustment_type')->nullable()->default('%');
            $table->string('pricing2_adjustment_value')->nullable();
            $table->string('pricing2_after_cycle')->nullable();
            $table->timestamps();

            $table->foreign('shop_id')->on('shops')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign('user_id')->on('users')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
//            $table->foreign('ss_plan_group_id')->on('ss_plan_groups')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ss_plans');
    }
}
