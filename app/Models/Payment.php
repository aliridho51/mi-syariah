<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    /**
     * Nama bulan, diurutkan sesuai tahun ajaran (Juli s.d. Juni).
     */
    public const BULAN = [
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    ];

    protected $fillable = [
        'user_id',
        'student_id',
        'jenis_pembayaran_id',
        'tahun_ajaran_id',
        'bulan',
        'title',
        'amount',
        'infaq_amount',
        'status',
        'transaction_code',
        'va_number',
        'payment_method',
        'payment_channel',
        'reference_no',
        'bank_pengirim',
        'nama_pengirim',
        'paid_at',
        'approved_by',
        'bukti_transfer',
        'catatan_penolakan',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'infaq_amount' => 'decimal:2',
        'paid_at'      => 'datetime',
        'bulan'        => 'integer',
    ];

    /**
     * Payment dimiliki oleh satu User (Wali/Siswa).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Payment terhubung ke satu Siswa/Murid.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Jenis pembayaran (SPP, Uang Gedung, dll).
     */
    public function jenisPembayaran()
    {
        return $this->belongsTo(JenisPembayaran::class);
    }

    /**
     * Tahun ajaran tagihan.
     */
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    /**
     * Bendahara/Admin yang menyetujui atau menerima pembayaran.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Buat Nomor Virtual Account (VA) BSI MI otomatis seperti SIAKAD Universitas.
     * Format: 900733 (Kode VA BSI MI) + 6 digit NIS + 4 digit ID Tagihan.
     */
    public static function generateVaNumber(?string $nis, int $paymentId): string
    {
        $nisDigits = preg_replace('/\D/', '', (string) $nis);
        $nisPart = str_pad(substr($nisDigits ?: '000000', -6), 6, '0', STR_PAD_LEFT);
        $idPart = str_pad((string) ($paymentId % 10000), 4, '0', STR_PAD_LEFT);

        return '900733' . $nisPart . $idPart;
    }
}
