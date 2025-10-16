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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title')->unique();
            $table->string('currency');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country');
            $table->string('state')->nullable();
            $table->string('city');
            $table->string('address')->nullable();
            $table->decimal('longitude', 10, 8)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->string('timezone')->nullable();
            $table->string('property_type')->nullable();
            $table->integer('group_id');
            $table->uuid('ch_group_id');
            $table->uuid('ch_property_id');
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
        Schema::dropIfExists('properties');
    }
};
