<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountColumnsToSsPlanGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_plan_groups', function (Blueprint $table) {
            $table->string('discount_code')->after('tag_order')->nullable()->comment('This will be the text of the Shopify discount code they want us to use');
            $table->string('discount_code_members', 1024)->after('discount_code')->nullable()->comment('This will be the text that is displayed to members on the cart page');
            $table->boolean('is_display_on_cart_page')->after('discount_code_members')->default(0);
            $table->boolean('is_display_on_member_login')->after('is_display_on_cart_page')->default(0);
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
