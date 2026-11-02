<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kasir extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'kasir';
    protected $primaryKey = 'id_kasir';

    protected $fillable = [
        'kode_kasir',
        'nama_kasir',
        'pin',
    ];

    protected $hidden = [
        'pin',
    ];

    protected function casts(): array
    {
        return [
            'pin' => 'hashed',
        ];
    }

    public function transaksiPenjualan(): HasMany
    {
        return $this->hasMany(TransaksiPenjualan::class, 'id_kasir', 'id_kasir');
    }
}
