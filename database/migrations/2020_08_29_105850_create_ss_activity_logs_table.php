<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsActivityLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->nullable()->comment('ID from shops table');
            $table->unsignedBigInteger('user_id')->nullable()->comment('ID from users table');
            $table->unsignedBigInteger('ss_contract_id')->nullable()->comment('internal contract id');
            $table->unsignedBigInteger('ss_customer_id')->nullable()->comment('ID from customers table');
            $table->unsignedBigInteger('ss_plan_id')->nullable()->comment('ID from ss plans table');
            $table->timestamp('ts_created')->nullable();
            $table->string('user_type')->nullable()->comment('System, User, Shopify');
            $table->string('user_name')->nullable()->comment('Logged in user’s name if available');
            $table->longText('message')->nullable()->comment('text, blob, varchar?');
            $table->timestamps();

            $table->foreign('shop_id')->on('shops')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign('user_id')->on('users')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign('ss_contract_id')->on('ss_contracts')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign('ss_customer_id')->on('ss_customers')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign('ss_plan_id')->on('ss_plans')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ss_activity_logs');
    }
}
