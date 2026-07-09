<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Kolom ini menyimpan nilai saldo USD yang dikunci saat order dibuat
            // Penting untuk proses refund saat order dibatalkan atau ditutup
            $table->decimal('margin_locked', 30, 12)->default(0)->after('fee');
            
            // Index untuk mempercepat query pencarian order berdasarkan user & status
            // Berguna saat admin approve/reject deposit atau user cek riwayat
            $table->index(['user_id', 'status']); 
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('margin_locked');
            $table->dropIndex(['user_id', 'status']);
        });
    }
};