<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeRuleAttribute1HandleTypeToTextInSsRules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_rules', function (Blueprint $table) {
            $table->text('rule_attribute1_handle')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_rules', function (Blueprint $table) {
            $table->string('rule_attribute1_handle')->change();
        });
    }
}
