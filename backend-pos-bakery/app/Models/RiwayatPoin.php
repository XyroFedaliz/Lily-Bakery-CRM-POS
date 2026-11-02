<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatPoin extends Model
{
    protected $table = 'riwayat_poin';
    protected $primaryKey = 'id_poin';

    protected $fillable = [
        'id_transaksi',
        'jenis_poin',
        'jumlah_poin',
        'keterangan',
        'waktu',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_poin' => 'integer',
            'waktu'       => 'datetime',
        ];
    }

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'id_transaksi', 'id_transaksi');
    }
}
