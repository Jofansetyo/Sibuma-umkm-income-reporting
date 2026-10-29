<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Usaha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register new user with usaha
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:users',
            'password'              => 'required|string|min:6|confirmed',
            'nama_usaha'            => 'required|string|max:255',
            'no_wa'                 => 'required|string|max:20',
            'alamat'                => 'required|string',
            'kategori'              => 'required|in:industri,jasa,perdagangan',
            // BUG FIX: tambahkan nilai_jasa agar konsisten dengan migration & UsahaController
            'jenis_pendapatan'      => 'required|in:omzet,nilai_produk,nilai_jasa',
            'catatan_pendaftaran'   => 'nullable|string',
        ]);

        $kodeKategori = [
            'industri'    => 'IND',
            'jasa'        => 'JSA',
            'perdagangan' => 'PDG',
        ];

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'responden',
        ]);

        $usaha = Usaha::create([
            'user_id'               => $user->id,
            'nama_usaha'            => $request->nama_usaha,
            'no_wa'                 => $request->no_wa,
            'alamat'                => $request->alamat,
            'kategori'              => $request->kategori,
            'kode_kategori'         => $kodeKategori[$request->kategori],
            'jenis_pendapatan'      => $request->jenis_pendapatan,
            'catatan_pendaftaran'   => $request->catatan_pendaftaran ?? null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'data' => [
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => $user,
                'usaha'        => $usaha,
            ]
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $usaha = null;
        if ($user->role === 'responden') {
            $usaha = Usaha::where('user_id', $user->id)->first();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => $user,
                'usaha'        => $usaha,
            ]
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    /**
     * Get current user
     */
    public function me(Request $request)
    {
        $user  = $request->user();
        $usaha = null;

        if ($user->role === 'responden') {
            $usaha = Usaha::where('user_id', $user->id)->first();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user'  => $user,
                'usaha' => $usaha,
            ]
        ]);
    }
}