<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisPembayaran extends Model
{
    protected $table = 'jenis_pembayarans';

    /**
     * Pilihan akad syariah untuk setiap jenis pembayaran.
     */
    public const AKAD = [
        'ijarah' => 'Ijarah (upah jasa pendidikan)',
        'wakaf'  => 'Wakaf (sarana & gedung)',
        'hibah'  => 'Hibah (pemberian/kegiatan)',
        'jual_beli' => "Jual Beli / Murabahah (seragam, buku)",
    ];

    protected $fillable = ['nama', 'nominal', 'akad', 'is_bulanan', 'keterangan'];

    protected $casts = [
        'nominal'    => 'decimal:2',
        'is_bulanan' => 'boolean',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getAkadLabelAttribute(): string
    {
        return self::AKAD[$this->akad] ?? ucfirst($this->akad);
    }
}

