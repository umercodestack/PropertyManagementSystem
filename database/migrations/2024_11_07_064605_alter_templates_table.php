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
        Schema::table('templates', function (Blueprint $table) {
            if (!Schema::hasColumn('templates', 'standard_check_in_time')) {
                $table->string('standard_check_in_time')->after('is_active')->nullable()->default('16');
            }
            if (!Schema::hasColumn('templates', 'standard_check_out_time')) {
                $table->string('standard_check_out_time')->after('standard_check_in_time')->nullable()->default('16');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            //
        });
    }
};
