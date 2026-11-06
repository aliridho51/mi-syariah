<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Master data pembayaran:
 * - tahun_ajarans      : periode tahun ajaran (mis. 2026/2027), hanya satu yang aktif
 * - jenis_pembayarans  : SPP, Uang Gedung, Seragam, dll. beserta nominal default & akad syariah
 * - payments           : ditambah relasi ke jenis pembayaran, tahun ajaran, dan bulan tagihan
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahun_ajarans', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 20)->unique(); // contoh: 2026/2027
            $table->boolean('aktif')->default(false);
            $table->timestamps();
        });

        Schema::create('jenis_pembayarans', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();      // contoh: SPP, Uang Gedung
            $table->decimal('nominal', 12, 2)->default(0); // nominal default
            $table->string('akad', 30)->default('ijarah'); // ijarah / wakaf / hibah
            $table->boolean('is_bulanan')->default(false); // true = ditagih per bulan (SPP)
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('jenis_pembayaran_id')->nullable()->after('student_id')
                ->constrained('jenis_pembayarans')->restrictOnDelete();
            $table->foreignId('tahun_ajaran_id')->nullable()->after('jenis_pembayaran_id')
                ->constrained('tahun_ajarans')->restrictOnDelete();
            $table->unsignedTinyInteger('bulan')->nullable()->after('tahun_ajaran_id'); // 1-12
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('jenis_pembayaran_id');
            $table->dropConstrainedForeignId('tahun_ajaran_id');
            $table->dropColumn('bulan');
        });

        Schema::dropIfExists('jenis_pembayarans');
        Schema::dropIfExists('tahun_ajarans');
    }
};

