<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemainingColumnsToSsLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_languages', function (Blueprint $table) {
           $table->string('portal_is_waiting')->after('portal_no_membership')->default('Loading...');
           $table->string('portal_dropdown_label')->after('portal_is_waiting')->default('Membership');
           $table->string('portal_next_renewal')->after('portal_dropdown_label')->default('Next Renewal');
           
           $table->string('toaster_email_sent')->after('portal_next_renewal')->default('Email sent');
           $table->string('toaster_membership_updated')->after('toaster_email_sent')->default('Subscription updated');
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
