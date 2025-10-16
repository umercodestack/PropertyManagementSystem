<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTokenToListingIcalLinksTable extends Migration
{
    public function up()
    {
        Schema::table('listing_ical_links', function (Blueprint $table) {
            $table->string('token')->unique()->nullable()->after('url');
        });
    }

    public function down()
    {
        Schema::table('listing_ical_links', function (Blueprint $table) {
            $table->dropColumn('token');
        });
    }
}