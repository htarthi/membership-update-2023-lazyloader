<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToSsActivityLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_activity_logs', function (Blueprint $table) {
             $table->softDeletes();
        });
        Schema::table('ss_billing_attempts', function (Blueprint $table) {
             $table->softDeletes();
        });
        Schema::table('ss_languages', function (Blueprint $table) {
             $table->softDeletes();
        });
        Schema::table('ss_metrics', function (Blueprint $table) {
             $table->softDeletes();
        });
        Schema::table('ss_migrations', function (Blueprint $table) {
             $table->softDeletes();
        });
        Schema::table('ss_plans', function (Blueprint $table) {
             $table->softDeletes();
        });
        Schema::table('ss_pos_discounts', function (Blueprint $table) {
             $table->softDeletes();
        });
        Schema::table('ss_shipping_profiles', function (Blueprint $table) {
             $table->softDeletes();
        });
        Schema::table('ss_shipping_zones', function (Blueprint $table) {
             $table->softDeletes();
        });
        Schema::table('ss_theme_installs', function (Blueprint $table) {
             $table->softDeletes();
        });
        Schema::table('ss_twilios', function (Blueprint $table) {
             $table->softDeletes();
        });
        Schema::table('ss_twilio_blacklists', function (Blueprint $table) {
             $table->softDeletes();
        });
        Schema::table('ss_usage_fees', function (Blueprint $table) {
             $table->softDeletes();
        });
        Schema::table('ss_webhooks', function (Blueprint $table) {
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
        Schema::table('ss_activity_logs', function (Blueprint $table) {
            //
        });
    }
}
