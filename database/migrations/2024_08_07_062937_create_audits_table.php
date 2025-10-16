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
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->longText('listing_title')->nullable();
            $table->bigInteger('listing_id')->nullable();
            $table->bigInteger('host_id');
            $table->string('host_name')->nullable();
            $table->string('host_phone')->nullable();
            $table->string('poc_name');
            $table->bigInteger('poc');
            $table->bigInteger('assignTo');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('audit_date');
            $table->longText('location');
            $table->string('key_code')->nullable();
            $table->string('status')->nullable();
            $table->longText('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
