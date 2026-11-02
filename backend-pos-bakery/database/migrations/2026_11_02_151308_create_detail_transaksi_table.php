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
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->id('id_detail');
            $table->foreignId('id_transaksi')
                  ->constrained('transaksi_penjualan', 'id_transaksi')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->foreignId('id_produk')
                  ->constrained('produk', 'id_produk')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
            $table->integer('jumlah');
            $table->decimal('harga_satuan', 14, 2);
            $table->decimal('diskon_item', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi');
    }
};
