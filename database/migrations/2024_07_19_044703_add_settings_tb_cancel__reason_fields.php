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
            $table->boolean('cancellation_reason_enable')->default(false)->after('auto_fulfill');
            $table->boolean('cancellation_reason_enable_custom')->default(false)->after('cancellation_reason_enable');
            $table->string('custom_reason_message')->default('We’re sorry to see you go.  You will keep access to your membership until the remainder of your current paid period expires.  Please let us know the reason for your cancellation.')->after('cancellation_reason_enable_custom');
            $table->string('custom_options',255)->default('Other')->after('custom_reason_message');
            $table->string('custom_submit',255)->default('Submit')->after('custom_options');
            $table->string('custom_cancel',255)->default('Don’t cancel')->after('custom_submit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ss_settings', function (Blueprint $table) {
            $table->dropColumn('cancellation_reason_enable');
            $table->dropColumn('cancellation_reason_enable');
            $table->dropColumn('custom_reason_message');
            $table->dropColumn('custom_options');
            $table->dropColumn('custom_submit');
            $table->dropColumn('custom_cancel');
        });
    }
};
