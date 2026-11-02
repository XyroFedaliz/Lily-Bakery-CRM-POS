<?php

namespace Database\Seeders;

use App\Models\KategoriProduk;
use Illuminate\Database\Seeder;

class KategoriProdukSeeder extends Seeder
{
    public function run(): void
    {
        $daftarKategori = [
            'Roti Manis',
            'Roti Tawar',
            'Kue',
            'Minuman',
        ];

        foreach ($daftarKategori as $nama) {
            KategoriProduk::firstOrCreate(['nama_kategori' => $nama]);
        }
    }
}
