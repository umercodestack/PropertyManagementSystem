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
        Schema::create('listing_charges', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('listing_id');
            $table->integer('cleaning_fee')->nullable();
            $table->integer('default_daily_price')->nullable();
            $table->integer('price_per_extra_person')->nullable();
            $table->integer('security_deposit')->nullable();
            $table->integer('weekend_price')->nullable();
            $table->integer('weekly_price_factor')->nullable();
            $table->integer('monthly_price_factor')->nullable();
            $table->string('listing_currency')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_charges');
    }
};
