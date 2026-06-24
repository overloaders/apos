<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->decimal('credit_limit', 15, 2)->default(0)->after('total_spent');
            $table->decimal('outstanding_balance', 15, 2)->default(0)->after('credit_limit');
            $table->date('last_credit_at')->nullable()->after('outstanding_balance');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['credit_limit', 'outstanding_balance', 'last_credit_at']);
        });
    }
};
