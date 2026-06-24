<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_opname_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 15, 2)->default(0)->after('difference');
            $table->decimal('system_value', 15, 2)->default(0)->after('unit_cost');
            $table->decimal('actual_value', 15, 2)->default(0)->after('system_value');
            $table->decimal('difference_value', 15, 2)->default(0)->after('actual_value');
        });
    }

    public function down(): void
    {
        Schema::table('stock_opname_items', function (Blueprint $table) {
            $table->dropColumn(['unit_cost', 'system_value', 'actual_value', 'difference_value']);
        });
    }
};