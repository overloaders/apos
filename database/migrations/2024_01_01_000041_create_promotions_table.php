<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['discount_percent', 'discount_amount', 'buy_x_get_y', 'bundle', 'member_discount']);
            $table->decimal('value', 15, 2)->default(0);
            $table->decimal('min_purchase', 15, 2)->default(0);
            $table->decimal('buy_qty', 10, 2)->default(0);
            $table->decimal('get_qty', 10, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
