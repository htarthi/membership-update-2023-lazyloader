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
        Schema::create('automatic_discount', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('discount_id')->nullable();
            $table->bigInteger('shop_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('product_id')->nullable();
            $table->bigInteger('tier_id')->nullable();
            $table->string('customer_tag',255)->nullable();
            $table->string('collection_id',255)->nullable();
            $table->string('collection_name',255)->nullable();
            $table->float('collection_discount')->nullable();
            $table->string('collection_discount_type')->nullable();
            $table->text('collection_message')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automatic_discount');
    }
};
