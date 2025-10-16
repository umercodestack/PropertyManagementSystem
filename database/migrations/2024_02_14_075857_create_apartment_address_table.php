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
        Schema::create('apartment_address', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('apartment_id');
            $table->float('latitude');
            $table->float('longitude');
            $table->string('country');
            $table->string('address_line');
            $table->string('city');
            $table->string('province');
            $table->integer('postal');
            $table->timestamps();

            $table->foreign('apartment_id')->references('id')->on('apartments')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartment_address');
    }
};
