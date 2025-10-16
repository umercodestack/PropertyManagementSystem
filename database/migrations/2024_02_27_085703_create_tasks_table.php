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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_title');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('apartment_id');
            $table->string('stage');
            $table->string('frequency');
            $table->string('picture');
            $table->time('time_duration');
            $table->date('date_duration');
            $table->time('completion_time');
            $table->string('status');
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('task_categories')
                ->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')
                ->onDelete('cascade');
            $table->foreign('apartment_id')->references('id')->on('apartments')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
