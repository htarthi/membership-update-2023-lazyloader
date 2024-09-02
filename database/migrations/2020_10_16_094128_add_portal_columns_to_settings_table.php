<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPortalColumnsToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_settings', function (Blueprint $table) {
            $table->boolean('portal_can_cancel')->default(1)->after('email_from_email');
            $table->boolean('portal_can_skip')->default(0)->after('portal_can_cancel');
            $table->boolean('portal_can_ship_now')->default(1)->after('portal_can_skip');
            $table->boolean('portal_can_change_qty')->default(0)->after('portal_can_ship_now');
            $table->boolean('portal_can_change_nod')->default(1)->after('portal_can_change_qty');
            $table->boolean('portal_can_change_freq')->default(1)->after('portal_can_change_nod');
            $table->boolean('portal_can_add_product')->default(1)->after('portal_can_change_freq');
            $table->boolean('portal_show_content')->default(1)->after('portal_can_add_product');
            $table->longText('portal_content')->nullable()->after('portal_show_content');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            //
        });
    }
}
