<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendapatans', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel usahas
            $table->foreignId('usaha_id')->constrained('usahas')->onDelete('cascade');
            
            // Angka pendapatan (Decimal 15,2 cocok untuk nilai Rupiah yang besar)
            $table->decimal('jumlah', 15, 2);
            
            // Format Bulan Laporan (Contoh: "2026-02")
            $table->string('bulan_tahun', 7); 
            
            // Menyimpan apakah nilai ini adalah Omzet atau Nilai Produk
            $table->enum('jenis_input', ['omzet', 'nilai_produk']);
            
            // Opsional: Catatan dari responden untuk bulan tersebut
            $table->text('catatan_laporan')->nullable();
            
            // Status data: draft (belum dikirim), terkirim (masuk ke admin)
            $table->enum('status', ['draft', 'terkirim'])->default('terkirim');
            
            $table->timestamps();

            // Index untuk mempercepat loading grafik/tabel di Flutter
            $table->index('bulan_tahun');
            $table->index(['usaha_id', 'bulan_tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendapatans');
    }
};
