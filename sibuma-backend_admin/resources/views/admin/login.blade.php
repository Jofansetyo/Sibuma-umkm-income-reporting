<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIBUMA Admin</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563EB',
                    }
                }
            }
        }
    </script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Left Side - Login Form -->
        <div class="flex-1 flex items-center justify-center p-8">
            <div class="w-full max-w-md">
                <!-- Logo -->
                <div class="text-center mb-8">
                    <div class="inline-block p-4 bg-blue-100 rounded-2xl mb-4">
                        <i class="fas fa-chart-bar text-4xl text-blue-600"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-800">SIBUMA</h1>
                    <p class="text-gray-600 mt-2">Sistem Informasi Bisnis Usaha Madiun</p>
                </div>
                
                <!-- Login Card -->
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Login Admin</h2>
                    <p class="text-gray-600 mb-6">Masuk ke dashboard administrator</p>
                    
                    @if ($errors->any())
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                                <div>
                                    @foreach ($errors->all() as $error)
                                        <p class="text-sm text-red-700">{{ $error }}</p>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('admin.login.post') }}">
                        @csrf
                        
                        <!-- Email -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-envelope mr-2"></i>Email
                            </label>
                            <input 
                                type="email" 
                                name="email" 
                                value="{{ old('email') }}"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                placeholder="admin@bps.go.id"
                            >
                        </div>
                        
                        <!-- Password -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2"></i>Password
                            </label>
                            <input 
                                type="password" 
                                name="password" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                placeholder="••••••••"
                            >
                        </div>
                        
                        <!-- Lupa Password link -->
                        <div class="flex justify-end mb-2">
                            <a href="{{ route('admin.lupa-password') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                Lupa kata sandi?
                            </a>
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit"
                            class="w-full gradient-bg text-white font-semibold py-3 rounded-lg hover:shadow-lg transform hover:-translate-y-0.5 transition duration-200"
                        >
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Login
                        </button>
                    </form>