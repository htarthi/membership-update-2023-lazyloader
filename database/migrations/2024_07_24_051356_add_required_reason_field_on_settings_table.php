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
        Schema::table('ss_settings', function (Blueprint $table) {
            $table->boolean('required_reason')->default(false)->after('custom_submit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ss_settings', function (Blueprint $table) {
            $table->dropColumn('required_reason');
        });
    }
};
