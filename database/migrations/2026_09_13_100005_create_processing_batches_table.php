<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processing_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pomegranate_load_id')->constrained()->cascadeOnDelete();
            $table->string('process_type'); // peeling | juice
            $table->unsignedInteger('input_crates_count');
            $table->decimal('input_weight_kg', 12, 3);
            $table->decimal('output_weight_kg', 12, 3);
            $table->decimal('waste_weight_kg', 12, 3)->default(0);
            $table->dateTime('processed_at');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['pomegranate_load_id', 'processed_at']);
            $table->index('process_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processing_batches');
    }
};
