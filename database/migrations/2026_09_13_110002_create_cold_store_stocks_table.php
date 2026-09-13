<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cold_store_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cold_store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pomegranate_load_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('crates_count')->default(0);
            $table->decimal('weight_kg', 12, 3)->default(0);
            $table->timestamps();
            $table->unique(['cold_store_id', 'pomegranate_load_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cold_store_stocks');
    }
};
