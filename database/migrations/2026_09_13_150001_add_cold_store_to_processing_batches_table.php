<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processing_batches', function (Blueprint $table) {
            $table->foreignId('cold_store_id')
                ->nullable()
                ->after('pomegranate_load_id')
                ->constrained('cold_stores')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('processing_batches', function (Blueprint $table) {
            $table->dropForeign(['cold_store_id']);
            $table->dropColumn('cold_store_id');
        });
    }
};
