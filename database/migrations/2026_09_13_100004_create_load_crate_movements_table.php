<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('load_crate_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pomegranate_load_id')->constrained()->cascadeOnDelete();
            $table->string('direction'); // vehicle_to_facility | facility_to_vehicle
            $table->unsignedInteger('crates_count');
            $table->decimal('weight_kg', 12, 3);
            $table->string('reason')->default('unloading');
            $table->dateTime('moved_at');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['pomegranate_load_id', 'moved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('load_crate_movements');
    }
};
