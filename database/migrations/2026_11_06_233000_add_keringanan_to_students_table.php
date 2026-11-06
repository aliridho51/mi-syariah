<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan fitur Keringanan / Beasiswa Syariah (Yatim, Piatu, Dhuafa, Prestasi):
 * - kategori        : Reguler / Yatim / Dhuafa / Beasiswa Prestasi
 * - potongan_persen : Persentase keringanan (0 - 100%) otomatis memotong tagihan SPP
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('kategori', 30)->default('Reguler')->after('kelas');
            $table->unsignedTinyInteger('potongan_persen')->default(0)->after('kategori');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'potongan_persen']);
        });
    }
};
