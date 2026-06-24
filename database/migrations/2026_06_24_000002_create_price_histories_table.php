<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('old_cost_price', 15, 2)->default(0);
            $table->decimal('new_cost_price', 15, 2)->default(0);
            $table->decimal('old_selling_price', 15, 2)->default(0);
            $table->decimal('new_selling_price', 15, 2)->default(0);
            $table->decimal('old_member_price', 15, 2)->default(0);
            $table->decimal('new_member_price', 15, 2)->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_histories');
    }
};
