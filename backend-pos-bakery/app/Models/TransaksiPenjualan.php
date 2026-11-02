<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TransaksiPenjualan extends Model
{
    protected $table = 'transaksi_penjualan';
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'nomor_transaksi',
        'id_kasir',
        'id_member',
        'waktu_transaksi',
        'diskon_transaksi',
    ];

    // Otomatis menyertakan subtotal dan grand_total saat dikirim ke API
    protected $appends = ['subtotal', 'grand_total'];

    protected function casts(): array
    {
        return [
            'waktu_transaksi'  => 'datetime',
            'diskon_transaksi' => 'decimal:2',
        ];
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(Kasir::class, 'id_kasir', 'id_kasir');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'id_member', 'id_member');
    }

    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi', 'id_transaksi');
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'id_transaksi', 'id_transaksi');
    }

    public function riwayatPoin(): HasMany
    {
        return $this->hasMany(RiwayatPoin::class, 'id_transaksi', 'id_transaksi');
    }

    /**
     * Menghitung subtotal otomatis dari seluruh item di detail_transaksi.
     */
    public function getSubtotalAttribute(): float
    {
        return (float) $this->detailTransaksi->sum(function ($item) {
            return ($item->jumlah * $item->harga_satuan) - $item->diskon_item;
        });
    }

    /**
     * Menghitung Grand Total (Subtotal dikurangi diskon_transaksi dari penukaran poin).
     */
    public function getGrandTotalAttribute(): float
    {
        return max(0, $this->subtotal - (float) $this->diskon_transaksi);
    }
}