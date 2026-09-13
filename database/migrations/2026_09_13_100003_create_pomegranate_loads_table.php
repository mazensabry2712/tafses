<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pomegranate_loads', function (Blueprint $table) {
            $table->id();
            $table->string('load_number')->unique();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('received_at');
            $table->unsignedInteger('loaded_crates_count');
            $table->decimal('loaded_weight_kg', 12, 3);
            $table->unsignedInteger('on_vehicle_crates_count');
            $table->decimal('on_vehicle_weight_kg', 12, 3);
            $table->unsignedInteger('available_crates_count')->default(0);
            $table->decimal('available_weight_kg', 12, 3)->default(0);
            $table->string('status')->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['supplier_id', 'received_at']);
            $table->index(['vehicle_id', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pomegranate_loads');
    }
};
