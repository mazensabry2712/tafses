<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crate_standards', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('gross_weight_kg', 8, 3);
            $table->decimal('tare_weight_kg', 8, 3)->default(2.500);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crate_standards');
    }
};
