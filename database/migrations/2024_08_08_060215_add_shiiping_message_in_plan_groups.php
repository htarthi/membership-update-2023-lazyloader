<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ss_plan_groups', function (Blueprint $table) {
            $table->string('shipping_discount_message')->after('shipping_discount_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ss_plan_groups', function (Blueprint $table) {
            $table->dropIfExists('shipping_discount_message');
        });
    }
};
