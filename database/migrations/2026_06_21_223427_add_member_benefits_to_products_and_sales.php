<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('member_price', 15, 2)->nullable()->after('selling_price');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->integer('points_redeemed')->default(0)->after('points_earned');
            $table->decimal('points_discount', 15, 2)->default(0)->after('points_redeemed');
            $table->decimal('member_discount', 15, 2)->default(0)->after('points_discount');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('member_price');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['points_redeemed', 'points_discount', 'member_discount']);
        });
    }
};
