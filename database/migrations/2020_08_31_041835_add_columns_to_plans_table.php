<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedBigInteger('app_id')->nullable()->after('id');
            $table->boolean('active')->nullable()->after('on_install');
            $table->boolean('public')->nullable()->comment('Should this be displayed when merchants are choosing a plan?')->after('active');
            $table->decimal('transaction_fee', 8, 4)->nullable()->after('public');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            //
        });
    }
}
