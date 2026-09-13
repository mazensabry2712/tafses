<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finished_product_id')->constrained()->restrictOnDelete();
            $table->string('direction', 20);
            $table->decimal('quantity', 14, 3);
            $table->string('reason', 1000);
            $table->timestamp('adjusted_at');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['finished_product_id', 'adjusted_at']);
            $table->index('direction');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
