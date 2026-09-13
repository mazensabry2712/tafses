<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pomegranate_purchases', function (Blueprint $table) {
            $table->unique('pomegranate_load_id', 'pomegranate_purchases_load_unique');
        });
    }

    public function down(): void
    {
        Schema::table('pomegranate_purchases', function (Blueprint $table) {
            $table->dropUnique('pomegranate_purchases_load_unique');
        });
    }
};
