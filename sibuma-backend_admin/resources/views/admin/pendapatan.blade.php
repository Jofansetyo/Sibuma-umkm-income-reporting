@extends('layouts.admin')

@section('title', 'Data Pendapatan')
@section('page-title', 'Data Pendapatan')
@section('page-description', 'Monitoring semua data pendapatan usaha')

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
            <h3 class="text-xl font-bold text-gray-800">Semua Data Pendapatan</h3>
            <p class="text-sm text-gray-500 mt-1">Total: <span class="font-semibold text-gray-700">{{ $pendapatans->total() }}</span> data</p>
        </div>
        <a href="{{ route('admin.export.pendapatan', request()->query()) }}" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition text-sm font-semibold inline-flex items-center">
            <i class="fas fa-file-excel mr-2"></i>Export
        </a>
    </div>

    <!-- Filter -->
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded-xl">
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Kategori Usaha</label>
            <select name="kategori" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm text-gray-700">
                <option value="">Semua Kategori</option>
                <option value="perdagangan" {{ request('kategori') == 'perdagangan' ? 'selected' : '' }}>Perdagangan</option>
                <option value="industri"    {{ request('kategori') == 'industri'    ? 'selected' : '' }}>Industri / Manufaktur</option>
                <option value="jasa"        {{ request('kategori') == 'jasa'        ? 'selected' : '' }}>Jasa</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Bulan</label>
            <div class="relative">
                <input type="text" id="bulanDisplay" readonly
                    value="{{ request('bulan') ? (function() { try { return \Carbon\Carbon::createFromFormat('Y-m', request('bulan'))->locale('id')->isoFormat('MMMM YYYY'); } catch (\Exception $e) { return ''; } })() : '' }}"
                    placeholder="Pilih bulan..."
                    onclick="toggleMonthPicker()"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 cursor-pointer bg-white pr-10">
                <input type="hidden" name="bulan" id="bulanValue" value="{{ request('bulan') }}">
                <i class="fas fa-calendar-alt absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
            </div>
            <!-- Month Picker -->
            <div id="monthPicker" class="hidden absolute z-40 bg-white border border-gray-200 rounded-xl shadow-xl p-4 w-72 mt-1">
                <div class="flex items-center justify-between mb-3">
                    <button type="button" onclick="changeYear(-1)" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-600">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </button>
                    <span id="pickerYear" class="font-bold text-gray-800 text-sm"></span>
                    <button type="button" onclick="changeYear(1)" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-600">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </button>
                </div>
                <div id="monthGrid" class="grid grid-cols-3 gap-2"></div>
                <button type="button" onclick="clearMonth()" class="mt-3 w-full text-xs text-gray-400 hover:text-red-500 transition text-center">
                    <i class="fas fa-times mr-1"></i>Hapus Filter Bulan
                </button>
            </div>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm font-semibold">
                <i class="fas fa-filter mr-2"></i>Filter
            </button>
        </div>
    </form>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b-2 border-gray-200">
                    <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">No</th>
                    <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Usaha</th>
                    <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pemilik</th>
                    <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kategori</th>
                    <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Bulan</th>
                    <th class="text-right py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jumlah</th>
                    <th class="text-center py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Catatan</th>
                    <th class="text-center py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendapatans as $index => $item)
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                    <td class="py-4 px-4 text-gray-500 text-sm">{{ ($pendapatans->currentPage() - 1) * $pendapatans->perPage() + $index + 1 }}</td>
                    <td class="py-4 px-4">
                        <p class="font-semibold text-gray-800">{{ $item->usaha->nama_usaha }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $item->usaha->kode_kategori }}</p>
                    </td>
                    <td class="py-4 px-4">
                        <p class="font-medium text-gray-800">{{ $item->usaha->user->name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $item->usaha->user->email }}</p>
                    </td>
                    <td class="py-4 px-4">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            @if($item->usaha->kategori == 'perdagangan') bg-blue-100 text-blue-600
                            @elseif($item->usaha->kategori == 'industri') bg-purple-100 text-purple-600
                            @else bg-green-100 text-green-600 @endif">
                            {{ $item->usaha->kategori == 'industri' ? 'Industri / Manufaktur' : ucfirst($item->usaha->kategori) }}
                        </span>
                    </td>
                    <td class="py-4 px-4 text-sm text-gray-700">{{ $item->formatted_bulan }}</td>
                    <td class="py-4 px-4 text-right font-bold text-gray-800">{{ $item->formatted_jumlah }}</td>
                    <td class="py-4 px-4 text-center">
                        @if($item->catatan_laporan)
                        <button type="button" onclick="showCatatan('{{ e($item->catatan_laporan) }}')"
                            class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition text-xs font-semibold">
                            <i class="fas fa-comment-dots mr-1"></i>Lihat
                        </button>
                        @else
                        <span class="text-gray-400 text-sm">-</span>
                        @endif
                    </td>
                    <td class="py-4 px-4 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('admin.pendapatan.edit', $item->id) }}"
                                class="px-3 py-1 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition text-xs font-semibold">
                                <i class="fas fa-pencil mr-1"></i>Edit
                            </a>
                            <button type="button"
                                onclick="confirmHapus({{ $item->id }}, '{{ e($item->usaha->nama_usaha) }}', '{{ e($item->formatted_bulan) }}')"
                                class="px-3 py-1 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition text-xs font-semibold">
                                <i class="fas fa-trash mr-1"></i>Hapus
                            </button>
                        </div>
                        {{-- Form hapus tersembunyi --}}
                        <form id="form-hapus-{{ $item->id }}"
                            action="{{ route('admin.pendapatan.delete', $item->id) }}"
                            method="POST" style="display:none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-12 text-center text-gray-400">
                        <i class="fas fa-inbox text-4xl mb-3 block"></i>
                        <p class="text-sm font-medium">Tidak ada data pendapatan</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">{{ $pendapatans->links() }}</div>

