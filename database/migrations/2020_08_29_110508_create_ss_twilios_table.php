<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsTwiliosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_twilios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->nullable()->comment('ID from shops table');
            $table->string('message_type')->nullable()->comment('sent, received');
            $table->string('message_from')->nullable()->comment('phone number');
            $table->string('message_to')->nullable()->comment('phone number');
            $table->longText('message_body')->nullable();
            $table->timestamps();

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
        Schema::dropIfExists('ss_twilios');
    }
}
