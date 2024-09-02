<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsStoreCreditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_store_credits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ss_customer_id')->nullable();
            $table->dateTime('transaction_date')->nullable();
            $table->string('description')->nullable();
            $table->decimal('beginning_balance',8,2)->nullable();
            $table->decimal('ending_balance',8,2)->nullable();
            $table->bigInteger('gift_card_id')->nullable();
            $table->string('gift_card_code_ending',4)->nullable();

            $table->foreign('ss_customer_id')->references('id')->on('ss_customers');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ss_store_credits');
    }
}
