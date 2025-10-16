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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->longText('user_id');
            $table->string('commission_type');
            $table->bigInteger('commission_value');
            $table->unsignedBigInteger('listing_id');
            $table->unsignedBigInteger('channel_id');
            $table->longText('listing_json');
            $table->boolean('is_sync')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
