<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // <-- TAMBAHKAN INI
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/admin/login');
});

// Admin routes
Route::prefix('admin')->group(function () {
    // Login
    Route::get('/login', function () {
        return view('admin.login');
    })->name('admin.login');

    Route::post('/login', function (Illuminate\Http\Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            if ($user->role !== 'admin') {
                Auth::logout();
                return back()->withErrors(['email' => 'Anda tidak memiliki akses admin.']);
            }

            $request->session()->regenerate();
            return redirect()->intended('/admin/dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    })->name('admin.login.post');

    // Lupa password (publik, tanpa login)
    Route::get('/lupa-password', function () {
        return view('admin.lupa-password');
    })->name('admin.lupa-password');

    Route::post('/lupa-password', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'email'            => 'required|email',
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ], [
            'email.required'            => 'Email wajib diisi.',
            'current_password.required' => 'Kata sandi lama wajib diisi.',
            'new_password.required'     => 'Kata sandi baru wajib diisi.',
            'new_password.min'          => 'Kata sandi baru minimal 6 karakter.',
            'new_password.confirmed'    => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = \App\Models\User::where('email', $request->email)
                                ->where('role', 'admin')
                                ->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email admin tidak ditemukan.'])->withInput();
        }

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi lama tidak sesuai.'])->withInput();
        }

        $user->update(['password' => \Illuminate\Support\Facades\Hash::make($request->new_password)]);

        return redirect()->route('admin.lupa-password')
            ->with('success', 'Kata sandi berhasil diubah! Silakan login dengan kata sandi baru.');
    })->name('admin.lupa-password.post');

    Route::post('/logout', function (Illuminate\Http\Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin/login');
    })->name('admin.logout');

    // Protected admin routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/usaha', [DashboardController::class, 'usaha'])->name('admin.usaha');
        Route::get('/usaha/{id}', [DashboardController::class, 'detailUsaha'])->name('admin.usaha.detail');
        Route::get('/pendapatan', [DashboardController::class, 'pendapatan'])->name('admin.pendapatan');
        Route::get('/catatan', [DashboardController::class, 'catatan'])->name('admin.catatan');
        Route::get('/statistik', [DashboardController::class, 'statistik'])->name('admin.statistik');
    
        // Export
        Route::get('/export/pendapatan', [ExportController::class, 'exportPendapatan'])->name('admin.export.pendapatan');
        Route::get('/export/all', [ExportController::class, 'exportAll'])->name('admin.export.all');
        Route::get('/export/usaha', [ExportController::class, 'exportUsaha'])->name('admin.export.usaha');

        // Ganti Password Admin
        Route::get('/ganti-password', function () {
            return view('admin.ganti-password');
        })->name('admin.ganti-password');

        Route::post('/ganti-password', function (Illuminate\Http\Request $request) {
            $request->validate([
                'current_password'          => 'required',
                'new_password'              => 'required|min:6|confirmed',
            ], [
                'current_password.required' => 'Kata sandi lama wajib diisi.',
                'new_password.required'     => 'Kata sandi baru wajib diisi.',
                'new_password.min'          => 'Kata sandi baru minimal 6 karakter.',
                'new_password.confirmed'    => 'Konfirmasi kata sandi tidak cocok.',
            ]);

            $user = Auth::user();

            if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Kata sandi lama tidak sesuai.'])->withInput();
            }

            $user->update(['password' => \Illuminate\Support\Facades\Hash::make($request->new_password)]);

            return redirect()->route('admin.ganti-password')
                ->with('success', 'Kata sandi berhasil diubah!');
        })->name('admin.ganti-password.post');

        // Edit & Delete Pendapatan
        Route::get('/pendapatan/{id}/edit', [DashboardController::class, 'editPendapatan'])->name('admin.pendapatan.edit');
        Route::put('/pendapatan/{id}', [DashboardController::class, 'updatePendapatan'])->name('admin.pendapatan.update');
        Route::delete('/pendapatan/{id}', [DashboardController::class, 'deletePendapatan'])->name('admin.pendapatan.delete');
        Route::delete('/usaha/{id}', [DashboardController::class, 'deleteUsaha'])->name('admin.usaha.delete');
    });
});