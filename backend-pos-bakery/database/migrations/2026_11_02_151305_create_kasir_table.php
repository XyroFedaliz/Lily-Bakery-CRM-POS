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
        Schema::create('kasir', function (Blueprint $table) {
            $table->id('id_kasir');
            $table->string('kode_kasir', 20)->unique();
            $table->string('nama_kasir', 100);
            // Opsional (Rekomendasi Kritik): Tambahkan kolom pin untuk login kasir
            // $table->string('pin', 255);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kasir');
    }
};
