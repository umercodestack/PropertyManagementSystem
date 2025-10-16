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
        Schema::create('host_charge_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('host_type_id');
            $table->string('charge_type');
            $table->timestamps();

            $table->foreign('host_type_id')->references('id')->on('host_types')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('host_charge_type');
    }
};
