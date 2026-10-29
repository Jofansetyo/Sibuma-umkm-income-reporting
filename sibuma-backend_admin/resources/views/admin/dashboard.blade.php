@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Overview statistik pendapatan usaha di Kota Madiun')

@section('content')

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Usaha -->
    <div class="bg-white rounded-xl shadow-md p-6 card-hover transition duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1">Total Usaha</p>
                <h3 class="text-3xl font-bold text-gray-800">{{ $totalUsaha }}</h3>
                <p class="text-sm text-green-600 mt-2 font-medium">
                    <i class="fas fa-arrow-up mr-1"></i>Usaha Terdaftar
                </p>
            </div>
            <div class="w-16 h-16 gradient-primary rounded-full flex items-center justify-center">
                <i class="fas fa-store text-2xl text-white"></i>
            </div>
        </div>
    </div>
    
    <!-- Total Responden -->
    <div class="bg-white rounded-xl shadow-md p-6 card-hover transition duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1">Total Responden</p>
                <h3 class="text-3xl font-bold text-gray-800">{{ $totalResponden }}</h3>
                <p class="text-sm text-green-600 mt-2 font-medium">
                    <i class="fas fa-users mr-1"></i>Pengguna Aktif
                </p>
            </div>
            <div class="w-16 h-16 gradient-success rounded-full flex items-center justify-center">
                <i class="fas fa-users text-2xl text-white"></i>
            </div>
        </div>
    </div>
    
    <!-- Total Pendapatan -->
    <div class="bg-white rounded-xl shadow-md p-6 card-hover transition duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1">Total Pendapatan</p>
                <h3 class="text-2xl font-bold text-gray-800">
                    @if($totalPendapatan >= 1000000000000)
                        Rp {{ number_format($totalPendapatan / 1000000000000, 2, ',', '.') }} T
                    @elseif($totalPendapatan >= 1000000000)
                        Rp {{ number_format($totalPendapatan / 1000000000, 2, ',', '.') }} M
                    @elseif($totalPendapatan >= 1000000)
                        Rp {{ number_format($totalPendapatan / 1000000, 2, ',', '.') }} Jt
                    @else
                        Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                    @endif
                </h3>
                <p class="text-sm text-green-600 mt-2 font-medium">
                    <i class="fas fa-chart-line mr-1"></i>Akumulasi
                </p>
            </div>
            <div class="w-16 h-16 gradient-warning rounded-full flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-2xl text-white"></i>
            </div>
        </div>
    </div>
    
    <!-- Total Entries -->
    <div class="bg-white rounded-xl shadow-md p-6 card-hover transition duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1">Total Entri Data</p>
                <h3 class="text-3xl font-bold text-gray-800">{{ $totalEntries }}</h3>
                <p class="text-sm text-green-600 mt-2 font-medium">
                    <i class="fas fa-database mr-1"></i>Data Tercatat
                </p>
            </div>
            <div class="w-16 h-16 gradient-info rounded-full flex items-center justify-center">
                <i class="fas fa-chart-bar text-2xl text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Trend Pendapatan Bulanan -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
                    Trend Pendapatan Bulanan
                </h3>
                <p class="text-xs text-gray-400 mt-1 font-medium tracking-wide">Omzet usaha per bulan (Rupiah)</p>
            </div>
            <a href="{{ route('admin.export.all') }}" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition text-sm font-semibold">
                <i class="fas fa-download mr-2"></i>Export
            </a>
        </div>

        @php
            $totalBulan = $chartData->count();
            $rataRata = $totalBulan > 0 ? $chartData->sum('total') / $totalBulan : 0;
        @endphp
        <div class="flex gap-4 mb-4 mt-2">
            <div class="bg-blue-50 rounded-lg px-4 py-2 text-center flex-1">
                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Periode Tercatat</p>
                <p class="font-bold text-blue-700">{{ $totalBulan }} Bulan</p>
            </div>
            <div class="bg-green-50 rounded-lg px-4 py-2 text-center flex-1">
                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Rata-rata/Bulan</p>
                <p class="font-bold text-green-700">
                    @if($rataRata >= 1000000000)
                        Rp {{ number_format($rataRata / 1000000000, 1, ',', '.') }} M
                    @elseif($rataRata >= 1000000)
                        Rp {{ number_format($rataRata / 1000000, 1, ',', '.') }} Jt
                    @else
                        Rp {{ number_format($rataRata, 0, ',', '.') }}
                    @endif
                </p>
            </div>
        </div>

        <div style="position: relative; height: 300px; width: 100%;">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>
    
    <!-- Pendapatan by Kategori -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="mb-2">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-chart-pie text-blue-600 mr-2"></i>
                Pendapatan per Kategori
            </h3>
            <p class="text-xs text-gray-400 mt-1 font-medium tracking-wide">Distribusi omzet berdasarkan kategori usaha</p>
        </div>

        <div class="flex flex-col gap-2 mb-4 mt-2">
            @php
                $kategoriColors = ['perdagangan' => '#2563EB', 'industri' => '#9333EA', 'jasa' => '#10B981'];
                $totalPendapatanKategori = $pendapatanByKategori->sum('total');
            @endphp
            @foreach($pendapatanByKategori as $item)
            @php
                $color = $kategoriColors[$item->kategori] ?? '#6B7280';
                $persen = $totalPendapatanKategori > 0 ? round(($item->total / $totalPendapatanKategori) * 100) : 0;
            @endphp
            <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full inline-block" style="background:{{ $color }}"></span>
                    <span class="text-sm font-semibold text-gray-700">
                        {{ $item->kategori == 'industri' ? 'Industri / Manufaktur' : ucfirst($item->kategori) }}
                    </span>
                </div>
                <div class="text-right">
                    <span class="text-sm font-bold text-gray-800">
                        @if($item->total >= 1000000000)
                            Rp {{ number_format($item->total / 1000000000, 1, ',', '.') }} M
                        @elseif($item->total >= 1000000)
                            Rp {{ number_format($item->total / 1000000, 1, ',', '.') }} Jt
                        @else
                            Rp {{ number_format($item->total, 0, ',', '.') }}
                        @endif
                    </span>
                    <span class="text-xs text-gray-400 ml-2">({{ $persen }}%)</span>
                </div>
            </div>
            @endforeach
        </div>

        <div style="position: relative; height: 220px; width: 100%;">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
