<?php

namespace Database\Seeders;

use App\Models\JenisPembayaran;
use App\Models\Payment;
use App\Models\Student;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Kosongkan SEMUA data (Tagihan, Murid, Wali, Jenis Pembayaran, dan Tahun Ajaran)
        // agar Admin benar-benar mulai menginput semuanya dari nol.
        Schema::disableForeignKeyConstraints();
        Payment::truncate();
        Student::truncate();
        JenisPembayaran::truncate();
        TahunAjaran::truncate();
        User::where('role', 'wali_murid')->delete();
        Schema::enableForeignKeyConstraints();

        // 1. Akun Login Bendahara / Admin
        User::updateOrCreate(
            ['email' => 'admin@mi.sch.id'],
            [
                'name' => 'Bendahara MI',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // 2. Akun Login Kepala Madrasah
        User::updateOrCreate(
            ['email' => 'kepala@mi.sch.id'],
            [
                'name' => 'Kepala Madrasah',
                'password' => Hash::make('password123'),
                'role' => 'kepala_madrasah',
            ]
        );
    }
}
