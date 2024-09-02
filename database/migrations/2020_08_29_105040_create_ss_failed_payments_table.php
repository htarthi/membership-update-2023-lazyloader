<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsFailedPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_failed_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->nullable()->comment('ID from shops table');
            $table->unsignedBigInteger('user_id')->nullable()->comment('ID from users table');
            $table->unsignedBigInteger('ss_contract_id')->nullable()->comment('internal contract id');
            $table->unsignedBigInteger('ss_customer_id')->nullable()->comment('internal customer id');
            $table->integer('billing_attempt_id')->nullable();
            $table->timestamp('next_attempt_date')->nullable();
            $table->integer('failure_count')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('shop_id')->on('shops')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign('user_id')->on('users')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign('ss_contract_id')->on('ss_contracts')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
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
        Schema::dropIfExists('ss_failed_payments');
    }
}
