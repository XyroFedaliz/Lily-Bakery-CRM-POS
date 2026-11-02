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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id('id_pembayaran');
            $table->foreignId('id_transaksi')
                  ->constrained('transaksi_penjualan', 'id_transaksi')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->enum('metode_pembayaran', ['tunai', 'debit', 'qris']);
            $table->decimal('jumlah_bayar', 14, 2);
            $table->enum('status', ['pending', 'berhasil', 'gagal'])->default('pending');
            $table->dateTime('waktu_pembayaran')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
