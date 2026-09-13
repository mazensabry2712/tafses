<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('load_crate_movements', function (Blueprint $table) {
            $table->foreignId('cold_store_id')
                ->nullable()
                ->after('pomegranate_load_id')
                ->constrained()
                ->nullOnDelete();

            $table->index(['cold_store_id', 'moved_at']);
        });
    }

    public function down(): void
    {
        Schema::table('load_crate_movements', function (Blueprint $table) {
            $table->dropForeign(['cold_store_id']);
            $table->dropIndex(['cold_store_id', 'moved_at']);
            $table->dropColumn('cold_store_id');
        });
    }
};
