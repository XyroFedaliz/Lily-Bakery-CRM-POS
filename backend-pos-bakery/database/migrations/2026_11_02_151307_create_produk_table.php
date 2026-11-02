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
        Schema::create('produk', function (Blueprint $table) {
            $table->id('id_produk');
            $table->foreignId('id_kategori')
                  ->constrained('kategori_produk', 'id_kategori')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
            $table->string('kode_produk', 30)->unique();
            $table->string('nama_produk', 150);
            $table->decimal('harga_jual', 14, 2);
            $table->integer('stok')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
