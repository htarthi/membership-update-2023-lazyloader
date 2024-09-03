<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequiredToSsFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_forms', function (Blueprint $table) {
            $table->dropColumn('active');
            $table->boolean('field_required')->default(0)->after('field_options');
            $table->boolean('field_displayed')->default(0)->after('field_required');
            $table->integer('field_order')->default(0)->after('field_displayed');
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_forms', function (Blueprint $table) {
            //
        });
    }
}
