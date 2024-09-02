<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTagsColumnToSsPlanGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_plan_groups', function (Blueprint $table) {
            $table->string('tag_customer')->after('position')->nullable();
            $table->string('tag_order', 40)->after('tag_customer')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_plan_groups', function (Blueprint $table) {
            //
        });
    }
}
