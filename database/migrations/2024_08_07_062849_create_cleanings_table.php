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
        Schema::create('cleanings', function (Blueprint $table) {
            $table->id();
            $table->longText('listing_title');
            $table->bigInteger('listing_id');
            $table->bigInteger('booking_id');
            $table->string('key_code')->nullable();
            $table->date('checkout_date');
            $table->date('cleaning_date');
            $table->time('cleaning_time');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleanings');
    }
};
