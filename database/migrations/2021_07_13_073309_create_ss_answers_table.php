<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ss_contract_id')->nullable()->comment('internal ss_contracts id');
            $table->string('question')->nullable();
            $table->string('answer')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ss_contract_id')->on('ss_contracts')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ss_answers');
    }
}
