<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    /**
     * Daftar Kategori Produk (Tab Kategori di Layar 03)
     */
    public function kategori()
    {
        $kategori = KategoriProduk::all();

        return response()->json([
            'status' => 'success',
            'data'   => $kategori,
        ]);
    }

    /**
     * Daftar Produk & Filter Stok (Layar 03 Katalog & Layar 11 Stok)
     */
    public function index(Request $request)
    {
        $query = Produk::with('kategori');

        // Filter berdasarkan kategori
        if ($request->filled('id_kategori')) {
            $query->where('id_kategori', $request->id_kategori);
        }

        // Pencarian berdasarkan nama atau kode produk
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%")
                  ->orWhere('kode_produk', 'like', "%{$search}%");
            });
        }

        // Filter status stok untuk Layar 11 (menipis / habis)
        if ($request->status_stok === 'habis') {
            $query->where('stok', '<=', 0);
        } elseif ($request->status_stok === 'menipis') {
            $query->whereBetween('stok', [1, 10]);
        }

        $produk = $query->orderBy('nama_produk')->get();

        return response()->json([
            'status'    => 'success',
            'ringkasan' => [
                'total_jenis_produk' => Produk::count(),
                'stok_menipis'       => Produk::whereBetween('stok', [1, 10])->count(),
                'stok_habis'         => Produk::where('stok', '<=', 0)->count(),
            ],
            'data'      => $produk,
        ]);
    }

    /**
     * Tambah Stok Produk & Update Harga Jual (Layar 12 Tambah Stok)
     */
    public function tambahStok(Request $request, $id_produk)
    {
        $request->validate([
            'jumlah_masuk' => 'required|integer|min:1',
            'harga_jual'   => 'nullable|numeric|min:0',
        ]);

        $produk = Produk::findOrFail($id_produk);
        $stokAwal = $produk->stok;

        $produk->stok += $request->jumlah_masuk;
        if ($request->filled('harga_jual')) {
            $produk->harga_jual = $request->harga_jual;
        }
        $produk->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Stok produk berhasil ditambahkan.',
            'data'    => [
                'produk'                => $produk,
                'stok_sebelumnya'       => $stokAwal,
                'jumlah_masuk'          => (int) $request->jumlah_masuk,
                'stok_setelah_ditambah' => $produk->stok,
            ],
        ]);
    }

    /**
     * Edit / Koreksi Stok Produk & Harga Jual (Layar 13 Edit Stok)
     */
    public function editStok(Request $request, $id_produk)
    {
        $request->validate([
            'stok_sebenarnya' => 'required|integer|min:0',
            'harga_jual'      => 'nullable|numeric|min:0',
        ]);

        $produk = Produk::findOrFail($id_produk);
        $stokTercatat = $produk->stok;
        $selisih = $request->stok_sebenarnya - $stokTercatat;

        $produk->stok = $request->stok_sebenarnya;
        if ($request->filled('harga_jual')) {
            $produk->harga_jual = $request->harga_jual;
        }
        $produk->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data stok produk berhasil diperbarui.',
            'data'    => [
                'produk'          => $produk,
                'stok_tercatat'   => $stokTercatat,
                'stok_sebenarnya' => $produk->stok,
                'selisih_stok'    => $selisih,
            ],
        ]);
    }
}
