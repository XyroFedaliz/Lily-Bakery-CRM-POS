<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Member extends Model
{
    protected $table = 'member';
    protected $primaryKey = 'id_member';

    protected $fillable = [
        'kode_member',
        'no_hp',
        'nama_member',
        'tanggal_daftar',
    ];

    // Otomatis menyertakan atribut virtual `saldo_poin` saat model diubah ke JSON API
    protected $appends = ['saldo_poin'];

    protected function casts(): array
    {
        return [
            'tanggal_daftar' => 'date',
        ];
    }

    public function transaksiPenjualan(): HasMany
    {
        return $this->hasMany(TransaksiPenjualan::class, 'id_member', 'id_member');
    }

    public function riwayatPoin(): HasManyThrough
    {
        return $this->hasManyThrough(
            RiwayatPoin::class,
            TransaksiPenjualan::class,
            'id_member',     // Foreign key di tabel transaksi_penjualan
            'id_transaksi',  // Foreign key di tabel riwayat_poin
            'id_member',     // Local key di tabel member
            'id_transaksi'   // Local key di tabel transaksi_penjualan
        );
    }

    /**
     * Menghitung saldo poin secara dinamis dari tabel riwayat_poin
     * (Total poin 'tambah' dikurangi total poin 'tukar').
     */
    public function getSaldoPoinAttribute(): int
    {
        $poinTambah = $this->riwayatPoin()->where('jenis_poin', 'tambah')->sum('jumlah_poin');
        $poinTukar  = $this->riwayatPoin()->where('jenis_poin', 'tukar')->sum('jumlah_poin');

        return (int) ($poinTambah - $poinTukar);
    }
}

