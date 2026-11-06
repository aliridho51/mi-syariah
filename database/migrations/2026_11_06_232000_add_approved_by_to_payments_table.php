<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sistem Pembayaran Non-Tunai Otomatis ala SIAKAD (Virtual Account BSI & QRIS):
 * - va_number      : Nomor Virtual Account BSI unik per tagihan
 * - payment_channel: Kanal pembayaran otomatis (VA BSI, QRIS Syariah, dll.)
 * - reference_no   : Nomor referensi jurnal bank otomatis (pengganti foto struk manual)
 * - bank_pengirim, nama_pengirim, approved_by : informasi transaksi & verifikasi
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('va_number', 30)->nullable()->unique()->after('transaction_code');
            $table->string('payment_channel', 50)->nullable()->after('payment_method');
            $table->string('reference_no', 50)->nullable()->after('payment_channel');
            $table->string('bank_pengirim', 50)->nullable()->after('reference_no');
            $table->string('nama_pengirim', 100)->nullable()->after('bank_pengirim');
            $table->foreignId('approved_by')->nullable()->after('paid_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn([
                'va_number',
                'payment_channel',
                'reference_no',
                'bank_pengirim',
                'nama_pengirim',
            ]);
        });
    }
};
