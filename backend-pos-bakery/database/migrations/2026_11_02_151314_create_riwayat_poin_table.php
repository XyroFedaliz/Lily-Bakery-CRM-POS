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
        Schema::create('riwayat_poin', function (Blueprint $table) {
            $table->id('id_poin');
            $table->foreignId('id_transaksi')
                  ->constrained('transaksi_penjualan', 'id_transaksi')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->enum('jenis_poin', ['tambah', 'tukar']);
            $table->integer('jumlah_poin');
            $table->string('keterangan', 100)->nullable();
            $table->dateTime('waktu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_poin');
    }
};
