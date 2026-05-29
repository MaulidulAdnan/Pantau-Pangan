<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchant_stores', function (Blueprint $table) {
            $table->string('shop_photo')->nullable()->after('store_address');
        });
    }

    public function down(): void
    {
        Schema::table('merchant_stores', function (Blueprint $table) {
            $table->dropColumn('shop_photo');
        });
    }
};
