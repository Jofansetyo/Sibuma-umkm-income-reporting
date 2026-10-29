<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pendapatan;
use App\Models\Usaha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PendapatanController extends Controller
{
    /**
     * Get all pendapatan for authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'responden') {
            $usaha = Usaha::where('user_id', $user->id)->first();

            if (!$usaha) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usaha tidak ditemukan'
                ], 404);
            }

            $pendapatans = Pendapatan::where('usaha_id', $usaha->id)
                ->orderBy('bulan_tahun', 'desc')
                ->get()
                ->map(fn($p) => $this->formatPendapatan($p));
        } else {
            $pendapatans = Pendapatan::with(['usaha.user'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($p) => $this->formatPendapatan($p));
        }

        return response()->json([
            'success' => true,
            'data' => $pendapatans
        ]);
    }

    /**
     * Store new pendapatan (Input Bulanan dari Flutter)
     */
    public function store(Request $request)
    {
        $request->validate([
            'jumlah'          => 'required|numeric|min:0',
            'bulan_tahun'     => 'required|date_format:Y-m',
            'catatan_laporan' => 'nullable|string',
        ]);

        $user  = $request->user();
        $usaha = Usaha::where('user_id', $user->id)->first();

        if (!$usaha) {
            return response()->json([
                'success' => false,
                'message' => 'Usaha tidak ditemukan'
            ], 404);
        }

        // Cek duplikasi bulan
        $existing = Pendapatan::where('usaha_id', $usaha->id)
            ->where('bulan_tahun', $request->bulan_tahun)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan untuk bulan ini sudah ada. Silakan edit data yang sudah ada.'
            ], 422);
        }

        $pendapatan = Pendapatan::create([
            'usaha_id'        => $usaha->id,
            'jumlah'          => $request->jumlah,
            'bulan_tahun'     => $request->bulan_tahun,
            'jenis_input'     => $usaha->jenis_pendapatan,
            'catatan_laporan' => $request->catatan_laporan ?? null,
            'status'          => 'terkirim',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dilaporkan ke BPS',
            'data'    => $this->formatPendapatan($pendapatan)
        ], 201);
    }

    /**
     * Statistics pendapatan untuk user yang login
     */
    public function statistics(Request $request)
    {
        $user  = $request->user();
        $usaha = Usaha::where('user_id', $user->id)->first();

        if (!$usaha) {
            return response()->json([
                'success' => false,
                'message' => 'Usaha tidak ditemukan'
            ], 404);
        }

        $pendapatans = Pendapatan::where('usaha_id', $usaha->id)->get();

        if ($pendapatans->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'total'         => 0,
                    'rata_rata'     => 0,
                    'tertinggi'     => 0,
                    'terendah'      => 0,
                    'jumlah_bulan'  => 0,
                    'bulan_terbaru' => null,
                    'tren'          => [],
                ]
            ]);
        }

        // Data tren bulanan (urut ASC untuk grafik)
        $tren = Pendapatan::where('usaha_id', $usaha->id)
            ->orderBy('bulan_tahun', 'asc')
            ->get()
            ->map(fn($p) => [
                'bulan_tahun'    => $p->bulan_tahun,
                'bulan_label'    => $p->formatted_bulan,
                'jumlah'         => (float) $p->jumlah,
                'jumlah_label'   => $p->formatted_jumlah,
                'jenis_input'    => $p->jenis_input,
                'status'         => $p->status,
            ]);

        $latest = Pendapatan::where('usaha_id', $usaha->id)
            ->orderBy('bulan_tahun', 'desc')
            ->first();

        // Hitung pertumbuhan bulan terakhir vs sebelumnya
        $growth = null;
        if ($tren->count() >= 2) {
            $trenValues = $tren->values();
            $last  = $trenValues->last()['jumlah'];
            $prev  = $trenValues->get($trenValues->count() - 2)['jumlah'] ?? null;
            if ($prev && $prev > 0) {
                $growth = round((($last - $prev) / $prev) * 100, 2);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total'            => (float) $pendapatans->sum('jumlah'),
                'rata_rata'        => round($pendapatans->avg('jumlah'), 2),
                'tertinggi'        => (float) $pendapatans->max('jumlah'),
                'terendah'         => (float) $pendapatans->min('jumlah'),
                'jumlah_bulan'     => $pendapatans->count(),
                'bulan_terbaru'    => $latest ? $latest->formatted_bulan : null,
                'pertumbuhan_pct'  => $growth,
                'tren'             => $tren,
            ]
        ]);
    }

    /**
     * Update pendapatan (Edit dari Flutter)
     */
    public function update(Request $request, $id)
    {
        $user  = $request->user();
        $usaha = Usaha::where('user_id', $user->id)->first();

        if (!$usaha) {
            return response()->json([
                'success' => false,
                'message' => 'Usaha tidak ditemukan'
            ], 404);
        }

        $pendapatan = Pendapatan::where('id', $id)
            ->where('usaha_id', $usaha->id)
            ->first();

        if (!$pendapatan) {
            return response()->json([
                'success' => false,
                'message' => 'Data pendapatan tidak ditemukan atau bukan milik Anda'
            ], 404);
        }

        $request->validate([
            'jumlah'          => 'required|numeric|min:0',
            'catatan_laporan' => 'nullable|string',
        ]);

        $pendapatan->update([
            'jumlah'          => $request->jumlah,
            'catatan_laporan' => $request->catatan_laporan ?? $pendapatan->catatan_laporan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
            'data'    => $this->formatPendapatan($pendapatan->fresh())
        ]);
    }

    /**
     * Delete pendapatan (Hapus dari Flutter)
     */
    public function destroy(Request $request, $id)
    {
        $user  = $request->user();
        $usaha = Usaha::where('user_id', $user->id)->first();

        if (!$usaha) {
            return response()->json([
                'success' => false,
                'message' => 'Usaha tidak ditemukan'
            ], 404);
        }

        $pendapatan = Pendapatan::where('id', $id)
            ->where('usaha_id', $usaha->id)
            ->first();

        if (!$pendapatan) {
            return response()->json([
                'success' => false,
                'message' => 'Data pendapatan tidak ditemukan atau bukan milik Anda'
            ], 404);
        }

        $pendapatan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus'
        ]);
    }

    /**
     * Helper: format satu pendapatan untuk response
     */
    private function formatPendapatan(Pendapatan $p): array
    {
        return [
            'id'              => $p->id,
            'usaha_id'        => $p->usaha_id,
            'jumlah'          => (float) $p->jumlah,
            'jumlah_label'    => $p->formatted_jumlah,
            'bulan_tahun'     => $p->bulan_tahun,
            'bulan_label'     => $p->formatted_bulan,
            'jenis_input'     => $p->jenis_input,
            'catatan_laporan' => $p->catatan_laporan,
            'status'          => $p->status,
            'created_at'      => $p->created_at,
        ];
    }
}