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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Purchasing Order', 'Backorder'])->default('Purchasing Order');
            $table->string('payment_method')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('total_price')->nullable();
            $table->string('reason')->nullable();

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            // Ensure supplier_id is NOT nullable
            $table->bigInteger('supplier_id')->unsigned()->index()->default(3);
            $table->foreign('supplier_id')->references('id')->on('users')->cascadeOnDelete();

            $table->smallInteger('order_status')->unsigned()->index()->default(1);
            $table->foreign('order_status')->references('ostatus_id')->on('order_statuses')->onDelete('cascade');
            
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};

