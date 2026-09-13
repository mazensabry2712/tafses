<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finished_product_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finished_product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('processing_batch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // production | sale | adjustment_in | adjustment_out
            $table->decimal('quantity', 14, 3);
            $table->dateTime('moved_at');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['finished_product_id', 'moved_at']);
            $table->index(['processing_batch_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finished_product_transactions');
    }
};
