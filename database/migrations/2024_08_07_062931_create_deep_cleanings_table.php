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
        Schema::create('deep_cleanings', function (Blueprint $table) {
            $table->id();
            $table->longText('listing_title')->nullable();
            $table->bigInteger('listing_id')->nullable();
            $table->bigInteger('host_id')->nullable();  
            $table->string('host_name')->nullable();
            $table->string('host_phone')->nullable();
            $table->bigInteger('poc');
            $table->string('poc_name');
            $table->bigInteger('audit_id')->nullable();
            $table->bigInteger('assignToPropertyManager');
            $table->bigInteger('assignToVendor');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('cleaning_date')->nullable();
            $table->longText('location');
            $table->string('key_code')->nullable();
            $table->string('status')->nullable();
            $table->longtext('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deep_cleanings');
    }
};
