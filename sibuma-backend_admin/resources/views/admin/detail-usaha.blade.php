@extends('layouts.admin')

@section('title', 'Detail Usaha')
@section('page-title', 'Detail Usaha')
@section('page-description', 'Informasi lengkap usaha dan riwayat pendapatan')

@section('content')
<!-- Back Button -->
<a href="{{ route('admin.usaha') }}" class="inline-flex items-center mb-6 text-white hover:text-blue-100 transition font-medium text-sm">
    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar Usaha
</a>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Usaha Info Card -->
    <div class="lg:col-span-1 bg-white rounded-xl shadow-md p-6">
        <div class="text-center mb-6">
            <div class="w-20 h-20 mx-auto bg-gradient-to-br
                @if($usaha->kategori == 'perdagangan') from-blue-400 to-blue-600
                @elseif($usaha->kategori == 'industri') from-purple-400 to-purple-600
                @else from-green-400 to-green-600
                @endif
                rounded-full flex items-center justify-center mb-4">
                <i class="fas 
                    @if($usaha->kategori == 'perdagangan') fa-shopping-cart
                    @elseif($usaha->kategori == 'industri') fa-industry
                    @else fa-concierge-bell
                    @endif
                    text-3xl text-white"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ $usaha->nama_usaha }}</h3>
            <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold
                @if($usaha->kategori == 'perdagangan') bg-blue-100 text-blue-600
                @elseif($usaha->kategori == 'industri') bg-purple-100 text-purple-600
                @else bg-green-100 text-green-600
                @endif">
                {{ $usaha->kode_kategori }} - {{ $usaha->kategori == 'industri' ? 'Industri / Manufaktur' : ucfirst($usaha->kategori) }}
            </span>
        </div>
        
        <div class="border-t border-gray-200 pt-6 space-y-4">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Pemilik</p>
                <p class="font-semibold text-gray-800">{{ $usaha->user->name }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Email</p>
                <p class="font-medium text-gray-800 text-sm">{{ $usaha->user->email }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Label Pendapatan</p>
                <p class="font-semibold text-gray-800">{{ $usaha->label_pendapatan }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Terdaftar Sejak</p>
                <p class="font-semibold text-gray-800">{{ $usaha->created_at->format('d M Y') }}</p>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="lg:col-span-2 grid grid-cols-2 md:grid-cols-3 gap-4">

        @php
            $fmt = function($val) {
                if ($val >= 1000000000) return 'Rp ' . number_format($val / 1000000000, 2, ',', '.') . ' M';
                if ($val >= 1000000)    return 'Rp ' . number_format($val / 1000000, 1, ',', '.') . ' Jt';
                return 'Rp ' . number_format($val, 0, ',', '.');
            };

            // Hitung tertinggi, terendah, pertumbuhan
            $pendapatanList  = $usaha->pendapatans->sortBy('bulan_tahun')->values();
            $tertinggi       = $pendapatanList->max('jumlah') ?? 0;
            $terendah        = $pendapatanList->min('jumlah') ?? 0;
            $pertumbuhan     = null;
            $pertumbuhanLabel = '-';
            $pertumbuhanPositif = true;
            if ($pendapatanList->count() >= 2) {
                $last = $pendapatanList->last()->jumlah;
                $arr  = $pendapatanList->values();
                $prev = $arr->get($arr->count() - 2)->jumlah ?? null;
                if ($prev && $prev > 0) {
                    $pertumbuhan = round((($last - $prev) / $prev) * 100, 1);
                    $pertumbuhanPositif = $pertumbuhan >= 0;
                    $pertumbuhanLabel = ($pertumbuhan >= 0 ? '+' : '') . $pertumbuhan . '%';
                }
            }
        @endphp

        <!-- Total -->
        <div class="bg-white rounded-xl shadow-md p-5 card-hover transition duration-300">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Total</p>
            <h4 class="text-lg font-bold text-gray-800 leading-tight">{{ $fmt($statistics['total']) }}</h4>
            <p class="text-xs text-gray-400 mt-1">Akumulasi semua bulan</p>
            <div class="mt-3 w-10 h-10 gradient-primary rounded-xl flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-white text-sm"></i>
            </div>
        </div>

        <!-- Rata-rata -->
        <div class="bg-white rounded-xl shadow-md p-5 card-hover transition duration-300">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Rata-rata</p>
            <h4 class="text-lg font-bold text-gray-800 leading-tight">{{ $fmt($statistics['average']) }}</h4>
            <p class="text-xs text-gray-400 mt-1">Per bulan</p>
            <div class="mt-3 w-10 h-10 gradient-success rounded-xl flex items-center justify-center">
                <i class="fas fa-chart-line text-white text-sm"></i>
            </div>
        </div>

        <!-- Jumlah Data -->
        <div class="bg-white rounded-xl shadow-md p-5 card-hover transition duration-300">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Laporan</p>
            <h4 class="text-2xl font-bold text-gray-800">{{ $statistics['count'] }}</h4>
            <p class="text-xs text-gray-400 mt-1">Bulan terlaporkan</p>
            <div class="mt-3 w-10 h-10 gradient-warning rounded-xl flex items-center justify-center">
                <i class="fas fa-calendar-check text-white text-sm"></i>
            </div>
        </div>

        <!-- Tertinggi -->
        <div class="bg-white rounded-xl shadow-md p-5 card-hover transition duration-300">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Tertinggi</p>
            <h4 class="text-lg font-bold text-gray-800 leading-tight">{{ $fmt($tertinggi) }}</h4>
            <p class="text-xs text-gray-400 mt-1">Nilai terbesar</p>
            <div class="mt-3 w-10 h-10 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-arrow-trend-up text-white text-sm"></i>
            </div>
        </div>

        <!-- Terendah -->
        <div class="bg-white rounded-xl shadow-md p-5 card-hover transition duration-300">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Terendah</p>
            <h4 class="text-lg font-bold text-gray-800 leading-tight">{{ $fmt($terendah) }}</h4>
            <p class="text-xs text-gray-400 mt-1">Nilai terkecil</p>
            <div class="mt-3 w-10 h-10 bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-arrow-trend-down text-white text-sm"></i>
            </div>
        </div>

        <!-- Pertumbuhan -->
        <div class="bg-white rounded-xl shadow-md p-5 card-hover transition duration-300">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Pertumbuhan</p>
            <h4 class="text-2xl font-bold {{ $pertumbuhanPositif ? 'text-green-600' : 'text-red-500' }}">
                {{ $pertumbuhanLabel }}
            </h4>
            <p class="text-xs text-gray-400 mt-1">Bulan terakhir vs sebelumnya</p>
            <div class="mt-3 w-10 h-10 {{ $pertumbuhanPositif ? 'bg-gradient-to-br from-green-400 to-green-600' : 'bg-gradient-to-br from-red-400 to-red-600' }} rounded-xl flex items-center justify-center">
                <i class="fas {{ $pertumbuhanPositif ? 'fa-arrow-up' : 'fa-arrow-down' }} text-white text-sm"></i>
            </div>
        </div>
        
        <!-- Chart -->
        <div class="md:col-span-3 bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-1">
                <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
                Grafik Pendapatan Bulanan
            </h3>
            <p class="text-xs text-gray-400 font-medium tracking-wide mb-4">Omzet per bulan dalam Rupiah</p>

            @if($grafikKosong)
            <div class="flex flex-col items-center justify-center h-40 text-gray-400">
                <i class="fas fa-chart-bar text-4xl mb-2"></i>
                <p class="text-sm font-medium">Belum ada data untuk ditampilkan</p>
            </div>
            @else
            <div style="position: relative; height: 280px; width: 100%;">
                <canvas id="usahaChart"></canvas>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Riwayat Pendapatan -->
<div class="bg-white rounded-xl shadow-md p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-6">
        <i class="fas fa-history text-blue-600 mr-2"></i>
        Riwayat Pendapatan
    </h3>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b-2 border-gray-200">
                    <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">No</th>
                    <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Bulan</th>
                    <th class="text-right py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jumlah</th>
                    <th class="text-center py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Catatan</th>
                    <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal Input</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usaha->pendapatans as $index => $item)
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                    <td class="py-4 px-4 text-gray-500 text-sm">{{ $index + 1 }}</td>
                    <td class="py-4 px-4 font-semibold text-gray-800">{{ $item->formatted_bulan }}</td>
                    <td class="py-4 px-4 text-right font-bold text-gray-800">{{ $item->formatted_jumlah }}</td>
                    <td class="py-4 px-4 text-center">
                        @if($item->catatan_laporan)
                        <button onclick="showCatatan('{{ e($item->catatan_laporan) }}')" class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition text-xs font-semibold">
                            <i class="fas fa-comment-dots mr-1"></i>Lihat
                        </button>
                        @else
                        <span class="text-gray-400 text-sm">-</span>
                        @endif
                    </td>
                    <td class="py-4 px-4 text-sm text-gray-500">{{ $item->created_at->format('d M Y, H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-10 text-center text-gray-400 text-sm font-medium">
                        Belum ada data pendapatan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Catatan -->
<div id="catatanModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-comment-dots text-yellow-500 mr-2"></i>
                Catatan / Keluhan
            </h3>
            <button onclick="closeCatatan()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="catatanContent" class="p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-lg text-gray-700 text-sm leading-relaxed">
        </div>
        <button onclick="closeCatatan()" class="mt-4 w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-semibold">
            Tutup
        </button>
    </div>
</div>
@endsection

@section('scripts')
<script>
@if(!$grafikKosong)
const bulanLabels = {!! json_encode($bulanData) !!};
const nominalData = {!! json_encode($nominalData) !!};

const maxVal = Math.max(...nominalData);
const barColors = nominalData.map(v => v === maxVal ? '#1D4ED8' : '#93C5FD');

const ctx = document.getElementById('usahaChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: bulanLabels,
        datasets: [{
            label: 'Pendapatan',
            data: nominalData,
            backgroundColor: barColors,
            borderRadius: 6,
            borderSkipped: false,
            barPercentage: 0.55,
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
                    label: function(context) {
                        const val = context.parsed.y;
                        if (val >= 1000000000) return 'Rp ' + (val / 1000000000).toFixed(2) + ' Miliar';
                        if (val >= 1000000)    return 'Rp ' + (val / 1000000).toFixed(1) + ' Juta';
                        return 'Rp ' + val.toLocaleString('id-ID');
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
                    callback: function(value) {
                        if (value >= 1000000000) return 'Rp ' + (value / 1000000000).toFixed(1) + ' M';
                        if (value >= 1000000)    return 'Rp ' + (value / 1000000).toFixed(0) + ' Jt';
                        if (value >= 1000)       return 'Rp ' + (value / 1000).toFixed(0) + ' Rb';
                        return 'Rp ' + value;
                    }
                }
            },
            x: {
                grid: { display: false },
                ticks: { font: { size: 11 } }
            }
        }
    }
});
@endif

function showCatatan(text) {
    document.getElementById('catatanContent').textContent = text;
    document.getElementById('catatanModal').classList.remove('hidden');
}

function closeCatatan() {
    document.getElementById('catatanModal').classList.add('hidden');
}
</script>
@endsection