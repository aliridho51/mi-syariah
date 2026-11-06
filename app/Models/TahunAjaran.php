<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajarans';

    protected $fillable = ['nama', 'aktif'];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Tahun ajaran yang sedang aktif (atau null jika belum diatur).
     */
    public static function aktif(): ?self
    {
        return static::where('aktif', true)->first();
    }

    /**
     * Tahun kalender untuk bulan tertentu dalam tahun ajaran ini.
     * Tahun ajaran "2026/2027": Juli–Desember = 2026, Januari–Juni = 2027.
     */
    public function tahunUntukBulan(int $bulan): ?int
    {
        if (! preg_match('/^(\d{4})\s*\/\s*(\d{4})$/', $this->nama, $m)) {
            return null;
        }

        return $bulan >= 7 ? (int) $m[1] : (int) $m[2];
    }
}

