<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Melindungi data keuangan:
 * sebelumnya menghapus Wali/Siswa akan ikut menghapus (cascade) atau
 * mengosongkan relasi riwayat pembayaran. Sekarang dibuat RESTRICT,
 * sehingga database menolak penghapusan selama masih ada tagihan terkait.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();

            $table->dropForeign(['student_id']);
            $table->foreign('student_id')->references('id')->on('students')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();

            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};

