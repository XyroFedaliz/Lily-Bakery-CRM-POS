<?php

namespace Database\Seeders;

use App\Models\Kasir;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KasirSeeder extends Seeder
{
    public function run(): void
    {
        $daftarKasir = [
            [
                'kode_kasir' => 'KSR-001',
                'nama_kasir' => 'Sari',
                'pin'        => Hash::make('123456'), // PIN Login: 123456
            ],
            [
                'kode_kasir' => 'KSR-002',
                'nama_kasir' => 'Budi',
                'pin'        => Hash::make('123456'), // PIN Login: 123456
            ],
        ];

        foreach ($daftarKasir as $kasir) {
            Kasir::updateOrCreate(
                ['kode_kasir' => $kasir['kode_kasir']],
                $kasir
            );
        }
    }
}
