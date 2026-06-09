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
        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('status', ['plaćeno', 'isporučeno', 'otkazano'])->default('plaćeno');
            $table->decimal('total_price', 10, 2)->default(0);
            $table->timestamps();
        });
 
        Schema::create('order_items', function (Blueprint $table) {
            $table->id('order_item_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('user_id');
            $table->string('ingredient_id');
            $table->unsignedInteger('amount');
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
 
            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade');
        });
 
        Schema::create('carts', function (Blueprint $table) {
            $table->id('cart_id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->unsignedInteger('total_amount_of_items')->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->timestamps();
        });
 
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id('cart_item_id');
            $table->unsignedBigInteger('cart_id');
            $table->string('ingredient_id'); 
            $table->unsignedInteger('amount');
            $table->timestamps();
 
            $table->foreign('cart_id')->references('cart_id')->on('carts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
