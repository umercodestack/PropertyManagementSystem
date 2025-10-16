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
        Schema::create('listing_shiftings', function (Blueprint $table) {
            $table->id();
            $table->string('listing_id_one');
            $table->string('title_one');
            $table->string('listing_id_two');
            $table->string('title_two');
            $table->string('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_shiftings');
    }
};
