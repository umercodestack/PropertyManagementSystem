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
        Schema::create('apartment_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('apartment_id');
            $table->unsignedBigInteger('discount_id');
            $table->bigInteger('price');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->foreign('apartment_id')->references('id')->on('apartments')
                ->onDelete('cascade');
            $table->foreign('discount_id')->references('id')->on('discounts')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartment_prices');
    }
};
