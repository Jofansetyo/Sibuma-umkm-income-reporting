@extends('layouts.admin')

@section('title', 'Data Usaha')
@section('page-title', 'Data Usaha')
@section('page-description', 'Daftar semua usaha yang terdaftar di SIBUMA')

@section('content')

{{-- Notifikasi Sukses --}}
@if(session('success'))
<div id="successAlert" class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-5 py-4 rounded-xl shadow-sm">
    <i class="fas fa-check-circle text-green-500 text-lg"></i>
    <span class="font-medium text-sm">{{ session('success') }}</span>
    <button onclick="document.getElementById('successAlert').remove()" class="ml-auto text-green-400 hover:text-green-600"><i class="fas fa-times"></i></button>
</div>
@endif

<div class="bg-white rounded-xl shadow-md p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <div>
            <h3 class="text-xl font-bold text-gray-800">Semua Usaha Terdaftar</h3>
            <p class="text-sm text-gray-500 mt-1">Total: <span class="font-semibold text-gray-700">{{ $usahas->total() }}</span> usaha</p>
        </div>
        <a href="{{ route('admin.export.usaha') }}" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition text-sm font-semibold inline-flex items-center">
            <i class="fas fa-file-excel mr-2"></i>Export ke Excel
        </a>
    </div>

    <!-- Filter & Search -->
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 mb-6 p-4 bg-gray-50 rounded-xl">
        <div class="sm:col-span-2 md:col-span-2">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Cari Usaha / Pemilik</label>
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Nama usaha atau pemilik..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm text-gray-700">
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Kategori</label>
            <select name="kategori" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm text-gray-700">
                <option value="">Semua</option>
                <option value="perdagangan" {{ request('kategori') == 'perdagangan' ? 'selected' : '' }}>Perdagangan</option>
                <option value="industri"    {{ request('kategori') == 'industri'    ? 'selected' : '' }}>Industri</option>
                <option value="jasa"        {{ request('kategori') == 'jasa'        ? 'selected' : '' }}>Jasa</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm font-semibold">
                <i class="fas fa-filter mr-1"></i>Filter
            </button>
            @if(request('search') || request('kategori'))
            <a href="{{ route('admin.usaha') }}" class="px-3 py-2 bg-gray-200 text-gray-600 rounded-lg hover:bg-gray-300 transition text-sm font-semibold">
                <i class="fas fa-times"></i>
            </a>
            @endif
        </div>
    </form>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b-2 border-gray-200">
                    <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">No</th>
                    <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama Usaha</th>
                    <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pemilik</th>
                    <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kategori</th>
                    <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kode</th>
                    <th class="text-center py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usahas as $index => $usaha)
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                    <td class="py-4 px-4 text-gray-500 text-sm">{{ ($usahas->currentPage() - 1) * $usahas->perPage() + $index + 1 }}</td>
                    <td class="py-4 px-4">
                        <p class="font-semibold text-gray-800">{{ $usaha->nama_usaha }}</p>
                    </td>
                    <td class="py-4 px-4">
                        <p class="font-medium text-gray-800">{{ $usaha->user->name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $usaha->user->email }}</p>
                    </td>
                    <td class="py-4 px-4">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            @if($usaha->kategori == 'perdagangan') bg-blue-100 text-blue-600
                            @elseif($usaha->kategori == 'industri') bg-purple-100 text-purple-600
                            @else bg-green-100 text-green-600
                            @endif">
                            {{ $usaha->kategori == 'industri' ? 'Industri / Manufaktur' : ucfirst($usaha->kategori) }}
                        </span>
                    </td>
                    <td class="py-4 px-4">
                        <span class="font-mono font-bold text-gray-700 bg-gray-100 px-2 py-1 rounded text-sm">{{ $usaha->kode_kategori }}</span>
                    </td>
                    <td class="py-4 px-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.usaha.detail', $usaha->id) }}" class="px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-xs font-semibold">
                                <i class="fas fa-eye mr-1"></i>Detail
                            </a>
                            <button onclick="confirmDeleteUsaha({{ $usaha->id }}, '{{ e($usaha->nama_usaha) }}', '{{ e($usaha->user->name) }}')"
                                class="px-3 py-1.5 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition text-xs font-semibold">
                                <i class="fas fa-trash mr-1"></i>Hapus
                            </button>
                            <form id="delete-usaha-{{ $usaha->id }}" action="{{ route('admin.usaha.delete', $usaha->id) }}" method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-gray-400">
                        <i class="fas fa-search text-4xl mb-3 block"></i>
                        <p class="text-sm font-medium">Tidak ada usaha ditemukan</p>
                        @if(request('search') || request('kategori'))
                        <a href="{{ route('admin.usaha') }}" class="mt-2 inline-block text-blue-500 text-sm hover:underline">Hapus filter</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $usahas->withQueryString()->links() }}
    </div>
</div>

<!-- Modal Hapus Usaha -->
<div id="deleteUsahaModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 px-4">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl">
        <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">Hapus Data Usaha</h3>
                <p class="text-sm text-gray-500 mt-0.5">Tindakan ini tidak dapat dibatalkan</p>
            </div>
        </div>
        <div class="p-4 bg-red-50 border border-red-200 rounded-xl mb-2">
            <p class="text-sm text-red-700 font-semibold">Data yang akan dihapus:</p>
            <p class="font-bold text-red-800 mt-1" id="deleteNamaUsaha"></p>
            <p class="text-sm text-red-600">Pemilik: <span id="deleteNamaPemilik"></span></p>
        </div>
        <div class="p-3 bg-orange-50 border border-orange-200 rounded-xl mb-5">
            <p class="text-xs text-orange-700 flex items-start gap-2">
                <i class="fas fa-triangle-exclamation mt-0.5 flex-shrink-0"></i>
                <span>Semua data pendapatan usaha ini dan akun responden juga akan ikut terhapus secara permanen.</span>
            </p>
        </div>
        <div class="flex gap-3">
            <button onclick="closeDeleteUsahaModal()" class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition text-sm font-semibold">Batal</button>
            <button onclick="submitDeleteUsaha()" class="flex-1 px-4 py-2.5 bg-red-500 text-white rounded-xl hover:bg-red-600 transition text-sm font-semibold">
                <i class="fas fa-trash mr-2"></i>Ya, Hapus Semua
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let currentDeleteUsahaId = null;
function confirmDeleteUsaha(id, namaUsaha, namaPemilik) {
    currentDeleteUsahaId = id;
    document.getElementById('deleteNamaUsaha').textContent = namaUsaha;
    document.getElementById('deleteNamaPemilik').textContent = namaPemilik;
    document.getElementById('deleteUsahaModal').classList.remove('hidden');
}
function closeDeleteUsahaModal() {
    document.getElementById('deleteUsahaModal').classList.add('hidden');
    currentDeleteUsahaId = null;
}
function submitDeleteUsaha() {
    if (currentDeleteUsahaId) {
        document.getElementById('delete-usaha-' + currentDeleteUsahaId).submit();
    }
}
</script>
@endsection