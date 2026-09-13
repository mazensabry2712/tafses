<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finished_product_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('finished_product_sales')->cascadeOnDelete();
            $table->foreignId('finished_product_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 14, 3);
            $table->decimal('unit_price', 14, 2);
            $table->decimal('total_amount', 14, 2);
            $table->timestamps();
            $table->index(['sale_id', 'finished_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finished_product_sale_items');
    }
};
