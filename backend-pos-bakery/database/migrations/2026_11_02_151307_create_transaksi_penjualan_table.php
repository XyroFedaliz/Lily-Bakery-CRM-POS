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
        Schema::create('transaksi_penjualan', function (Blueprint $table) {
            $table->id('id_transaksi');
            $table->string('nomor_transaksi', 30)->unique();
            $table->foreignId('id_kasir')
                  ->constrained('kasir', 'id_kasir')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
            // Nullable karena pelanggan non-member tetap bisa bertransaksi
            $table->foreignId('id_member')
                  ->nullable()
                  ->constrained('member', 'id_member')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
            $table->dateTime('waktu_transaksi');
            $table->decimal('diskon_transaksi', 14, 2)->default(0);
            // Opsional (Rekomendasi Kritik): Tambahkan subtotal & grand_total
            // $table->decimal('subtotal', 14, 2)->default(0);
            // $table->decimal('grand_total', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_penjualan');
    }
};
