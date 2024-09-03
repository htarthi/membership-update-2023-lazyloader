<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsThemeInstallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_theme_installs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->nullable()->comment('ID from shops table');
            $table->string('theme_id')->nullable()->comment('the Shopify theme ID');
            $table->string('theme_name')->nullable()->comment('the name of the theme');
            $table->string('theme_version')->nullable()->comment('a version like 12.0.2');
            $table->string('theme_author')->nullable()->comment('theme author');
            $table->string('install_status')->nullable();
            $table->string('install_messages')->nullable()->comment('a place to dump errors that we encounter');
            $table->timestamps();

            $table->foreign('shop_id')->on('shops')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ss_theme_installs');
    }
}
