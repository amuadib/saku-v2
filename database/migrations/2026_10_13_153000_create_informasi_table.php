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
        if (! Schema::hasTable('informasi')) {
            Schema::create('informasi', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('judul');
                $table->string('kategori')->default('Umum');
                $table->longText('konten');
                $table->string('gambar')->nullable();
                $table->integer('lembaga_id')->nullable();
                $table->boolean('is_aktif')->default(true);
                $table->date('tanggal');
                $table->uuid('user_id')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informasi');
    }
};
