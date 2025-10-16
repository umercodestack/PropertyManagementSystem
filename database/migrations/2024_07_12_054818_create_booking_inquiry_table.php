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
        Schema::create('booking_inquiry', function (Blueprint $table) {
            $table->id();
            $table->longText('ch_inquiry_id');
            $table->longText('property_id');
            $table->string('status')->nullable();
            $table->longText('comment')->nullable();
            $table->longText('message_thread_id')->nullable();
            $table->string('type')->nullable();
            $table->bigInteger('total_price')->nullable();
            $table->longText('booking_details')->nullable();
            $table->string('event_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_inquiry');
    }
};
