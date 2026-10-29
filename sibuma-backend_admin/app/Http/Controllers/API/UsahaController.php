<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Usaha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UsahaController extends Controller
{
    // Get usaha milik user yang login
    public function index(Request $request)
    {
        $usaha = Usaha::where('user_id', $request->user()->id)->get();
        return response()->json([
            'success' => true,
            'data' => $usaha
        ], 200);
    }

    // Update usaha (Edit Profil dari Flutter)
    public function update(Request $request, $id)
    {
        $usaha = Usaha::where('id', $id)
                      ->where('user_id', $request->user()->id)
                      ->first();

        if (!$usaha) {
            return response()->json([
                'success' => false,
                'message' => 'Data usaha tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_usaha' => 'required|string|max:255',
            'no_wa' => 'required|string|max:20',
            'alamat' => 'required|string',
            'kategori' => 'required|in:industri,jasa,perdagangan',
            'jenis_pendapatan' => 'nullable|in:omzet,nilai_produk,nilai_jasa', // ✅ tambah nilai_jasa
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $kodeKategori = [
            'industri' => 'IND',
            'jasa' => 'JSA',
            'perdagangan' => 'PDG',
        ];

        $dataUpdate = $request->only(['nama_usaha', 'no_wa', 'alamat', 'kategori']);
        $dataUpdate['kode_kategori'] = $kodeKategori[$request->kategori];

        // Hanya update jenis_pendapatan jika dikirim
        if ($request->filled('jenis_pendapatan')) {
            $dataUpdate['jenis_pendapatan'] = $request->jenis_pendapatan;
        }

        $usaha->update($dataUpdate);

        // ✅ Return user + usaha agar Flutter bisa refresh profil
        return response()->json([
            'success' => true,
            'message' => 'Profil usaha berhasil diupdate',
            'data' => [
                'user'  => $request->user(),
                'usaha' => $usaha,
            ]
        ], 200);
    }

    // Delete usaha
    public function destroy(Request $request, $id)
    {
        $usaha = Usaha::where('id', $id)
                      ->where('user_id', $request->user()->id)
                      ->first();

        if (!$usaha) {
            return response()->json([
                'success' => false,
                'message' => 'Data usaha tidak ditemukan'
            ], 404);
        }

        $usaha->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data usaha berhasil dihapus'
        ], 200);
    }

    // Get semua usaha (untuk admin)
    public function getAllUsaha()
    {
        $usaha = Usaha::with('user', 'pendapatans')->get();
        return response()->json([
            'success' => true,
            'data' => $usaha
        ], 200);
    }

    // Get detail usaha by ID
    public function show($id)
    {
        $usaha = Usaha::with('pendapatans')->find($id);

        if (!$usaha) {
            return response()->json([
                'success' => false,
                'message' => 'Data usaha tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $usaha
        ], 200);
    }
}