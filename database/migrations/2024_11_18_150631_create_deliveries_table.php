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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->bigIncrements('delivery_id');
            $table->date('issued_date');
            $table->date('date_delivered')->nullable();

            $table->bigInteger('po_id')->unsigned()->index();
            $table->foreign('po_id')->references('id')->on('purchase_orders')->onDelete('cascade');

            // $table->unsignedBigInteger('backorder_id')->unsigned()->index();
            // $table->foreign('backorder_id')->references('backorder_id')->on('backorders')->onDelete('cascade');

            $table->smallInteger('dstatus_id')->unsigned()->default(1);
            $table->foreign('dstatus_id')->references('dstatus_id')->on('delivery_statuses')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
