<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('bukti_transfer')->nullable()->after('payment_method');
            $table->text('catatan_penolakan')->nullable()->after('bukti_transfer');
            
            // Karena SQLite tidak mendukung perubahan tipe kolom enum secara langsung,
            // kita ubah status di level code saja, tapi migrasi ini memastikan kolom baru terbuat.
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['bukti_transfer', 'catatan_penolakan']);
        });
    }
};
