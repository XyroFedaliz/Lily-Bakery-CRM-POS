<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            KasirSeeder::class,
            KategoriProdukSeeder::class,
            ProdukSeeder::class,
            MemberSeeder::class,
            TransaksiAwalSeeder::class,
        ]);
    }
}