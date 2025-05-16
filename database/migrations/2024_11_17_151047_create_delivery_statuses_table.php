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
        Schema::create('delivery_statuses', function (Blueprint $table) {
            $table->smallIncrements('dstatus_id');
            $table->string('dstatus_name');
            $table->string('dstatus_description');

            $table->timestamps();
        });

        DB::table('delivery_statuses')->insert([
            [
                'dstatus_name' => 'Pending',
                'dstatus_description' => 'The delivery has been accepted but not yet shipped.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dstatus_name' => 'Out for Delivery',
                'dstatus_description' => 'The delivery is currently on the way to the recipient.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dstatus_name' => 'Delivered',
                'dstatus_description' => 'The delivery has been successfully delivered to the recipient.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dstatus_name' => 'Failed Delivery',
                'dstatus_description' => 'The delivery could not be completed due to an issue.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dstatus_name' => 'Return to Sender',
                'dstatus_description' => 'The delivery has been returned to the sender.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_statuses');
    }
};
