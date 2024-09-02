<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusDisplayToSsLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_languages', function (Blueprint $table) {
            $table->text('portal_status_display_lifetime')->after('portal_general_expired');
            $table->text('portal_status_display_expiring')->after('portal_status_display_lifetime');
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
