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
        Schema::table('siswa_aktivitas', function (Blueprint $table) {
            $table->json('data_lama')->nullable()->after('keterangan');
            $table->json('data_baru')->nullable()->after('data_lama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa_aktivitas', function (Blueprint $table) {
            $table->dropColumn(['data_lama', 'data_baru']);
        });
    }
};
