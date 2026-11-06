<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama',
        'nis',
        'kelas',
        'kategori',
        'potongan_persen',
    ];

    /**
     * Siswa dimiliki oleh satu Wali Murid (User).
     */
    public function wali()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Siswa memiliki banyak tagihan/pembayaran.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
