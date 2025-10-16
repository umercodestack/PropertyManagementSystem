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
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('apartment_type');
            $table->string('rental_type');
            $table->longText('description');
            $table->string('title');
            $table->integer('max_guests');
            $table->integer('bedrooms');
            $table->integer('beds');
            $table->integer('bathrooms');
            $table->json('amenities');
            $table->json('any_of_these');
            $table->json('unique_attr');
            $table->bigInteger('js_id')->nullable();
            $table->bigInteger('host_type_id')->nullable();
            $table->bigInteger('created_by');
            $table->boolean('door_lock')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
