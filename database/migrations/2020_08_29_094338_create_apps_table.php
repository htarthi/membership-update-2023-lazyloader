<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apps', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable();
            $table->string('api_key', 40)->nullable()->comment('From the partners page');
            $table->string('shared_secret', 40)->nullable()->comment('From the partners page');
            $table->integer('trial_days')->nullable()->comment('How many days does this app give for a trial?');
            $table->string('charge_return_url', 255)->nullable()->comment('The callback URL for when a charge is accepted');
            $table->string('uninstall_url', 255)->nullable();
            $table->string('app_url', 255)->nullable()->comment('The primary URL for the app');
            $table->string('api_version', 7)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('apps');
    }
}
