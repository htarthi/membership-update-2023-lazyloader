<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMailgunMethodToSsSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_settings', function (Blueprint $table) {
            $table->renameColumn('sendgrid_enabled', 'mailgun_verified')->default(0);
            $table->renameColumn('sendgrid_api_key', 'mailgun_domain');
            $table->string('mailgun_method')->after('sendgrid_api_key')->nullable()->comment('Basic :: we send emails as though we are the merchant,
            Safe :: we send emails using our domain, but set the reply-to to be their email, Advanced :: we let them verify their domain and send emails from their domain’s email address')->default('Safe');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_settings', function (Blueprint $table) {
            //
        });
    }
}
