<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('informasi', function (Blueprint $table) {
            $table->boolean('is_sticky')->default(false)->after('is_aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('informasi', function (Blueprint $table) {
            $table->dropColumn('is_sticky');
        });
    }
};
