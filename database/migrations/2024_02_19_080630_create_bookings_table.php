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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname');
            $table->string('email');
            $table->bigInteger('phone');
            $table->string('country');
            $table->string('city');
            $table->string('cnic_passport');
            $table->integer('adult');
            $table->integer('children');
            $table->integer('rooms');
            $table->string('rating')->nullable();
            $table->string('purpose_of_call')->nullable();
            $table->string('reason')->nullable();
            $table->string('booking_notes')->nullable();
            $table->integer('custom_discount');
            $table->string('payment_method');
            $table->unsignedBigInteger('guest_id');
            $table->longtext('host_id')->nullable();
            $table->bigInteger('listing_id');
            $table->string('booking_sources');
            $table->date('booking_date_start');
            $table->date('booking_date_end');
            $table->integer('service_fee');
            $table->integer('cleaning_fee');
            $table->integer('per_night_price');
            $table->integer('ota_commission')->nullable();
            $table->integer('total_price');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
