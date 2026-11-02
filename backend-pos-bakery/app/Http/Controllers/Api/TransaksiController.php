<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailTransaksi;
use App\Models\Member;
use App\Models\Pembayaran;
use App\Models\Produk;
use App\Models\RiwayatPoin;
use App\Models\TransaksiPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    // Konversi sesuai Skenario 1 & 2:
    // - Perolehan poin: Kelipatan Rp 10.000 = 1 poin
    // - Nilai tukar poin: 1 poin = Rp 100 potongan harga
    private const KELIPATAN_PEROLEHAN_POIN = 10000;
    private const NILAI_TUKAR_SATU_POIN    = 100;

    /**
     * Validasi & Simulasi Penukaran Poin di Tengah Transaksi (Skenario 2 Langkah 1-4, Layar 07)
     */
    public function cekTukarPoin(Request $request)
    {
        $request->validate([
            'id_member'   => 'required|exists:member,id_member',
            'jumlah_poin' => 'required|integer|min:1',
            'subtotal'    => 'required|numeric|min:0',
        ]);

        $member = Member::findOrFail($request->id_member);
        $saldoPoin = $member->saldo_poin;
        $poinDitukar = (int) $request->jumlah_poin;

        // Skenario 2 Langkah 3: Jika poin tidak cukup, tampilkan peringatan
        if ($saldoPoin < $poinDitukar) {
            return response()->json([
                'status'  => 'insufficient_points',
                'message' => "Saldo poin tidak mencukupi. Saldo saat ini: {$saldoPoin} poin.",
                'data'    => [
                    'saldo_poin'  => $saldoPoin,
                    'poin_diminta'=> $poinDitukar,
                    'subtotal'    => (float) $request->subtotal,
                    'grand_total' => (float) $request->subtotal,
                ],
            ], 422);
        }

        // Skenario 2 Langkah 4: Konversi poin menjadi diskon dan kurangi Grand Total
        $potonganHarga = $poinDitukar * self::NILAI_TUKAR_SATU_POIN;
        $diskonEfektif = min((float) $request->subtotal, $potonganHarga);
        $grandTotal    = max(0, (float) $request->subtotal - $diskonEfektif);

        return response()->json([
            'status'  => 'success',
            'message' => 'Poin mencukupi, potongan harga berhasil diterapkan.',
            'data'    => [
                'saldo_poin_awal'   => $saldoPoin,
                'poin_ditukar'      => $poinDitukar,
                'sisa_poin_estimasi'=> $saldoPoin - $poinDitukar,
                'subtotal'          => (float) $request->subtotal,
                'diskon_transaksi'  => $diskonEfektif,
                'grand_total'       => $grandTotal,
            ],
        ]);
    }

    /**
     * Proses Transaksi POS, Pembayaran, Potong Stok, Tukar Poin & Tambah Poin Otomatis
     * (Skenario 1 Langkah 6-9 & Skenario 2 Langkah 5-7, Layar 08 & 09)
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_kasir'              => 'required|exists:kasir,id_kasir',
            'id_member'             => 'nullable|exists:member,id_member',
            'poin_ditukar'          => 'nullable|integer|min:0',
            'metode_pembayaran'     => 'required|in:tunai,debit,qris',
            'jumlah_bayar'          => 'required|numeric|min:0',
            'items'                 => 'required|array|min:1',
            'items.*.id_produk'     => 'required|exists:produk,id_produk',
            'items.*.jumlah'        => 'required|integer|min:1',
            'items.*.diskon_item'   => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $waktuSekarang = Carbon::now();
            $subtotal = 0;
            $detailItems = [];

            // 1. Validasi stok & hitung subtotal dari database
            foreach ($request->items as $itemInput) {
                $produk = Produk::lockForUpdate()->findOrFail($itemInput['id_produk']);

                if ($produk->stok < $itemInput['jumlah']) {
                    abort(422, "Stok produk {$produk->nama_produk} tidak mencukupi (tersisa {$produk->stok}).");
                }

                $hargaSatuan = (float) $produk->harga_jual;
                $diskonItem  = (float) ($itemInput['diskon_item'] ?? 0);
                $subtotal   += ($hargaSatuan * $itemInput['jumlah']) - $diskonItem;

                // Kurangi stok produk
                $produk->decrement('stok', $itemInput['jumlah']);

                $detailItems[] = [
                    'id_produk'    => $produk->id_produk,
                    'jumlah'       => $itemInput['jumlah'],
                    'harga_satuan' => $hargaSatuan,
                    'diskon_item'  => $diskonItem,
                ];
            }

            // 2. Validasi Member & Penukaran Poin (Skenario 2)
            $member = null;
            $poinDitukar = (int) ($request->poin_ditukar ?? 0);
            $diskonTransaksi = 0;

            if ($request->filled('id_member')) {
                $member = Member::findOrFail($request->id_member);

                if ($poinDitukar > 0) {
                    if ($member->saldo_poin < $poinDitukar) {
                        abort(422, 'Saldo poin member tidak mencukupi untuk ditukar.');
                    }
                    $diskonTransaksi = min($subtotal, $poinDitukar * self::NILAI_TUKAR_SATU_POIN);
                }
            }

            $grandTotal = max(0, $subtotal - $diskonTransaksi);

            // 3. Validasi nominal pembayaran
            if ((float) $request->jumlah_bayar < $grandTotal) {
                abort(422, 'Jumlah uang pembayaran kurang dari Grand Total tagihan.');
            }

            // 4. Buat Nomor Transaksi unik (Contoh: #0042 atau TRX-20261102-0002)
            $urutTransaksi = (TransaksiPenjualan::max('id_transaksi') ?? 0) + 1;
            $nomorTransaksi = '#' . str_pad($urutTransaksi, 4, '0', STR_PAD_LEFT);

            // 5. Simpan Header Transaksi Penjualan
            $transaksi = TransaksiPenjualan::create([
                'nomor_transaksi'  => $nomorTransaksi,
                'id_kasir'         => $request->id_kasir,
                'id_member'        => $member?->id_member,
                'waktu_transaksi'  => $waktuSekarang,
                'diskon_transaksi' => $diskonTransaksi,
            ]);

            // 6. Simpan Detail Transaksi
            foreach ($detailItems as $detail) {
                DetailTransaksi::create(array_merge($detail, [
                    'id_transaksi' => $transaksi->id_transaksi,
                ]));
            }

            // 7. Simpan Data Pembayaran
            $pembayaran = Pembayaran::create([
                'id_transaksi'      => $transaksi->id_transaksi,
                'metode_pembayaran' => $request->metode_pembayaran,
                'jumlah_bayar'      => $request->jumlah_bayar,
                'status'            => 'berhasil',
                'waktu_pembayaran'  => $waktuSekarang,
            ]);

            // 8. Pencatatan Poin Member (Tukar Poin & Tambah Poin Otomatis)
            $poinDidapat = 0;
            if ($member) {
                // Jika ada penukaran poin (Skenario 2 Langkah 6)
                if ($poinDitukar > 0) {
                    RiwayatPoin::create([
                        'id_transaksi' => $transaksi->id_transaksi,
                        'jenis_poin'   => 'tukar',
                        'jumlah_poin'  => $poinDitukar,
                        'keterangan'   => "Penukaran {$poinDitukar} poin (Diskon Rp" . number_format($diskonTransaksi, 0, ',', '.') . ")",
                        'waktu'        => $waktuSekarang,
                    ]);
                }

                // Tambah poin otomatis kelipatan Rp 10.000 = 1 poin dari Grand Total (Skenario 1 Langkah 7-8)
                $poinDidapat = (int) floor($grandTotal / self::KELIPATAN_PEROLEHAN_POIN);
                if ($poinDidapat > 0) {
                    RiwayatPoin::create([
                        'id_transaksi' => $transaksi->id_transaksi,
                        'jenis_poin'   => 'tambah',
                        'jumlah_poin'  => $poinDidapat,
                        'keterangan'   => "Perolehan poin transaksi {$nomorTransaksi}",
                        'waktu'        => $waktuSekarang,
                    ]);
                }
            }

            // Refresh relasi untuk respon struk (Layar 09 Selesai)
            $transaksi->load(['kasir', 'member', 'detailTransaksi.produk', 'pembayaran', 'riwayatPoin']);

            return response()->json([
                'status'  => 'success',
                'message' => 'Transaksi berhasil disimpan.',
                'data'    => [
                    'transaksi'       => $transaksi,
                    'ringkasan_struk' => [
                        'nomor_transaksi'   => $transaksi->nomor_transaksi,
                        'waktu_transaksi'   => $transaksi->waktu_transaksi->format('d M Y H:i'),
                        'kasir'             => $transaksi->kasir->nama_kasir,
                        'member'            => $member ? $member->nama_member : 'Non-member',
                        'subtotal'          => $subtotal,
                        'diskon_transaksi'  => $diskonTransaksi,
                        'grand_total'       => $grandTotal,
                        'metode_pembayaran' => $pembayaran->metode_pembayaran,
                        'jumlah_bayar'      => (float) $pembayaran->jumlah_bayar,
                        'kembalian'         => max(0, (float) $pembayaran->jumlah_bayar - $grandTotal),
                        'poin_ditukar'      => $poinDitukar,
                        'poin_ditambah'     => $poinDidapat,
                        'sisa_saldo_poin'   => $member ? $member->fresh()->saldo_poin : 0,
                    ],
                ],
            ], 201);
        });
    }

    /**
     * Rekap Pembelian & Riwayat Transaksi (Layar 10 Rekap Pembelian)
     */
    public function rekap(Request $request)
    {
        $query = TransaksiPenjualan::with(['kasir', 'member', 'detailTransaksi', 'pembayaran', 'riwayatPoin'])
            ->orderByDesc('waktu_transaksi');

        // Filter periode (hari_ini, minggu_ini, bulan_ini, atau tanggal spesifik)
        $periode = $request->get('periode', 'hari_ini');
        if ($periode === 'hari_ini') {
            $query->whereDate('waktu_transaksi', Carbon::today());
        } elseif ($periode === 'minggu_ini') {
            $query->whereBetween('waktu_transaksi', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($periode === 'bulan_ini') {
            $query->whereMonth('waktu_transaksi', Carbon::now()->month)
                  ->whereYear('waktu_transaksi', Carbon::now()->year);
        } elseif ($request->filled('tanggal')) {
            $query->whereDate('waktu_transaksi', $request->tanggal);
        }

        // Filter metode pembayaran (tunai, debit, qris)
        if ($request->filled('metode_pembayaran')) {
            $query->whereHas('pembayaran', function ($q) use ($request) {
                $q->where('metode_pembayaran', $request->metode_pembayaran);
            });
        }

        // Pencarian nomor struk atau nama member
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('member', function ($mq) use ($search) {
                      $mq->where('nama_member', 'like', "%{$search}%");
                  });
            });
        }

        $daftarTransaksi = $query->get();

        // Hitung kartu ringkasan atas pada Layar 10
        $totalTransaksi      = $daftarTransaksi->count();
        $totalPenjualan      = $daftarTransaksi->sum('grand_total');
        $transaksiMember     = $daftarTransaksi->whereNotNull('id_member')->count();
        $poinDiberikan       = $daftarTransaksi->flatMap->riwayatPoin->where('jenis_poin', 'tambah')->sum('jumlah_poin');
        $poinDitukar         = $daftarTransaksi->flatMap->riwayatPoin->where('jenis_poin', 'tukar')->sum('jumlah_poin');

        return response()->json([
            'status'    => 'success',
            'ringkasan' => [
                'total_transaksi'  => $totalTransaksi,
                'total_penjualan'  => $totalPenjualan,
                'transaksi_member' => $transaksiMember,
                'poin_diberikan'   => $poinDiberikan,
                'poin_ditukar'     => $poinDitukar,
            ],
            'data'      => $daftarTransaksi,
        ]);
    }
}