</div>

<!-- Usaha by Kategori & Recent Data -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Usaha by Kategori -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-6">
            <i class="fas fa-layer-group text-blue-600 mr-2"></i>
            Usaha per Kategori
        </h3>
        
        <div class="space-y-4">
            @forelse($usahaByKategori as $item)
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    @if($item->kategori == 'perdagangan')
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-shopping-cart text-blue-600"></i>
                        </div>
                    @elseif($item->kategori == 'industri')
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-industry text-purple-600"></i>
                        </div>
                    @else
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-concierge-bell text-green-600"></i>
                        </div>
                    @endif
                    <p class="font-semibold text-gray-800">
                        {{ $item->kategori == 'industri' ? 'Industri / Manufaktur' : ucfirst($item->kategori) }}
                    </p>
                </div>
                <span class="text-2xl font-bold text-gray-800">{{ $item->total }}</span>
            </div>
            @empty
            <p class="text-gray-500 text-center text-sm">Belum ada data usaha.</p>
            @endforelse
        </div>
    </div>
    
    <!-- Recent Pendapatan -->
    <div class="bg-white rounded-xl shadow-md p-6 lg:col-span-2">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-clock text-blue-600 mr-2"></i>
                Data Pendapatan Terbaru
            </h3>
            <a href="{{ route('admin.pendapatan') }}" class="text-blue-600 hover:text-blue-700 text-sm font-semibold">
                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Usaha</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kategori</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Bulan</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPendapatan->take(5) as $item)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="py-3 px-4">
                            <p class="font-semibold text-gray-800">{{ $item->usaha->nama_usaha ?? 'Usaha Dihapus' }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $item->usaha->user->name ?? 'User Dihapus' }}</p>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                @if($item->usaha?->kategori == 'perdagangan') bg-blue-100 text-blue-600
                                @elseif($item->usaha?->kategori == 'industri') bg-purple-100 text-purple-600
                                @else bg-green-100 text-green-600
                                @endif">
                                {{ $item->usaha->kode_kategori ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-600">{{ $item->formatted_bulan ?? $item->bulan_tahun }}</td>
                        <td class="py-3 px-4 text-right font-bold text-gray-800">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-gray-400 text-sm">Belum ada data pendapatan yang masuk.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyLabels = {!! json_encode($chartData->pluck('bulan_tahun')->map(function($bt) {
        try { return \Carbon\Carbon::createFromFormat('Y-m', $bt)->format('M Y'); }
        catch (\Exception $e) { return $bt ?? '-'; }
    })) !!};
    const monthlyData   = {!! json_encode($chartData->pluck('total')->map(fn($v) => (int)$v)) !!};

    const maxVal = Math.max(...monthlyData);
    const barColors = monthlyData.map(v => v === maxVal ? '#1D4ED8' : '#93C5FD');

    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Total Pendapatan',
                data: monthlyData,
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
                    callbacks: {
                        label: function(context) {
                            const val = context.parsed.y;
                            if (val >= 1000000000) return 'Rp ' + (val / 1000000000).toFixed(2) + ' Miliar';
                            if (val >= 1000000)    return 'Rp ' + (val / 1000000).toFixed(1) + ' Juta';
                            return 'Rp ' + val.toLocaleString('id-ID');
                        }
                    },
                    backgroundColor: '#1e3a5f',
                    titleFont: { size: 12 },
                    bodyFont: { size: 13, weight: 'bold' },
                    padding: 10,
                    cornerRadius: 8,
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
                x: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });

    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($pendapatanByKategori->pluck('kategori')->map(fn($k) => ucfirst($k))) !!},
            datasets: [{
                data: {!! json_encode($pendapatanByKategori->pluck('total')->map(fn($v) => (int)$v)) !!},
                backgroundColor: ['#2563EB', '#9333EA', '#10B981'],
                borderWidth: 3,
                borderColor: '#ffffff',
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const val = context.parsed;
                            if (val >= 1000000000) return context.label + ': Rp ' + (val / 1000000000).toFixed(2) + ' Miliar';
                            if (val >= 1000000)    return context.label + ': Rp ' + (val / 1000000).toFixed(1) + ' Juta';
                            return context.label + ': Rp ' + val.toLocaleString('id-ID');
                        }
                    },
                    backgroundColor: '#1e3a5f',
                    padding: 10,
                    cornerRadius: 8,
                }
            }
        }
    });
</script>
@endsection