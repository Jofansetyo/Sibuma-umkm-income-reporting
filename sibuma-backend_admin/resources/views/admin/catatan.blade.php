@extends('layouts.admin')

@section('title', 'Catatan & Keluhan')
@section('page-title', 'Catatan & Keluhan')
@section('page-description', 'Monitoring catatan dan keluhan dari responden')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-xl font-bold text-gray-800">Semua Catatan & Keluhan</h3>
            <p class="text-sm text-gray-500 mt-1">Total: <span class="font-semibold text-gray-700">{{ $catatans->total() }}</span> catatan</p>
        </div>
    </div>
    
    <div class="space-y-4">
        @forelse($catatans as $catatan)
        <div class="border border-gray-200 rounded-xl p-6 hover:shadow-lg transition">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                        <i class="fas fa-comment-dots text-xl text-yellow-600"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800">{{ $catatan->usaha->nama_usaha }}</h4>
                        <p class="text-sm text-gray-600 mt-0.5">{{ $catatan->usaha->user->name }}</p>
                        <span class="inline-block px-2 py-1 rounded-full text-xs font-semibold mt-1
                            @if($catatan->usaha->kategori == 'perdagangan') bg-blue-100 text-blue-600
                            @elseif($catatan->usaha->kategori == 'industri') bg-purple-100 text-purple-600
                            @else bg-green-100 text-green-600
                            @endif">
                            {{ $catatan->usaha->kode_kategori }} -
                            {{ $catatan->usaha->kategori == 'industri' ? 'Industri / Manufaktur' : ucfirst($catatan->usaha->kategori) }}
                        </span>
                    </div>
                </div>
                
                <div class="text-right flex-shrink-0 ml-4">
                    <p class="text-sm text-gray-500 font-medium">{{ $catatan->created_at->diffForHumans() }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $catatan->created_at->format('d M Y, H:i') }}</p>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="flex items-center gap-4 text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                    <span>
                        <i class="fas fa-calendar-alt mr-1"></i>
                        {{ $catatan->formatted_bulan }}
                    </span>
                    <span>
                        <i class="fas fa-money-bill-wave mr-1"></i>
                        {{ $catatan->formatted_jumlah }}
                    </span>
                </div>
            </div>
            
            <div class="p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                <p class="text-gray-700 text-sm leading-relaxed">{{ $catatan->catatan_laporan }}</p>
            </div>
            
            <div class="mt-4 flex gap-2">
                <a href="{{ route('admin.usaha.detail', $catatan->usaha->id) }}" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm font-semibold">
                    <i class="fas fa-eye mr-1"></i>Lihat Usaha
                </a>
            </div>
        </div>
        @empty
        <div class="text-center py-16">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4 block"></i>
            <p class="text-gray-500 font-medium">Belum ada catatan atau keluhan</p>
            <p class="text-gray-400 text-sm mt-1">Catatan dari responden akan muncul di sini</p>
        </div>
        @endforelse
    </div>
    
    <div class="mt-6">
        {{ $catatans->links() }}
    </div>
</div>
@endsection