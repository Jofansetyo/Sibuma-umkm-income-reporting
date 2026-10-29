@extends('layouts.admin')

@section('title', 'Edit Pendapatan')
@section('page-title', 'Edit Pendapatan')
@section('page-description', 'Ubah data laporan pendapatan responden')

@section('content')
<div class="max-w-2xl mx-auto">

    <!-- Breadcrumb -->
    <div class="mb-6 flex items-center gap-2 text-sm">
        <a href="{{ route('admin.pendapatan') }}" class="text-blue-300 hover:text-white transition">
            <i class="fas fa-arrow-left mr-1"></i>Kembali ke Data Pendapatan
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-6 md:p-8">

        <!-- Info Usaha -->
        <div class="flex items-center gap-4 mb-6 p-4 bg-blue-50 rounded-xl border border-blue-100">
            <div class="w-12 h-12 gradient-primary rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-store text-white text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-gray-800 text-lg">{{ $pendapatan->usaha->nama_usaha }}</p>
                <p class="text-sm text-gray-500">
                    {{ $pendapatan->usaha->user->name }} &bull;
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                        @if($pendapatan->usaha->kategori == 'perdagangan') bg-blue-100 text-blue-600
                        @elseif($pendapatan->usaha->kategori == 'industri') bg-purple-100 text-purple-600
                        @else bg-green-100 text-green-600 @endif">
                        {{ ucfirst($pendapatan->usaha->kategori) }}
                    </span>
                </p>
            </div>
        </div>

        <!-- Form Edit -->
        <form action="{{ route('admin.pendapatan.update', $pendapatan->id) }}" method="POST">
            @csrf
            @method('PUT')

            @if ($errors->any())
            <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl">
                <p class="text-sm text-red-700 font-semibold mb-1"><i class="fas fa-exclamation-circle mr-2"></i>Terdapat kesalahan:</p>
                @foreach ($errors->all() as $error)
                    <p class="text-sm text-red-600 ml-5">• {{ $error }}</p>
                @endforeach
            </div>
            @endif

            <!-- Jumlah -->
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Jumlah Pendapatan (Rp) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold text-sm">Rp</span>
                    <input type="number" name="jumlah" value="{{ old('jumlah', $pendapatan->jumlah) }}"
                        min="0" step="1000" required
                        class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-800 text-sm @error('jumlah') border-red-400 @enderror"
                        placeholder="0">
                </div>
                <p class="text-xs text-gray-400 mt-1">Jenis input: <span class="font-semibold capitalize">{{ $pendapatan->jenis_input }}</span></p>
            </div>

            <!-- Bulan Tahun -->
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Bulan Laporan <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="text" id="bulanDisplay" readonly
                        value="{{ old('bulan_tahun') ? (function($bt) { try { return \Carbon\Carbon::createFromFormat('Y-m', $bt)->locale('id')->isoFormat('MMMM YYYY'); } catch (\Exception $e) { return $bt; } })(old('bulan_tahun')) : ($pendapatan->formatted_bulan ?? '') }}"
                        placeholder="Pilih bulan laporan..."
                        onclick="toggleMonthPicker()"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-800 text-sm cursor-pointer bg-white pr-10 @error('bulan_tahun') border-red-400 @enderror">
                    <input type="hidden" name="bulan_tahun" id="bulanValue" value="{{ old('bulan_tahun', $pendapatan->bulan_tahun) }}" required>
                    <i class="fas fa-calendar-alt absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </div>
                @error('bulan_tahun')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror

                <!-- Month Picker -->
                <div id="monthPicker" class="hidden absolute z-50 bg-white border border-gray-200 rounded-2xl shadow-2xl p-5 w-80 mt-2">
                    <div class="flex items-center justify-between mb-4">
                        <button type="button" onclick="changeYear(-1)" class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-gray-100 text-gray-600">
                            <i class="fas fa-chevron-left text-sm"></i>
                        </button>
                        <span id="pickerYear" class="font-bold text-gray-800"></span>
                        <button type="button" onclick="changeYear(1)" class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-gray-100 text-gray-600">
                            <i class="fas fa-chevron-right text-sm"></i>
                        </button>
                    </div>
                    <div id="monthGrid" class="grid grid-cols-3 gap-2"></div>
                </div>
            </div>

            <!-- Status -->
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-800 text-sm">
                    <option value="terkirim" {{ $pendapatan->status == 'terkirim' ? 'selected' : '' }}>Terkirim</option>
                    <option value="draft"    {{ $pendapatan->status == 'draft'    ? 'selected' : '' }}>Draft</option>
                </select>
            </div>

            <!-- Catatan -->
            <div class="mb-7">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Catatan / Keluhan <span class="text-gray-400 font-normal">(opsional)</span>
                </label>
                <textarea name="catatan_laporan" rows="4"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-800 text-sm resize-none"
                    placeholder="Catatan dari responden...">{{ old('catatan_laporan', $pendapatan->catatan_laporan) }}</textarea>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3">
                <a href="{{ route('admin.pendapatan') }}"
                    class="flex-1 text-center px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition text-sm font-semibold">
                    Batal
                </a>
                <button type="submit"
                    class="flex-1 px-4 py-3 gradient-primary text-white rounded-xl hover:opacity-90 transition text-sm font-semibold">
                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
const bulanNama = ['Januari','Februari','Maret','April','Mei','Juni',
                   'Juli','Agustus','September','Oktober','November','Desember'];
let pickerYear    = new Date().getFullYear();
let selectedMonth = null;

(function() {
    const existing = document.getElementById('bulanValue').value;
    if (existing) {
        const parts = existing.split('-');
        pickerYear    = parseInt(parts[0]);
        selectedMonth = existing;
    }
    renderPicker();
})();

function toggleMonthPicker() {
    const picker = document.getElementById('monthPicker');
    picker.classList.toggle('hidden');
    if (!picker.classList.contains('hidden')) renderPicker();
}

function renderPicker() {
    document.getElementById('pickerYear').textContent = pickerYear;
    const grid = document.getElementById('monthGrid');
    grid.innerHTML = '';
    bulanNama.forEach((nama, i) => {
        const mm    = String(i + 1).padStart(2, '0');
        const val   = pickerYear + '-' + mm;
        const aktif = val === selectedMonth;
        const btn   = document.createElement('button');
        btn.type        = 'button';
        btn.textContent = nama.slice(0, 3);
        btn.title       = nama + ' ' + pickerYear;
        btn.className   = 'py-2.5 rounded-xl text-sm font-semibold transition ' +
            (aktif ? 'gradient-primary text-white shadow' : 'bg-gray-50 text-gray-700 hover:bg-blue-50 hover:text-blue-600');
        btn.onclick = () => selectMonth(val, nama + ' ' + pickerYear);
        grid.appendChild(btn);
    });
}

function selectMonth(val, label) {
    selectedMonth = val;
    document.getElementById('bulanValue').value   = val;
    document.getElementById('bulanDisplay').value = label;
    document.getElementById('monthPicker').classList.add('hidden');
}

function changeYear(delta) {
    pickerYear += delta;
    renderPicker();
}

document.addEventListener('click', function(e) {
    const picker  = document.getElementById('monthPicker');
    const display = document.getElementById('bulanDisplay');
    if (picker && !picker.contains(e.target) && e.target !== display) {
        picker.classList.add('hidden');
    }
});
</script>
@endsection