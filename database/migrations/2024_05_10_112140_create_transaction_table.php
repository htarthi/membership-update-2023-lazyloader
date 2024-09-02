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
        Schema::create('transaction', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('customer_id')->foreign('customer_id')->references('id')->on('ss_customers')->onDelete('cascade');
            $table->unsignedBigInteger('contract_id')->foreign('contract_id')->references('id')->on('ss_contracts')->onDelete('cascade');
            $table->enum('credit_debit', ['credit', 'debit']);
            $table->decimal('amount')->comment('Store Credit Amount')->default(0); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction');
    }
};
