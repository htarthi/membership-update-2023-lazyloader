<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewNotificationsColumnsToSsSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_settings', function (Blueprint $table) {
            $table->string('notify_email')->after('subscription_daily_at')->nullable();
            $table->boolean('notify_new')->after('notify_email')->default(0);
            $table->boolean('notify_cancel')->after('notify_new')->default(0);
            $table->boolean('notify_revoke')->after('notify_cancel')->default(0);
            $table->boolean('notify_paymentfailed')->after('notify_revoke')->default(0);
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
