import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:sibuma_mobile/core/constants/api_constants.dart';

class PelaporanProvider with ChangeNotifier {
  final _storage = const FlutterSecureStorage();

  bool _isLoading = false;
  String _errorMessage = '';

  bool get isLoading => _isLoading;
  String get errorMessage => _errorMessage;

  void _setLoading(bool loading) {
    _isLoading = loading;
    _errorMessage = '';
    notifyListeners();
  }

  void _setError(String message) {
    _errorMessage = message;
    _isLoading = false;
    notifyListeners();
  }

  Future<String?> _getToken() async {
    return await _storage.read(key: 'auth_token');
  }

  // ─── Kirim Laporan Baru ────────────────────────────────
  Future<bool> kirimLaporan({
    required String jumlahRupiah,
    required String bulanTahun,
    String? catatan,
  }) async {
    _setLoading(true);
    try {
      final token = await _getToken();
      if (token == null) { _setError('Sesi Anda telah berakhir.'); return false; }

      final cleanJumlah = jumlahRupiah.replaceAll(RegExp(r'[^0-9]'), '');
      if (cleanJumlah.isEmpty) { _setError('Jumlah pendapatan tidak valid.'); return false; }

      final response = await http.post(
        Uri.parse(ApiConstants.pendapatan),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode({
          'jumlah': cleanJumlah,
          'bulan_tahun': bulanTahun,
          if (catatan != null && catatan.trim().isNotEmpty) 'catatan_laporan': catatan.trim(),
        }),
      );

      final data = jsonDecode(response.body);
      if (response.statusCode == 201) {
        _setLoading(false);
        return true;
      } else {
        _setError(data['message'] ?? 'Gagal mengirim laporan.');
        return false;
      }
    } catch (e) {
      _setError('Gagal terhubung ke server.');
      return false;
    }
  }

  // ─── Edit Laporan ──────────────────────────────────────
  Future<bool> editLaporan({
    required int laporanId,
    required String jumlahRupiah,
    required String bulanTahun,
    String? catatan,
  }) async {
    _setLoading(true);
    try {
      final token = await _getToken();
      if (token == null) { _setError('Sesi Anda telah berakhir.'); return false; }

      final cleanJumlah = jumlahRupiah.replaceAll(RegExp(r'[^0-9]'), '');
      if (cleanJumlah.isEmpty) { _setError('Jumlah pendapatan tidak valid.'); return false; }

      final headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      };
      final body = jsonEncode({
        'jumlah': cleanJumlah,
        'bulan_tahun': bulanTahun,
        if (catatan != null && catatan.trim().isNotEmpty) 'catatan_laporan': catatan.trim(),
      });

      final url = Uri.parse(ApiConstants.pendapatanById(laporanId));

      // Coba PUT dulu, fallback ke PATCH
      http.Response response = await http.put(url, headers: headers, body: body);
      if (response.statusCode == 404 || response.statusCode == 405) {
        response = await http.patch(url, headers: headers, body: body);
      }

      final data = jsonDecode(response.body);
      if (response.statusCode == 200) {
        _setLoading(false);
        return true;
      } else {
        _setError(data['message'] ?? 'Gagal mengubah laporan. (HTTP ${response.statusCode})');
        return false;
      }
    } catch (_) {
      _setError('Gagal terhubung ke server.');
      return false;
    }
  }

  // ─── ✅ BARU: Delete Laporan ───────────────────────────
  Future<bool> deleteLaporan(int laporanId) async {
    _setLoading(true);
    try {
      final token = await _getToken();
      if (token == null) { _setError('Sesi Anda telah berakhir.'); return false; }

      final response = await http.delete(
        Uri.parse(ApiConstants.pendapatanById(laporanId)),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200 || response.statusCode == 204) {
        _setLoading(false);
        return true;
      } else {
        final data = jsonDecode(response.body);
        _setError(data['message'] ?? 'Gagal menghapus laporan.');
        return false;
      }
    } catch (_) {
      _setError('Gagal terhubung ke server.');
      return false;
    }
  }
}