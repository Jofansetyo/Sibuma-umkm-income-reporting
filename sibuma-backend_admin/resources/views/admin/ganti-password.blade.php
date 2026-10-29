@extends('layouts.admin')

@section('title', 'Ganti Password')
@section('page-title', 'Ganti Password')
@section('page-description', 'Ubah kata sandi akun administrator')

@section('content')
<div class="max-w-lg mx-auto">

    {{-- Notifikasi Sukses --}}
    @if(session('success'))
    <div id="successAlert" class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-5 py-4 rounded-xl shadow-sm">
        <i class="fas fa-check-circle text-green-500 text-lg"></i>
        <span class="font-medium text-sm">{{ session('success') }}</span>
        <button onclick="document.getElementById('successAlert').remove()" class="ml-auto text-green-400 hover:text-green-600"><i class="fas fa-times"></i></button>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-md p-6 md:p-8">

        <!-- Info Akun -->
        <div class="flex items-center gap-4 mb-7 p-4 bg-blue-50 rounded-xl border border-blue-100">
            <div class="w-12 h-12 gradient-primary rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user-shield text-white text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-gray-800">{{ Auth::user()->name }}</p>
                <p class="text-sm text-gray-500">{{ Auth::user()->email }}</p>
                <span class="text-xs bg-blue-100 text-blue-600 font-semibold px-2 py-0.5 rounded-full">Administrator</span>
            </div>
        </div>

        <h3 class="text-lg font-bold text-gray-800 mb-5">
            <i class="fas fa-key text-blue-500 mr-2"></i>Ubah Kata Sandi
        </h3>

        <form action="{{ route('admin.ganti-password.post') }}" method="POST">
            @csrf

            @if ($errors->any())
            <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl">
                <p class="text-sm text-red-700 font-semibold mb-1">
                    <i class="fas fa-exclamation-circle mr-2"></i>Terdapat kesalahan:
                </p>
                @foreach ($errors->all() as $error)
                    <p class="text-sm text-red-600 ml-5">• {{ $error }}</p>
                @endforeach
            </div>
            @endif

            <!-- Password Lama -->
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Kata Sandi Lama <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="password" name="current_password" id="current_password"
                        required placeholder="Masukkan kata sandi lama"
                        class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-800 text-sm @error('current_password') border-red-400 bg-red-50 @enderror">
                    <button type="button" onclick="togglePassword('current_password', 'eye1')"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i id="eye1" class="fas fa-eye text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Password Baru -->
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Kata Sandi Baru <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="password" name="new_password" id="new_password"
                        required placeholder="Minimal 6 karakter"
                        oninput="checkStrength(this.value)"
                        class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-800 text-sm @error('new_password') border-red-400 bg-red-50 @enderror">
                    <button type="button" onclick="togglePassword('new_password', 'eye2')"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i id="eye2" class="fas fa-eye text-sm"></i>
                    </button>
                </div>
                <!-- Indikator kekuatan password -->
                <div class="mt-2 hidden" id="strengthBar">
                    <div class="flex gap-1 mb-1">
                        <div id="s1" class="h-1.5 flex-1 rounded-full bg-gray-200 transition-all"></div>
                        <div id="s2" class="h-1.5 flex-1 rounded-full bg-gray-200 transition-all"></div>
                        <div id="s3" class="h-1.5 flex-1 rounded-full bg-gray-200 transition-all"></div>
                        <div id="s4" class="h-1.5 flex-1 rounded-full bg-gray-200 transition-all"></div>
                    </div>
                    <p id="strengthLabel" class="text-xs text-gray-400"></p>
                </div>
            </div>

            <!-- Konfirmasi Password Baru -->
            <div class="mb-7">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Konfirmasi Kata Sandi Baru <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                        required placeholder="Ulangi kata sandi baru"
                        oninput="checkMatch()"
                        class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-800 text-sm">
                    <button type="button" onclick="togglePassword('new_password_confirmation', 'eye3')"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i id="eye3" class="fas fa-eye text-sm"></i>
                    </button>
                </div>
                <p id="matchMsg" class="text-xs mt-1 hidden"></p>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex-1 text-center px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition text-sm font-semibold">
                    Batal
                </a>
                <button type="submit" id="submitBtn"
                    class="flex-1 px-4 py-3 gradient-primary text-white rounded-xl hover:opacity-90 transition text-sm font-semibold">
                    <i class="fas fa-save mr-2"></i>Simpan Kata Sandi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function togglePassword(fieldId, eyeId) {
    const input = document.getElementById(fieldId);
    const eye   = document.getElementById(eyeId);
    if (input.type === 'password') {
        input.type  = 'text';
        eye.className = 'fas fa-eye-slash text-sm';
    } else {
        input.type  = 'password';
        eye.className = 'fas fa-eye text-sm';
    }
}

function checkStrength(val) {
    const bar = document.getElementById('strengthBar');
    bar.classList.remove('hidden');
    const s = [document.getElementById('s1'), document.getElementById('s2'),
                document.getElementById('s3'), document.getElementById('s4')];
    const label = document.getElementById('strengthLabel');

    // Reset
    s.forEach(el => el.style.background = '#e5e7eb');

    let score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
    if (/[0-9]/.test(val) || /[^A-Za-z0-9]/.test(val)) score++;

    const colors = ['#EF4444', '#F59E0B', '#3B82F6', '#10B981'];
    const labels = ['Lemah', 'Cukup', 'Kuat', 'Sangat Kuat'];
    for (let i = 0; i < score; i++) s[i].style.background = colors[score - 1];
    label.textContent = score > 0 ? labels[score - 1] : '';
    label.style.color = colors[score - 1] ?? '#9ca3af';
}

function checkMatch() {
    const newPass    = document.getElementById('new_password').value;
    const confirmPass = document.getElementById('new_password_confirmation').value;
    const msg        = document.getElementById('matchMsg');
    if (!confirmPass) { msg.classList.add('hidden'); return; }
    msg.classList.remove('hidden');
    if (newPass === confirmPass) {
        msg.textContent = '✓ Kata sandi cocok';
        msg.style.color = '#10B981';
    } else {
        msg.textContent = '✗ Kata sandi tidak cocok';
        msg.style.color = '#EF4444';
    }
}
</script>
@endsection