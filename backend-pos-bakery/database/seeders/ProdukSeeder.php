<?php

namespace Database\Seeders;

use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Database\Seeder;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $katRotiManis = KategoriProduk::where('nama_kategori', 'Roti Manis')->first();
        $katRotiTawar = KategoriProduk::where('nama_kategori', 'Roti Tawar')->first();
        $katKue       = KategoriProduk::where('nama_kategori', 'Kue')->first();
        $katMinuman   = KategoriProduk::where('nama_kategori', 'Minuman')->first();

        // 8 Produk sesuai Layar 03 & Layar 11 Wireframe Lyly Bakery
        $daftarProduk = [
            [
                'kode_produk' => 'PRD-001',
                'id_kategori' => $katRotiManis->id_kategori,
                'nama_produk' => 'Croissant Butter',
                'harga_jual'  => 18000,
                'stok'        => 24,
            ],
            [
                'kode_produk' => 'PRD-002',
                'id_kategori' => $katRotiManis->id_kategori,
                'nama_produk' => 'Roti Sobek Cokelat',
                'harga_jual'  => 32000,
                'stok'        => 6,
            ],
            [
                'kode_produk' => 'PRD-003',
                'id_kategori' => $katRotiManis->id_kategori,
                'nama_produk' => 'Donat Gula',
                'harga_jual'  => 6000,
                'stok'        => 18,
            ],
            [
                'kode_produk' => 'PRD-004',
                'id_kategori' => $katRotiTawar->id_kategori,
                'nama_produk' => 'Roti Tawar',
                'harga_jual'  => 22000,
                'stok'        => 30,
            ],
            [
                'kode_produk' => 'PRD-005',
                'id_kategori' => $katKue->id_kategori,
                'nama_produk' => 'Bolu Pandan',
                'harga_jual'  => 15000,
                'stok'        => 0, // Contoh produk stok habis
            ],
            [
                'kode_produk' => 'PRD-006',
                'id_kategori' => $katRotiManis->id_kategori,
                'nama_produk' => 'Roti Keju',
                'harga_jual'  => 12000,
                'stok'        => 20,
            ],
            [
                'kode_produk' => 'PRD-007',
                'id_kategori' => $katMinuman->id_kategori,
                'nama_produk' => 'Es Teh Manis',
                'harga_jual'  => 5000,
                'stok'        => 40,
            ],
            [
                'kode_produk' => 'PRD-008',
                'id_kategori' => $katKue->id_kategori,
                'nama_produk' => 'Brownies',
                'harga_jual'  => 20000,
                'stok'        => 12,
            ],
        ];

        foreach ($daftarProduk as $produk) {
            Produk::updateOrCreate(
                ['kode_produk' => $produk['kode_produk']],
                $produk
            );
        }
    }
}