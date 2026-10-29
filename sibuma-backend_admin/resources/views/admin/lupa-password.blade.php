<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - SIBUMA Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">

    <!-- Logo -->
    <div class="text-center mb-8">
        <div class="inline-block p-4 bg-blue-100 rounded-2xl mb-4">
            <i class="fas fa-key text-4xl text-blue-600"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-800">SIBUMA</h1>
        <p class="text-gray-500 mt-1 text-sm">Sistem Informasi Bisnis Usaha Madiun</p>
    </div>

    <div class="bg-white rounded-2xl shadow-xl p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-1">Ganti Kata Sandi</h2>
        <p class="text-gray-500 text-sm mb-6">Masukkan email, kata sandi lama, dan kata sandi baru Anda.</p>

        {{-- Notif sukses --}}
        @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-5">
            <i class="fas fa-check-circle text-green-500"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
        @endif

        {{-- Error --}}
        @if ($errors->any())
        <div class="bg-red-50 border border-red-200 p-4 rounded-xl mb-5">
            @foreach ($errors->all() as $error)
                <p class="text-sm text-red-700"><i class="fas fa-exclamation-circle mr-2"></i>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('admin.lupa-password.post') }}">
            @csrf

            <!-- Email -->
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-envelope mr-2 text-blue-500"></i>Email
                </label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    placeholder="Email akun admin"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm text-gray-800">
            </div>

            <!-- Password Lama -->
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2 text-blue-500"></i>Kata Sandi Lama
                </label>
                <div class="relative">
                    <input type="password" name="current_password" id="pw_lama" required
                        placeholder="Masukkan kata sandi lama"
                        class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm text-gray-800">
                    <button type="button" onclick="toggle('pw_lama','eye1')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i id="eye1" class="fas fa-eye text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Password Baru -->
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-lock-open mr-2 text-blue-500"></i>Kata Sandi Baru
                </label>
                <div class="relative">
                    <input type="password" name="new_password" id="pw_baru" required
                        placeholder="Minimal 6 karakter"
                        class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm text-gray-800">
                    <button type="button" onclick="toggle('pw_baru','eye2')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i id="eye2" class="fas fa-eye text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Konfirmasi Password Baru -->
            <div class="mb-7">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-check-circle mr-2 text-blue-500"></i>Konfirmasi Kata Sandi Baru
                </label>
                <div class="relative">
                    <input type="password" name="new_password_confirmation" id="pw_konfirm" required
                        placeholder="Ulangi kata sandi baru"
                        class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm text-gray-800">
                    <button type="button" onclick="toggle('pw_konfirm','eye3')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i id="eye3" class="fas fa-eye text-sm"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full gradient-bg text-white font-semibold py-3 rounded-xl hover:shadow-lg transition">
                <i class="fas fa-save mr-2"></i>Simpan Kata Sandi Baru
            </button>
        </form>

        <div class="mt-5 text-center">
            <a href="{{ route('admin.login') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                <i class="fas fa-arrow-left mr-1"></i>Kembali ke Login
            </a>
        </div>
    </div>
</div>

<script>
function toggle(fieldId, eyeId) {
    var input = document.getElementById(fieldId);
    var eye   = document.getElementById(eyeId);
    if (input.type === 'password') {
        input.type    = 'text';
        eye.className = 'fas fa-eye-slash text-sm';
    } else {
        input.type    = 'password';
        eye.className = 'fas fa-eye text-sm';
    }
}
</script>
</body>
</html>