</div>

{{-- ===== MODAL CATATAN ===== --}}
<div id="modalCatatan" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; padding:24px; max-width:480px; width:90%; margin:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h3 style="font-weight:700; font-size:16px; color:#1f2937;">
                <i class="fas fa-comment-dots" style="color:#f59e0b; margin-right:8px;"></i>Catatan / Keluhan
            </h3>
            <button onclick="tutupCatatan()" style="background:none; border:none; font-size:20px; color:#9ca3af; cursor:pointer;">&times;</button>
        </div>
        <div id="isiCatatan" style="padding:16px; background:#fefce8; border-left:4px solid #f59e0b; border-radius:8px; font-size:14px; color:#374151; line-height:1.6;"></div>
        <button onclick="tutupCatatan()" style="margin-top:16px; width:100%; padding:10px; background:#f3f4f6; border:none; border-radius:8px; font-weight:600; font-size:14px; cursor:pointer;">Tutup</button>
    </div>
</div>

{{-- ===== MODAL KONFIRMASI HAPUS ===== --}}
<div id="modalHapus" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; padding:24px; max-width:440px; width:90%; margin:auto; box-shadow:0 25px 50px rgba(0,0,0,0.25);">
        <div style="display:flex; align-items:center; margin-bottom:16px;">
            <div style="width:48px; height:48px; background:#fee2e2; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-right:16px; flex-shrink:0;">
                <i class="fas fa-trash" style="color:#ef4444; font-size:18px;"></i>
            </div>
            <div>
                <h3 style="font-weight:700; font-size:16px; color:#1f2937; margin:0;">Hapus Data Pendapatan</h3>
                <p style="font-size:13px; color:#6b7280; margin:4px 0 0;">Tindakan ini tidak dapat dibatalkan</p>
            </div>
        </div>
        <div style="padding:16px; background:#fef2f2; border:1px solid #fecaca; border-radius:12px; margin-bottom:20px;">
            <p style="font-size:13px; color:#b91c1c; margin:0 0 4px;">Anda yakin ingin menghapus:</p>
            <p id="namaHapus" style="font-weight:700; color:#991b1b; margin:0 0 2px; font-size:15px;"></p>
            <p id="bulanHapus" style="font-size:13px; color:#dc2626; margin:0;"></p>
        </div>
        <div style="display:flex; gap:12px;">
            <button onclick="tutupHapus()" style="flex:1; padding:12px; background:#f3f4f6; border:none; border-radius:12px; font-weight:600; font-size:14px; cursor:pointer; color:#374151;">
                Batal
            </button>
            <button onclick="prosesHapus()" style="flex:1; padding:12px; background:#ef4444; border:none; border-radius:12px; font-weight:600; font-size:14px; cursor:pointer; color:#fff;">
                <i class="fas fa-trash" style="margin-right:6px;"></i>Ya, Hapus
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// ===== HAPUS =====
var idHapusAktif = null;

