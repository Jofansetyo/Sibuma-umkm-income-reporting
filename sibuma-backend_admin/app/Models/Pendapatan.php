<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pendapatan extends Model
{
    use HasFactory;

    // --- PERUBAHAN UTAMA DI SINI ---
    protected $fillable = [
        'usaha_id',
        'jumlah',
        'bulan_tahun',       // Diubah dari 'bulan'
        'jenis_input',       // Tambahan Baru
        'catatan_laporan',   // Tambahan Baru
        'status',            // Tambahan Baru
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
    ];

    /**
     * Get the usaha that owns the pendapatan.
     */
    public function usaha()
    {
        return $this->belongsTo(Usaha::class);
    }

    // FUNGSI CATATAN DIHAPUS (Karena tabel catatans sudah kita gabung ke pendapatans)

    /**
     * Format jumlah as currency
     */
    public function getFormattedJumlahAttribute()
    {
        return 'Rp ' . number_format($this->jumlah, 0, ',', '.');
    }

    /**
     * Format bulan
     */
    public function getFormattedBulanAttribute()
    {
        $months = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        // Pastikan variabel formatnya benar (bulan_tahun)
        list($year, $month) = explode('-', $this->bulan_tahun);

        return $months[$month] . ' ' . $year;
    }
}
