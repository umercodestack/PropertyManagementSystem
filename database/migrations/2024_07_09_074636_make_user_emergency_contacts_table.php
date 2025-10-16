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

     Schema::create('user_emergency_contact', function (Blueprint $table) {
         $table->id();
         $table->unsignedBigInteger('user_id');
         $table->string('first_name')->nullable();
         $table->string('last_name')->nullable();
         $table->integer('country_id')->nullable();
         $table->integer('city_id')->nullable();
         $table->bigInteger('phone')->nullable();
         $table->string('email')->nullable()->unique();
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
        //
    }
};
