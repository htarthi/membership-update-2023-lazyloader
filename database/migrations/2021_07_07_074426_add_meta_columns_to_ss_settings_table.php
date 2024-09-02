<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetaColumnsToSsSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_settings', function (Blueprint $table) {

            $table->bigInteger('meta_rules')->after('tag_order')->nullable();
            $table->bigInteger('meta_questions')->after('meta_rules')->nullable();
            $table->longText('restricted_content')->after('meta_questions')->nullable();
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
