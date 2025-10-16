<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('booking_otas_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_otas_id');
            $table->unsignedBigInteger('listing_id');
            $table->longText('property_id');
            $table->longText('channel_id');
            $table->date('arrival_date');
            $table->date('departure_date');
            $table->longText('booking_id');
            $table->longText('booking_otas_json_details');
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_otas_details');
    }
};
