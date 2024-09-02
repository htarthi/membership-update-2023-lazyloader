<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPortalStatusDisplayBillingFailedToSsLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_languages', function (Blueprint $table) {
            $table->text('portal_status_display_billing_failed')->after('portal_status_display_expiring');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_languages', function (Blueprint $table) {
            //
        });
    }
}
