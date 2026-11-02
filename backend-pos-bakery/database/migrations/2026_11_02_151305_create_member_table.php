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
        Schema::create('member', function (Blueprint $table) {
            $table->id('id_member');
            $table->string('kode_member', 20)->unique();
            $table->string('no_hp', 20)->unique();
            $table->string('nama_member', 100);
            $table->date('tanggal_daftar');
            // Opsional (Rekomendasi Kritik): Tambahkan saldo_poin agar cek poin member instan
            // $table->integer('saldo_poin')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member');
    }
};