function confirmHapus(id, nama, bulan) {
    idHapusAktif = id;
    document.getElementById('namaHapus').textContent = nama;
    document.getElementById('bulanHapus').textContent = bulan;
    var modal = document.getElementById('modalHapus');
    modal.style.display = 'flex';
}

function tutupHapus() {
    document.getElementById('modalHapus').style.display = 'none';
    idHapusAktif = null;
}

function prosesHapus() {
    if (idHapusAktif) {
        document.getElementById('form-hapus-' + idHapusAktif).submit();
    }
}

// ===== CATATAN =====
function showCatatan(teks) {
    document.getElementById('isiCatatan').textContent = teks;
    document.getElementById('modalCatatan').style.display = 'flex';
}

function tutupCatatan() {
    document.getElementById('modalCatatan').style.display = 'none';
}

// Tutup modal kalau klik overlay
document.getElementById('modalHapus').addEventListener('click', function(e) {
    if (e.target === this) tutupHapus();
});
document.getElementById('modalCatatan').addEventListener('click', function(e) {
    if (e.target === this) tutupCatatan();
});

// ===== MONTH PICKER =====
var bulanNama = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
var pickerYear = new Date().getFullYear();
var selectedMonth = null;

(function() {
    var existing = document.getElementById('bulanValue').value;
    if (existing) {
        var parts = existing.split('-');
        pickerYear = parseInt(parts[0]);
        selectedMonth = existing;
    }
})();

function toggleMonthPicker() {
    var picker = document.getElementById('monthPicker');
    if (picker.classList.contains('hidden')) {
        renderPicker();
        picker.classList.remove('hidden');
    } else {
        picker.classList.add('hidden');
    }
}

function renderPicker() {
    document.getElementById('pickerYear').textContent = pickerYear;
    var grid = document.getElementById('monthGrid');
    grid.innerHTML = '';
    bulanNama.forEach(function(nama, i) {
        var mm  = String(i + 1).padStart(2, '0');
        var val = pickerYear + '-' + mm;
        var aktif = val === selectedMonth;
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = nama.slice(0, 3);
        btn.className = 'py-2 rounded-lg text-xs font-semibold transition ' +
            (aktif ? 'bg-blue-500 text-white' : 'bg-gray-50 text-gray-700 hover:bg-blue-50 hover:text-blue-600');
        btn.onclick = function() { selectMonth(val, nama + ' ' + pickerYear); };
        grid.appendChild(btn);
    });
}

function selectMonth(val, label) {
    selectedMonth = val;
    document.getElementById('bulanValue').value   = val;
    document.getElementById('bulanDisplay').value = label;
    document.getElementById('monthPicker').classList.add('hidden');
}

function clearMonth() {
    selectedMonth = null;
    document.getElementById('bulanValue').value   = '';
    document.getElementById('bulanDisplay').value = '';
    document.getElementById('monthPicker').classList.add('hidden');
}

function changeYear(delta) {
    pickerYear += delta;
    renderPicker();
}

document.addEventListener('click', function(e) {
    var picker  = document.getElementById('monthPicker');
    var display = document.getElementById('bulanDisplay');
    if (picker && !picker.classList.contains('hidden')) {
        if (!picker.contains(e.target) && e.target !== display) {
            picker.classList.add('hidden');
        }
    }
});
</script>
@endsection