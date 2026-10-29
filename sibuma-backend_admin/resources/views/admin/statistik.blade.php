@extends('layouts.admin')

@section('title', 'Statistik')
@section('page-title', 'Statistik')
@section('page-description', 'Ringkasan dan analisis data pendapatan seluruh usaha')

@section('content')

@php
    $fmt = function($val) {
        if ($val >= 1000000000) return 'Rp ' . number_format($val / 1000000000, 2, ',', '.') . ' M';
        if ($val >= 1000000)    return 'Rp ' . number_format($val / 1000000, 1, ',', '.') . ' Jt';
        return 'Rp ' . number_format($val, 0, ',', '.');
    };
@endphp

{{-- ===== JUDUL DINAMIS SESUAI KATEGORI AKTIF ===== --}}
<div class="flex items-center justify-between mb-6 flex-wrap gap-2">
    <h2 class="text-lg font-bold text-white">
        <i class="fas fa-chart-pie text-blue-200 mr-1"></i>
        @if($kategoriAktif === 'semua')
            Ringkasan Statistik Seluruh Kategori
        @else
            Statistik Kategori {{ ucfirst($kategoriAktif) }}
        @endif
    </h2>
    @if($kategoriAktif !== 'semua')
        <a href="{{ route('admin.statistik') }}"
           class="text-xs font-semibold text-white bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg flex items-center gap-1 transition">
            <i class="fas fa-arrow-left"></i> Lihat Semua Kategori
        </a>
    @endif
</div>

