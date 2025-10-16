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
        Schema::create('calenders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('listing_id');
            $table->integer('availability');
            $table->integer('max_stay');
            $table->integer('min_stay_through');
            $table->integer('rate');
            $table->date('calender_date');
            $table->boolean('is_lock')->default(0);
            $table->longText('block_reason')->nullable();
            $table->bigInteger('updated_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calenders');
    }
};
