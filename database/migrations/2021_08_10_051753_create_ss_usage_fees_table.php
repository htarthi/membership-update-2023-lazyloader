<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsUsageFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_usage_fees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->nullable()->comment('ID from shops table');
            $table->bigInteger('charge_id')->nullable()->comment('The charge_id from the charges table for this charge');
            $table->decimal('charge_amount',8,2)->nullable()->comment('Amount in USD charged');
            $table->integer('member_count')->nullable()->comment('Total number of active + paused members when charge was created');
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
        Schema::dropIfExists('ss_usage_fees');
    }
}
