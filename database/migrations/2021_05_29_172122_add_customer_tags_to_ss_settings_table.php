<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerTagsToSsSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_settings', function (Blueprint $table) {
            $table->string('tag_customer', 255)->default('Active Subscriber');
            $table->string('tag_order', 255)->default('Contains Subscription');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_settings', function (Blueprint $table) {
            //
        });
    }
}
