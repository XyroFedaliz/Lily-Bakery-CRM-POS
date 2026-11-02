<?php

namespace Database\Seeders;

use App\Models\DetailTransaksi;
use App\Models\Kasir;
use App\Models\Member;
use App\Models\Pembayaran;
use App\Models\Produk;
use App\Models\RiwayatPoin;
use App\Models\TransaksiPenjualan;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransaksiAwalSeeder extends Seeder
{
    /**
     * Membuat 1 transaksi awal agar Member MBR-001 (Andi Pratama)
     * memiliki saldo awal 120 poin sesuai Layar 06 & 07 di Wireframe.
     */
    public function run(): void
    {
        $kasir  = Kasir::where('kode_kasir', 'KSR-001')->first();
        $member = Member::where('kode_member', 'MBR-001')->first();
        $produk = Produk::where('kode_produk', 'PRD-001')->first();

        if (! $kasir || ! $member || ! $produk) {
            return;
        }

        $waktuTransaksi = Carbon::now()->subDays(2);

        $transaksi = TransaksiPenjualan::updateOrCreate(
            ['nomor_transaksi' => '#0001'],
            [
                'id_kasir'         => $kasir->id_kasir,
                'id_member'        => $member->id_member,
                'waktu_transaksi'  => $waktuTransaksi,
                'diskon_transaksi' => 0,
            ]
        );

        DetailTransaksi::updateOrCreate(
            [
                'id_transaksi' => $transaksi->id_transaksi,
                'id_produk'    => $produk->id_produk,
            ],
            [
                'jumlah'       => 5,
                'harga_satuan' => 18000,
                'diskon_item'  => 0,
            ]
        );

        Pembayaran::updateOrCreate(
            ['id_transaksi' => $transaksi->id_transaksi],
            [
                'metode_pembayaran' => 'qris',
                'jumlah_bayar'      => 90000,
                'status'            => 'berhasil',
                'waktu_pembayaran'  => $waktuTransaksi,
            ]
        );

        RiwayatPoin::updateOrCreate(
            [
                'id_transaksi' => $transaksi->id_transaksi,
                'jenis_poin'   => 'tambah',
            ],
            [
                'jumlah_poin' => 120, // Menghasilkan Saldo Poin = 120 untuk MBR-001
                'keterangan'  => 'Akumulasi poin awal member',
                'waktu'       => $waktuTransaksi,
            ]
        );
    }
}