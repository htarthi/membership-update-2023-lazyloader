<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMemberNumberToSsLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_languages', function (Blueprint $table) {
            $table->string('member_number')->after('toaster_membership_updated')->default('Member Number');
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
