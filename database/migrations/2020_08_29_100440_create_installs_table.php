<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        Schema::create('installs', function (Blueprint $table) {
//            $table->id();
//            $table->unsignedBigInteger('shop_id')->nullable()->comment('ID from shops table');
//            $table->unsignedBigInteger('app_id')->nullable()->comment('ID from apps table');
//            $table->timestamp('install_date')->nullable();
//            $table->bigInteger('billing_plan_id')->nullable();
//            $table->string('recurring_charge_id')->nullable()->comment('from Shopify');
//            $table->boolean('active')->nullable();
//            $table->timestamp('trial_expires')->nullable()->comment('trial_days days after install_date');
//            $table->decimal('billing_plan_current_cap')->nullable()->comment('How much have we charged the merchant in usage fees so far this billing period?');
//            $table->decimal('transaction_fee_rate')->nullable()->comment('What is their current fee? Will usually be either 0.0075 or 0.0025');
//            $table->string('token')->nullable()->comment('access token from Shopify');
//            $table->string('shopify_charge_status')->nullable()->comment('Status of the charge from Shopify. i.e active');
//            $table->boolean('deleted')->nullable();
//            $table->timestamp('deleted_date')->nullable();
//            $table->timestamps();
//
//            $table->foreign('shop_id')->on('shops')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
//            $table->foreign('app_id')->on('apps')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('installs');
    }
}
