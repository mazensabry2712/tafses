<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained('finished_product_sales')->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('payment_method')->nullable();
            $table->dateTime('paid_at');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'paid_at']);
            $table->index(['sale_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_payments');
    }
};
