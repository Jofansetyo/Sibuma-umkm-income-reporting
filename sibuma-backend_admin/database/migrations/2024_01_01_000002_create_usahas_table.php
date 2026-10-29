<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usahas', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel users: Jika user dihapus, data usaha juga terhapus
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // --- Data Profil Perusahaan ---
            $table->string('nama_usaha');
            $table->string('no_wa', 20); // Wajib: Nomor WhatsApp
            $table->text('alamat'); // Wajib: Alamat Lengkap
            
            // Wajib: 3 Kategori Utama BPS
            $table->enum('kategori', ['industri', 'jasa', 'perdagangan']); 
            
            // Kode Kategori (Opsional, tapi bagus untuk pelaporan BPS: IND, JSA, PDG)
            $table->string('kode_kategori', 3)->nullable(); 
            
            // Wajib: Pilihan pelaporan pendapatan
            $table->enum('jenis_pendapatan', ['omzet', 'nilai_produk']); 
            
            // Opsional: Catatan tambahan dari responden saat mendaftar
            $table->text('catatan_pendaftaran')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usahas');
    }
};
