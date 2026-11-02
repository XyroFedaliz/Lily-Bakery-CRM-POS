<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\TransaksiController;
use Illuminate\Support\Facades\Route;

// Endpoint Public (Login Kasir)
Route::post('/login', [AuthController::class, 'login']);

// Endpoint Kasir (Bisa dibungkus middleware('auth:sanctum') saat integrasi token di Android)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Katalog & Manajemen Stok (Layar 03, 11, 12, 13)
    Route::get('/kategori', [ProdukController::class, 'kategori']);
    Route::get('/produk', [ProdukController::class, 'index']);
    Route::post('/produk/{id_produk}/tambah-stok', [ProdukController::class, 'tambahStok']);
    Route::put('/produk/{id_produk}/edit-stok', [ProdukController::class, 'editStok']);

    // Pengecekan & Pendaftaran Member (Layar 05 & 06)
    Route::get('/member/cek', [MemberController::class, 'cekMember']);
    Route::post('/member', [MemberController::class, 'store']);

    // Transaksi POS, Tukar Poin & Rekap Pembelian (Layar 07, 08, 09, 10)
    Route::post('/transaksi/cek-tukar-poin', [TransaksiController::class, 'cekTukarPoin']);
    Route::post('/transaksi', [TransaksiController::class, 'store']);
    Route::get('/transaksi/rekap', [TransaksiController::class, 'rekap']);
});
