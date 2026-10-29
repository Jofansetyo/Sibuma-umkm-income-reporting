<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Usaha;
use App\Models\Pendapatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsaha        = Usaha::count();
        $totalResponden    = User::where('role', 'responden')->count();
        $totalPendapatan   = Pendapatan::sum('jumlah');
        $totalEntries      = Pendapatan::count();

        $usahaByKategori = Usaha::select('kategori', DB::raw('count(*) as total'))
            ->groupBy('kategori')->get();

        $recentPendapatan = Pendapatan::with(['usaha.user'])
            ->orderBy('created_at', 'desc')->limit(10)->get();

        $chartData = Pendapatan::select('bulan_tahun', DB::raw('SUM(jumlah) as total'))
            ->groupBy('bulan_tahun')->orderBy('bulan_tahun', 'asc')->limit(12)->get();

        $pendapatanByKategori = Pendapatan::join('usahas', 'pendapatans.usaha_id', '=', 'usahas.id')
            ->select('usahas.kategori', DB::raw('SUM(pendapatans.jumlah) as total'))
            ->groupBy('usahas.kategori')->get();

        return view('admin.dashboard', compact(
            'totalUsaha', 'totalResponden', 'totalPendapatan', 'totalEntries',
            'usahaByKategori', 'recentPendapatan', 'chartData', 'pendapatanByKategori'
        ));
    }

    /**
     * Data usaha dengan filter search + kategori + pagination
     */
    public function usaha(Request $request)
    {
        $query = Usaha::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_usaha', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")
                                                    ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $usahas = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('admin.usaha', compact('usahas'));
    }

    public function pendapatan(Request $request)
    {
        $query = Pendapatan::with(['usaha.user']);

        if ($request->filled('kategori')) {
            $query->whereHas('usaha', fn($q) => $q->where('kategori', $request->kategori));
        }

        if ($request->filled('bulan')) {
            $query->where('bulan_tahun', $request->bulan);
        }

        if ($request->filled('dari_tanggal') && $request->filled('sampai_tanggal')) {
            $query->whereBetween('created_at', [$request->dari_tanggal, $request->sampai_tanggal]);
        }

        $pendapatans = $query->orderBy('bulan_tahun', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $daftarBulan = Pendapatan::select('bulan_tahun')
            ->distinct()->whereNotNull('bulan_tahun')
            ->orderBy('bulan_tahun', 'desc')->pluck('bulan_tahun');

        return view('admin.pendapatan', compact('pendapatans', 'daftarBulan'));
    }

    public function catatan()
    {
        $catatans = Pendapatan::with(['usaha.user'])
            ->whereNotNull('catatan_laporan')
            ->where('catatan_laporan', '!=', '')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.catatan', compact('catatans'));
    }

    /**
     * Form edit pendapatan
     */
    public function editPendapatan($id)
    {
        $pendapatan = Pendapatan::with('usaha.user')->findOrFail($id);
        return view('admin.edit-pendapatan', compact('pendapatan'));
    }

    /**
     * Simpan perubahan pendapatan
     */
    public function updatePendapatan(Request $request, $id)
    {
        $pendapatan = Pendapatan::findOrFail($id);

        $request->validate([
            'jumlah'          => 'required|numeric|min:0',
            'bulan_tahun'     => 'required|date_format:Y-m',
            'catatan_laporan' => 'nullable|string',
            'status'          => 'required|in:draft,terkirim',
        ]);

        $existing = Pendapatan::where('usaha_id', $pendapatan->usaha_id)
            ->where('bulan_tahun', $request->bulan_tahun)
            ->where('id', '!=', $id)
            ->first();

        if ($existing) {
            return back()->withErrors(['bulan_tahun' => 'Laporan untuk bulan ini sudah ada.'])->withInput();
        }

        $pendapatan->update([
            'jumlah'          => $request->jumlah,
            'bulan_tahun'     => $request->bulan_tahun,
            'catatan_laporan' => $request->catatan_laporan,
            'status'          => $request->status,
        ]);

        return redirect()->route('admin.pendapatan')
            ->with('success', 'Data pendapatan berhasil diperbarui.');
    }

    /**
     * Hapus data pendapatan
     */
    public function deletePendapatan($id)
    {
        $pendapatan = Pendapatan::findOrFail($id);
        $pendapatan->delete();

        return redirect()->back()->with('success', 'Data pendapatan berhasil dihapus.');
    }

    /**
     * Hapus data usaha beserta pendapatan dan akun responden
     */
    public function deleteUsaha($id)
    {
        $usaha = Usaha::with('user')->findOrFail($id);
        $user  = $usaha->user;

        $usaha->delete();

        if ($user && $user->role === 'responden') {
            $user->delete();
        }

        return redirect()->route('admin.usaha')
            ->with('success', 'Data usaha dan akun responden berhasil dihapus.');
    }

    /**
     * Halaman Statistik Global (dengan filter per kategori: industri/perdagangan/jasa)
     */
    public function statistik(Request $request)
    {
        $kategoriAktif = $request->get('kategori', 'semua');
        $kategoriValid = ['industri', 'perdagangan', 'jasa'];
        if (!in_array($kategoriAktif, $kategoriValid)) {
            $kategoriAktif = 'semua';
        }

        // Helper: scope query berdasarkan kategori aktif (jika bukan 'semua')
        $scopePendapatan = function ($query) use ($kategoriAktif) {
            if ($kategoriAktif !== 'semua') {
                $query->whereHas('usaha', fn($q) => $q->where('kategori', $kategoriAktif));
            }
            return $query;
        };
        $scopeUsaha = function ($query) use ($kategoriAktif) {
            if ($kategoriAktif !== 'semua') {
                $query->where('kategori', $kategoriAktif);
            }
            return $query;
        };

        // Jumlah usaha per kategori (buat badge di tab)
        $jumlahPerKategori = Usaha::select('kategori', DB::raw('count(*) as total'))
            ->groupBy('kategori')->pluck('total', 'kategori');

        // Total keseluruhan (sudah ter-filter kategori)
        $totalPendapatan = $scopePendapatan(Pendapatan::query())->sum('jumlah');
        $totalUsaha       = $scopeUsaha(Usaha::query())->count();
        $totalLaporan     = $scopePendapatan(Pendapatan::query())->count();

        $totalResponden = $kategoriAktif === 'semua'
            ? User::where('role', 'responden')->count()
            : Usaha::where('kategori', $kategoriAktif)->distinct('user_id')->count('user_id');

        // Tertinggi & terendah single entry
        $tertinggi = $scopePendapatan(Pendapatan::with('usaha'))->orderBy('jumlah', 'desc')->first();
        $terendah  = $scopePendapatan(Pendapatan::with('usaha'))->where('jumlah', '>', 0)->orderBy('jumlah', 'asc')->first();

        // Rata-rata per laporan
        $rataRata = $totalLaporan > 0 ? $totalPendapatan / $totalLaporan : 0;

        // Tren bulanan (12 bulan terakhir, ter-filter kategori)
        $trenQuery = Pendapatan::select('bulan_tahun', DB::raw('SUM(jumlah) as total'), DB::raw('COUNT(*) as jumlah_laporan'));
        if ($kategoriAktif !== 'semua') {
            $trenQuery->whereHas('usaha', fn($q) => $q->where('kategori', $kategoriAktif));
        }
        $trenBulanan = $trenQuery->groupBy('bulan_tahun')->orderBy('bulan_tahun', 'asc')->limit(12)->get();

        // Pertumbuhan bulan terakhir vs sebelumnya
        $pertumbuhan = null;
        $pertumbuhanLabel = '-';
        $pertumbuhanPositif = true;
        if ($trenBulanan->count() >= 2) {
            $arr  = $trenBulanan->values();
            $last = $arr->last()->total;
            $prev = $arr->get($arr->count() - 2)->total ?? null;
            if ($prev && $prev > 0) {
                $pertumbuhan = round((($last - $prev) / $prev) * 100, 1);
                $pertumbuhanPositif = $pertumbuhan >= 0;
                $pertumbuhanLabel = ($pertumbuhan >= 0 ? '+' : '') . $pertumbuhan . '%';
            }
        }

        // Statistik per kategori (perbandingan, dipakai di tab "Semua")
        $statKategori = Pendapatan::join('usahas', 'pendapatans.usaha_id', '=', 'usahas.id')
            ->select(
                'usahas.kategori',
                DB::raw('SUM(pendapatans.jumlah) as total'),
                DB::raw('AVG(pendapatans.jumlah) as rata_rata'),
                DB::raw('MAX(pendapatans.jumlah) as tertinggi'),
                DB::raw('MIN(pendapatans.jumlah) as terendah'),
                DB::raw('COUNT(pendapatans.id) as jumlah_laporan')
            )
            ->groupBy('usahas.kategori')
            ->get();

        // Top 5 usaha (ter-filter kategori)
        $topUsahaQuery = Usaha::with('user')->withSum('pendapatans', 'jumlah');
        $topUsahaQuery = $scopeUsaha($topUsahaQuery);
        $topUsaha = $topUsahaQuery->orderByDesc('pendapatans_sum_jumlah')->limit(5)->get();

        // Usaha yang belum pernah lapor (ter-filter kategori)
        $belumLaporQuery = Usaha::with('user')->doesntHave('pendapatans');
        $belumLaporQuery = $scopeUsaha($belumLaporQuery);
        $belumLapor = $belumLaporQuery->get();

        return view('admin.statistik', compact(
            'totalPendapatan', 'totalUsaha', 'totalResponden', 'totalLaporan',
            'tertinggi', 'terendah', 'rataRata',
            'trenBulanan', 'pertumbuhan', 'pertumbuhanLabel', 'pertumbuhanPositif',
            'statKategori', 'topUsaha', 'belumLapor',
            'kategoriAktif', 'jumlahPerKategori'
        ));
    }

    public function detailUsaha($id)
    {
        $usaha = Usaha::with(['user', 'pendapatans'])->findOrFail($id);

        $jumlahData = $usaha->pendapatans->count();
        $statistics = [
            'total'   => $usaha->pendapatans->sum('jumlah'),
            'average' => $jumlahData > 0 ? $usaha->pendapatans->avg('jumlah') : 0,
            'count'   => $jumlahData,
        ];

        $pendapatans = $usaha->pendapatans()->orderBy('bulan_tahun')->get();
        $bulanData   = [];
        $nominalData = [];
        $bulanIndo   = [
            '01' => 'Januari',  '02' => 'Februari', '03' => 'Maret',
            '04' => 'April',    '05' => 'Mei',       '06' => 'Juni',
            '07' => 'Juli',     '08' => 'Agustus',   '09' => 'September',
            '10' => 'Oktober',  '11' => 'November',  '12' => 'Desember',
        ];

        foreach ($pendapatans as $p) {
            if ($p->bulan_tahun) {
                $parts = explode('-', $p->bulan_tahun);
                if (count($parts) == 2) {
                    $bulanData[]   = ($bulanIndo[$parts[1]] ?? $parts[1]) . ' ' . $parts[0];
                    $nominalData[] = (int) $p->jumlah;
                }
            }
        }

        $grafikKosong = empty($bulanData);

        return view('admin.detail-usaha', compact('usaha', 'statistics', 'bulanData', 'nominalData', 'grafikKosong'));
    }
}