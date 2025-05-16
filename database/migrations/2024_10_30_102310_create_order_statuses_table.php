<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->smallIncrements('ostatus_id');
            $table->string('status_name')->unique();
            $table->string('status_description')->nullable();
            $table->timestamps();
        });

        DB::table('order_statuses')->insert([
            ['status_name' => 'Pending', 'status_description' => 'Order has been received and is awaiting processing.'],
            ['status_name' => 'Processing', 'status_description' => 'Order is accepted and is currently being prepared.'],
            ['status_name' => 'Shipped', 'status_description' => 'Order has been shipped to the customer.'],
            ['status_name' => 'Delivered', 'status_description' => 'Order has been delivered to the customer.'],
            ['status_name' => 'Completed', 'status_description' => 'Order process is complete.'],
            ['status_name' => 'Cancelled', 'status_description' => 'Order has been cancelled.'],
            ['status_name' => 'Rejected', 'status_description' => 'Order has been rejected.'],
            ['status_name' => 'Refunded', 'status_description' => 'Order has been refunded.'],
            ['status_name' => 'On Hold', 'status_description' => 'Order is on hold.'],
            ['status_name' => 'Failed', 'status_description' => 'Order process failed.'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_statuses');
    }
};
