<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custody_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custodian_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cold_store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pomegranate_load_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // issue | return_to_vehicle | return_to_store
            $table->unsignedInteger('crates_count');
            $table->decimal('weight_kg', 12, 3);
            $table->dateTime('moved_at');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['custodian_id', 'moved_at']);
            $table->index(['cold_store_id', 'moved_at']);
            $table->index(['pomegranate_load_id', 'moved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custody_transactions');
    }
};
