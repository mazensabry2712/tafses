<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pomegranate_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pomegranate_load_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->string('pricing_unit'); // kg | crate | fixed
            $table->decimal('unit_price', 12, 3);
            $table->decimal('quantity', 12, 3);
            $table->decimal('total_amount', 12, 3);
            $table->decimal('paid_amount', 12, 3)->default(0);
            $table->date('purchased_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['supplier_id', 'purchased_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pomegranate_purchases');
    }
};
