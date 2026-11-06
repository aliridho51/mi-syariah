<?php

use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\WaliController;
use Illuminate\Support\Facades\Route;

// Halaman publik - redirect ke halaman pembayaran (otomatis ke login jika belum auth)
Route::get('/', function () {
    return redirect()->route('payments.index');
});

// Dashboard bawaan Breeze diarahkan ke halaman utama sistem
Route::get('/dashboard', function () {
    return redirect()->route('payments.index');
})->middleware('auth')->name('dashboard');

/*
|--------------------------------------------------------------------------
| Semua role yang sudah login (admin, kepala_madrasah, wali_murid)
|--------------------------------------------------------------------------
| Data yang tampil tetap difilter di controller sesuai role.
*/
Route::middleware('auth')->group(function () {
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{id}/bukti', [PaymentController::class, 'bukti'])->name('payments.bukti');
    Route::get('/payments/{id}/kwitansi', [PaymentController::class, 'kwitansi'])->name('payments.kwitansi');
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');

    // Profile (bawaan Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin / Bendahara
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Tagihan & verifikasi pembayaran
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::post('/payments/{id}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
    Route::post('/payments/{id}/reject', [PaymentController::class, 'reject'])->name('payments.reject');
    Route::post('/payments/{id}/pay-cash', [PaymentController::class, 'payCash'])->name('payments.payCash');
    Route::delete('/payments/{id}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    // Kelola data murid
    Route::resource('students', StudentController::class)->only(['store', 'update', 'destroy']);

    // Kelola akun wali murid
    Route::resource('walis', WaliController::class)->only(['store', 'update', 'destroy']);

    // Master Data (Jenis Pembayaran & Tahun Ajaran)
    Route::get('/master', [MasterDataController::class, 'index'])->name('master.index');
    Route::post('/master/reset-data', [MasterDataController::class, 'resetData'])->name('master.resetData');
    Route::post('/master/jenis', [MasterDataController::class, 'storeJenis'])->name('master.jenis.store');
    Route::put('/master/jenis/{id}', [MasterDataController::class, 'updateJenis'])->name('master.jenis.update');
    Route::delete('/master/jenis/{id}', [MasterDataController::class, 'destroyJenis'])->name('master.jenis.destroy');
    Route::post('/master/tahun', [MasterDataController::class, 'storeTahun'])->name('master.tahun.store');
    Route::put('/master/tahun/{id}', [MasterDataController::class, 'updateTahun'])->name('master.tahun.update');
    Route::post('/master/tahun/{id}/aktifkan', [MasterDataController::class, 'aktifkanTahun'])->name('master.tahun.aktifkan');
    Route::delete('/master/tahun/{id}', [MasterDataController::class, 'destroyTahun'])->name('master.tahun.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin & Kepala Madrasah (Kepala hanya melihat / monitoring)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,kepala_madrasah'])->group(function () {
    Route::get('/walis', [WaliController::class, 'index'])->name('walis.index');
    Route::get('/laporan', [PaymentController::class, 'laporan'])->name('payments.laporan');
    Route::get('/laporan/export-csv', [PaymentController::class, 'exportCsv'])->name('payments.exportCsv');
});

/*
|--------------------------------------------------------------------------
| Wali Murid
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:wali_murid'])->group(function () {
    Route::post('/payments/{id}/pay', [PaymentController::class, 'pay'])->name('payments.pay');
    Route::post('/payments/{id}/pay-auto', [PaymentController::class, 'payAutomatic'])->name('payments.payAuto');
    Route::post('/payments/{id}/snap-token', [PaymentController::class, 'createSnapToken'])->name('payments.snapToken');
});

// Webhook otomatis dari Midtrans Payment Gateway (tanpa session login, diverifikasi SHA-512 signature)
Route::post('/webhook/midtrans', [PaymentController::class, 'midtransWebhook'])->name('webhook.midtrans');

require __DIR__.'/auth.php';
