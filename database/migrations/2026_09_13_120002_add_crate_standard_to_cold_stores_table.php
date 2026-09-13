<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cold_stores', function (Blueprint $table) {
            $table->foreignId('crate_standard_id')
                ->nullable()
                ->after('code')
                ->constrained('crate_standards')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cold_stores', function (Blueprint $table) {
            $table->dropConstrainedForeignId('crate_standard_id');
        });
    }
};
