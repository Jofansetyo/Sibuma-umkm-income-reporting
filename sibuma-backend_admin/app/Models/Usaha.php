<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usaha extends Model
{
    use HasFactory;

    // --- PERUBAHAN UTAMA DI SINI ---
    // Semua kolom baru wajib dimasukkan agar bisa diisi oleh Controller
    protected $fillable = [
        'user_id',
        'nama_usaha',
        'no_wa',               // Tambahan Baru
        'alamat',              // Tambahan Baru
        'kategori',
        'kode_kategori',
        'jenis_pendapatan',    // Tambahan Baru (omzet/nilai_produk)
        'catatan_pendaftaran', // Tambahan Baru
    ];

    /**
     * Get the user that owns the usaha.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the pendapatans for the usaha.
     */
    public function pendapatans()
    {
        return $this->hasMany(Pendapatan::class);
    }

    /**
     * Get label nama pendapatan based on jenis_pendapatan (Bukan lagi kategori)
     */
    public function getLabelPendapatanAttribute()
    {
        // Disesuaikan karena sekarang user milih langsung 'omzet' atau 'nilai_produk'
        if ($this->jenis_pendapatan === 'omzet') {
            return 'Omset';
        } else {
            return 'Nilai Produksi';
        }
    }

    /**
     * Get full display name with code
     */
    public function getFullNameAttribute()
    {
        $kode = $this->kode_kategori ? ' (' . $this->kode_kategori . ')' : '';
        return $this->nama_usaha . $kode;
    }
}
