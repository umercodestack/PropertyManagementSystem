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
        Schema::create('listing_settings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('listing_id');
            $table->longText('rate_plan_id');
            $table->string('listing_currency');
            $table->string('instant_booking');
            $table->bigInteger('default_daily_price');
            $table->bigInteger('guests_included')->nullable();
            $table->bigInteger('weekend_price')->nullable();
            $table->bigInteger('price_per_extra_person')->nullable();
            $table->bigInteger('weekly_price_factor')->nullable();
            $table->bigInteger('monthly_price_factor')->nullable();
            $table->bigInteger('pass_through_linen_fee')->nullable();
            $table->bigInteger('pass_through_security_deposit')->nullable();
            $table->bigInteger('pass_through_resort_fee')->nullable();
            $table->bigInteger('pass_through_community_fee')->nullable();
            $table->bigInteger('pass_through_pet_fee')->nullable();
            $table->bigInteger('pass_through_cleaning_fee')->nullable();
            $table->bigInteger('pass_through_short_term_cleaning_fee')->nullable();
            $table->bigInteger('cleaning_fee')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_settings');
    }
};
