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
        Schema::table('ss_forms', function (Blueprint $table) {
            $table->text('field_options')->change();
            $table->text('field_label')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ss_forms', function (Blueprint $table) {
            $table->string('field_options', 255)->change();
            $table->string('field_label', 255)->change();
        });
    }
};
