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
        Schema::table('ss_contracts', function (Blueprint $table) {
            $table->tinyInteger('is_physical_product')->after('is_migrated')->nullable()->default(null)->comment('0 is physical product not 1 for is physical product true');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ss_contracts', function (Blueprint $table) {
            $table->dropColumn('is_physical_product');
        });
    }
};
