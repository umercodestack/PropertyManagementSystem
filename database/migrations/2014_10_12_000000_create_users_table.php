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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_user_id')->nullable();
            $table->integer('role_id');
            $table->integer('host_type_id')->nullable();
            $table->string('name');
            $table->string('surname');
            $table->string('email')->unique();
            $table->string('email_verification_code')->nullable();
            $table->boolean('email_verified')->default(0)->nullable();
            $table->bigInteger('phone');
            $table->string('phone_verification_code')->nullable();
            $table->boolean('phone_verified')->default(0)->nullable();
            $table->string('dob');
            $table->string('gender');
            $table->string('country');
            $table->string('city');
            $table->integer('plan_verified')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
