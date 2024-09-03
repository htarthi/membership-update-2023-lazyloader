<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->nullable()->comment('ID from shops table');
            $table->integer('billing_plan_id')->nullable()->comment('The billing plan that the merchant is subscribing to');
            $table->decimal('transaction_fee')->nullable()->comment('0.0075 or 0.0025');
            $table->integer('dunning_retries')->nullable();
            $table->integer('dunning_daysbetween')->nullable()->default(1);
            $table->string('dunning_failedaction')->nullable()->comment('CANCEL, SKIP, PAUSE');
            $table->boolean('dunning_email_enabled')->nullable()->default(1);
            $table->string('twilio_status')->nullable()->comment('ACTIVE, SETUP, OFF');
            $table->string('twilio_number')->nullable()->comment('each merchant can have one toll-free number');
            $table->boolean('sendgrid_enabled')->nullable();
            $table->string('sendgrid_api_key')->nullable();
            $table->string('email_from_name')->nullable();
            $table->string('email_from_email')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('shop_id')->on('shops')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ss_settings');
    }
}