{{-- ===== KARTU RINGKASAN ATAS ===== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded-xl shadow-md p-5 card-hover transition duration-300">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Total Pendapatan</p>
        <h3 class="text-xl font-bold text-gray-800">{{ $fmt($totalPendapatan) }}</h3>
        <p class="text-xs text-gray-400 mt-1">{{ $kategoriAktif === 'semua' ? 'Semua usaha' : ucfirst($kategoriAktif) }}</p>
        <div class="mt-3 w-10 h-10 gradient-primary rounded-xl flex items-center justify-center">
            <i class="fas fa-money-bill-wave text-white text-sm"></i>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-5 card-hover transition duration-300">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Rata-rata/Bulan</p>
        <h3 class="text-xl font-bold text-gray-800">{{ $fmt($rataRata) }}</h3>
        <p class="text-xs text-gray-400 mt-1">Per entri laporan</p>
        <div class="mt-3 w-10 h-10 gradient-success rounded-xl flex items-center justify-center">
            <i class="fas fa-chart-line text-white text-sm"></i>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-5 card-hover transition duration-300">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Total Laporan</p>
        <h3 class="text-2xl font-bold text-gray-800">{{ $totalLaporan }}</h3>
        <p class="text-xs text-gray-400 mt-1">Entri pendapatan</p>
        <div class="mt-3 w-10 h-10 gradient-warning rounded-xl flex items-center justify-center">
            <i class="fas fa-file-alt text-white text-sm"></i>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-5 card-hover transition duration-300">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Pertumbuhan</p>
        <h3 class="text-2xl font-bold {{ $pertumbuhanPositif ? 'text-green-600' : 'text-red-500' }}">
            {{ $pertumbuhanLabel }}
        </h3>
        <p class="text-xs text-gray-400 mt-1">Bulan terakhir vs sebelumnya</p>
        <div class="mt-3 w-10 h-10 {{ $pertumbuhanPositif ? 'bg-gradient-to-br from-green-400 to-green-600' : 'bg-gradient-to-br from-red-400 to-red-600' }} rounded-xl flex items-center justify-center">
            <i class="fas {{ $pertumbuhanPositif ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }} text-white text-sm"></i>
        </div>
    </div>

</div>

{{-- ===== TREN BULANAN + KATEGORI ===== --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    {{-- Grafik Tren --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-1">
            <i class="fas fa-chart-bar text-blue-600 mr-2"></i>Tren Pendapatan Bulanan
        </h3>
        <p class="text-xs text-gray-400 mb-4">
            {{ $kategoriAktif === 'semua' ? 'Total semua usaha per bulan' : 'Total usaha kategori ' . ucfirst($kategoriAktif) . ' per bulan' }}
        </p>
        @if($trenBulanan->isEmpty())
            <div class="flex flex-col items-center justify-center h-48 text-gray-300">
                <i class="fas fa-chart-bar text-5xl mb-3"></i>
                <p class="text-sm">Belum ada data</p>
            </div>
        @else
        <div style="position:relative; height:260px; width:100%;">
            <canvas id="trenChart"></canvas>
        </div>
        @endif
    </div>

    {{-- Statistik per Kategori --}}
    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">
            <i class="fas fa-layer-group text-blue-600 mr-2"></i>Per Kategori
        </h3>
        @php
            $katColors = ['perdagangan' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'bar' => 'bg-blue-500'],
                          'industri'    => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'bar' => 'bg-purple-500'],
                          'jasa'        => ['bg' => 'bg-green-100', 'text' => 'text-green-600', 'bar' => 'bg-green-500']];
            $maxTotal = $statKategori->max('total') ?: 1;
        @endphp
        <div class="space-y-4">
            @forelse($statKategori as $kat)
            @php
                $c   = $katColors[$kat->kategori] ?? ['bg'=>'bg-gray-100','text'=>'text-gray-600','bar'=>'bg-gray-400'];
                $pct = round(($kat->total / $maxTotal) * 100);
                $label = $kat->kategori == 'industri' ? 'Industri / Manufaktur' : ucfirst($kat->kategori);
            @endphp
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $c['bg'] }} {{ $c['text'] }}">{{ $label }}</span>
                    <span class="text-xs font-bold text-gray-700">{{ $fmt($kat->total) }}</span>
                </div>
                <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-2 {{ $c['bar'] }} rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                </div>
                <div class="flex justify-between mt-1 text-xs text-gray-400">
                    <span>Rata-rata: {{ $fmt($kat->rata_rata) }}</span>
                    <span>{{ $kat->jumlah_laporan }} laporan</span>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-4">Belum ada data</p>
            @endforelse
        </div>
    </div>

</div>

{{-- ===== TERTINGGI & TERENDAH ===== --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-base font-bold text-gray-800 mb-4">
            <i class="fas fa-trophy text-yellow-500 mr-2"></i>Pendapatan Tertinggi
        </h3>
        @if($tertinggi)
        <div class="flex items-center gap-4 p-4 bg-green-50 rounded-xl border border-green-100">
            <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-arrow-up text-white"></i>
            </div>
            <div>
                <p class="font-bold text-gray-800">{{ $tertinggi->usaha->nama_usaha }}</p>
                <p class="text-xs text-gray-500">{{ $tertinggi->formatted_bulan }}</p>
                <p class="text-lg font-bold text-green-600 mt-1">{{ $tertinggi->formatted_jumlah }}</p>
            </div>
        </div>
        @else
        <p class="text-sm text-gray-400 text-center py-4">Belum ada data</p>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-base font-bold text-gray-800 mb-4">
            <i class="fas fa-arrow-down text-red-400 mr-2"></i>Pendapatan Terendah
        </h3>
        @if($terendah)
        <div class="flex items-center gap-4 p-4 bg-red-50 rounded-xl border border-red-100">
            <div class="w-12 h-12 bg-gradient-to-br from-red-400 to-red-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-arrow-down text-white"></i>
            </div>
            <div>
                <p class="font-bold text-gray-800">{{ $terendah->usaha->nama_usaha }}</p>
                <p class="text-xs text-gray-500">{{ $terendah->formatted_bulan }}</p>
                <p class="text-lg font-bold text-red-500 mt-1">{{ $terendah->formatted_jumlah }}</p>
            </div>
        </div>
        @else
        <p class="text-sm text-gray-400 text-center py-4">Belum ada data</p>
        @endif
    </div>

</div>

{{-- ===== TOP 5 USAHA + BELUM LAPOR ===== --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Top 5 Usaha --}}
    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-base font-bold text-gray-800 mb-4">
            <i class="fas fa-ranking-star text-blue-600 mr-2"></i>Top 5 Usaha
        </h3>
        <div class="space-y-3">
            @forelse($topUsaha as $i => $usaha)
            @php
                $medals = ['🥇','🥈','🥉','4️⃣','5️⃣'];
                $total  = $usaha->pendapatans_sum_jumlah ?? 0;
            @endphp
            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-blue-50 transition">
                <span class="text-xl">{{ $medals[$i] ?? ($i+1) }}</span>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 truncate">{{ $usaha->nama_usaha }}</p>
                    <p class="text-xs text-gray-400">{{ $usaha->user->name }}</p>
                </div>
                <p class="font-bold text-blue-600 text-sm whitespace-nowrap">{{ $fmt($total) }}</p>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-4">Belum ada data</p>
            @endforelse
        </div>
    </div>

    {{-- Usaha Belum Lapor --}}
    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-base font-bold text-gray-800 mb-1">
            <i class="fas fa-triangle-exclamation text-orange-500 mr-2"></i>Belum Pernah Lapor
        </h3>
        <p class="text-xs text-gray-400 mb-4">Usaha yang belum pernah submit pendapatan</p>
        @if($belumLapor->isEmpty())
        <div class="flex flex-col items-center justify-center py-8 text-green-500">
            <i class="fas fa-circle-check text-4xl mb-2"></i>
            <p class="text-sm font-semibold">Semua usaha sudah melapor! 🎉</p>
        </div>
        @else
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @foreach($belumLapor as $usaha)
            <div class="flex items-center gap-3 p-3 bg-orange-50 border border-orange-100 rounded-xl">
                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-store text-orange-500 text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm truncate">{{ $usaha->nama_usaha }}</p>
                    <p class="text-xs text-gray-400">{{ $usaha->user->name }} · {{ $usaha->no_wa }}</p>
                </div>
                <a href="{{ route('admin.usaha.detail', $usaha->id) }}"
                    class="px-2 py-1 bg-orange-100 text-orange-600 rounded-lg text-xs font-semibold hover:bg-orange-200 transition whitespace-nowrap">
                    Detail
                </a>
            </div>
            @endforeach
        </div>
        <p class="text-xs text-orange-500 mt-3 text-center font-medium">
            {{ $belumLapor->count() }} usaha belum lapor
        </p>
        @endif
    </div>

</div>

@endsection

@section('scripts')
<script>
@if(!$trenBulanan->isEmpty())
const trenLabels = {!! json_encode($trenBulanan->pluck('bulan_tahun')->map(function($bt) {
    try { return \Carbon\Carbon::createFromFormat('Y-m', $bt)->locale('id')->isoFormat('MMM YY'); }
    catch (\Exception $e) { return $bt ?? '-'; }
})) !!};
const trenData   = {!! json_encode($trenBulanan->pluck('total')->map(fn($v) => (int)$v)) !!};

const maxVal    = Math.max(...trenData);
const barColors = trenData.map(v => v === maxVal ? '#1D4ED8' : '#93C5FD');

new Chart(document.getElementById('trenChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: trenLabels,
        datasets: [{
            label: 'Total Pendapatan',
            data: trenData,
            backgroundColor: barColors,
            borderRadius: 6,
            borderSkipped: false,
            barPercentage: 0.6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1e3a5f',
                padding: 10,
                cornerRadius: 8,
                callbacks: {
                    label: function(ctx) {
                        const v = ctx.parsed.y;
                        if (v >= 1000000000) return 'Rp ' + (v/1000000000).toFixed(2) + ' M';
                        if (v >= 1000000)    return 'Rp ' + (v/1000000).toFixed(1) + ' Jt';
                        return 'Rp ' + v.toLocaleString('id-ID');
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)' },
                ticks: {
                    font: { size: 11 },
                    callback: v => {
                        if (v >= 1000000000) return 'Rp '+(v/1000000000).toFixed(1)+' M';
                        if (v >= 1000000)    return 'Rp '+(v/1000000).toFixed(0)+' Jt';
                        return 'Rp '+v;
                    }
                }
            },
            x: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
    }
});
@endif
</script>
@endsection