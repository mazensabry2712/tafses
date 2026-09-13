<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('pomegranate_purchase_id')->nullable()->constrained('pomegranate_purchases')->nullOnDelete();
            $table->decimal('amount', 12, 3);
            $table->dateTime('paid_at');
            $table->string('payment_method')->default('cash'); // cash | bank | transfer | other
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['supplier_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
    }
};
