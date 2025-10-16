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
        if (!Schema::hasTable('templates')) {
            Schema::create('templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->longText('message');
                $table->json('short_codes')->nullable();
                $table->string('action');
                $table->string('day')->nullable();
                $table->string('time')->nullable();
                $table->string('when_to_send')->nullable();
                $table->boolean('is_active')->default(1);
                $table->integer('user_id');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
