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
        // Schema::table('permission_module', function (Blueprint $table) {
        //     $table->bigInteger('parent_module_id')->nullable()->after('is_parent');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permission_module', function (Blueprint $table) {
            //
        });
    }
};
