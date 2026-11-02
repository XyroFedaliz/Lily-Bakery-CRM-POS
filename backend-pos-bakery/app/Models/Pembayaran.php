<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';

    protected $fillable = [
        'id_transaksi',
        'metode_pembayaran',
        'jumlah_bayar',
        'status',
        'waktu_pembayaran',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_bayar'     => 'decimal:2',
            'waktu_pembayaran' => 'datetime',
        ];
    }

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'id_transaksi', 'id_transaksi');
    }
}
