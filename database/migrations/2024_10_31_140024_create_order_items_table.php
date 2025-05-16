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
        Schema::create('order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->smallInteger('quantity');
            $table->decimal('price', 10, 2);

            $table->bigInteger('purchase_order')->unsigned()->index();
            $table->foreign('purchase_order')->references('id')->on('purchase_orders')->onDelete('cascade');

            $table->bigInteger('product_id')->unsigned()->index();
            $table->foreign('product_id')->references('product_id')->on('product')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
