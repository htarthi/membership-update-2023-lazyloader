<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsWebhooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('topic')->nullable()->comment('name of the webhook (ie. customers/update)');
            $table->integer('user_id')->nullable()->comment('from the users table');
            $table->string('api_version')->nullable()->comment('ie. (2020-07)');
            $table->longText('body')->nullable()->comment('the body of the webhook');
            $table->string('status')->nullable()->comment('new, processed, error');
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
        Schema::dropIfExists('ss_webhooks');
    }
}
