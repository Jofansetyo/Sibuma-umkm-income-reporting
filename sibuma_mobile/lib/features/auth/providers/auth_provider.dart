import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:sibuma_mobile/core/constants/api_constants.dart';
import 'package:sibuma_mobile/features/auth/models/user_model.dart';

class AuthProvider with ChangeNotifier {
  final _storage = const FlutterSecureStorage();

  UserModel? _currentUser;
  bool _isLoading = false;
  String _errorMessage = '';

  UserModel? get currentUser => _currentUser;
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

  // ✅ FIX: Validasi token ke server /me, bukan hanya cek lokal
  Future<bool> validateSession() async {
    final token = await _storage.read(key: 'auth_token');
    if (token == null) return false;
    try {
      final response = await http.get(
        Uri.parse(ApiConstants.me),
        headers: {'Accept': 'application/json', 'Authorization': 'Bearer $token'},
      ).timeout(const Duration(seconds: 8));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        _currentUser = UserModel.fromJson(data['data']);
        notifyListeners();
        return true;
      } else {
        // Token tidak valid, hapus dari storage
        await _storage.delete(key: 'auth_token');
        return false;
      }
    } catch (_) {
      // Tidak bisa konek server — anggap session masih valid (offline mode)
      return token.isNotEmpty;
    }
  }

  // ✅ Tetap ada checkLoginStatus untuk backward compat
  Future<bool> checkLoginStatus() async {
    return validateSession();
  }

  Future<bool> register({
    required String name, required String email, required String password,
    required String namaUsaha, required String noWa, required String alamat,
    required String kategori, required String jenisPendapatan, String? catatan,
  }) async {
    _setLoading(true);
    try {
      final response = await http.post(
        Uri.parse(ApiConstants.register),
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({
          'name': name, 'email': email, 'password': password,
          'password_confirmation': password, 'nama_usaha': namaUsaha,
          'no_wa': noWa, 'alamat': alamat, 'kategori': kategori,
          'jenis_pendapatan': jenisPendapatan,
          if (catatan != null && catatan.isNotEmpty) 'catatan_pendaftaran': catatan,
        }),
      );
      final data = jsonDecode(response.body);
      if (response.statusCode == 201) {
        await _storage.write(key: 'auth_token', value: data['data']['access_token']);
        _currentUser = UserModel.fromJson(data['data']);
        _setLoading(false);
        return true;
      } else {
        _setError(data['message'] ?? 'Pendaftaran gagal.');
        return false;
      }
    } catch (_) {
      _setError('Tidak dapat terhubung ke server.');
      return false;
    }
  }

  Future<bool> login(String email, String password) async {
    _setLoading(true);
    try {
      final response = await http.post(
        Uri.parse(ApiConstants.login),
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({'email': email, 'password': password}),
      );
      final data = jsonDecode(response.body);
      if (response.statusCode == 200) {
        await _storage.write(key: 'auth_token', value: data['data']['access_token']);
        _currentUser = UserModel.fromJson(data['data']);
        _setLoading(false);
        return true;
      } else {
        _setError(data['message'] ?? 'Email atau kata sandi salah.');
        return false;
      }
    } catch (_) {
      _setError('Gagal terhubung ke server.');
      return false;
    }
  }

  // ✅ FIX: Update name user juga, tidak hanya usaha
  Future<bool> updateProfil({
    required String name,
    required String namaUsaha,
    required String noWa,
    required String alamat,
    required String kategori,
  }) async {
    _setLoading(true);
    try {
      final token = await _storage.read(key: 'auth_token');
      final usahaId = _currentUser?.usahaId;

      if (usahaId == null) {
        _setError('ID usaha tidak ditemukan.');
        return false;
      }

      // Update usaha
      final usahaResponse = await http.put(
        Uri.parse(ApiConstants.usahaById(usahaId)),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode({
          'nama_usaha': namaUsaha,
          'no_wa': noWa,
          'alamat': alamat,
          'kategori': kategori,
          'name': name, // kirim name juga agar backend bisa update user
        }),
      );

      if (usahaResponse.statusCode == 200) {
        await _refreshCurrentUser();
        _setLoading(false);
        return true;
      } else {
        final data = jsonDecode(usahaResponse.body);
        _setError(data['message'] ?? 'Gagal memperbarui profil.');
        return false;
      }
    } catch (_) {
      _setError('Tidak dapat terhubung ke server.');
      return false;
    }
  }

  Future<bool> ubahSandi({required String sandiLama, required String sandiBaru}) async {
    _setLoading(true);
    try {
      final token = await _storage.read(key: 'auth_token');
      final response = await http.post(
        Uri.parse(ApiConstants.changePassword),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode({
          'current_password': sandiLama,
          'new_password': sandiBaru,
          'new_password_confirmation': sandiBaru,
        }),
      );
      final data = jsonDecode(response.body);
      if (response.statusCode == 200) {
        _setLoading(false);
        return true;
      } else {
        _setError(data['message'] ?? 'Gagal mengubah kata sandi.');
        return false;
      }
    } catch (_) {
      _setError('Tidak dapat terhubung ke server.');
      return false;
    }
  }

  Future<bool> lupaSandi({required String email}) async {
    _setLoading(true);
    try {
      final response = await http.post(
        Uri.parse(ApiConstants.forgotPassword),
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({'email': email}),
      );
      final data = jsonDecode(response.body);
      if (response.statusCode == 200) {
        _setLoading(false);
        return true;
      } else {
        _setError(data['message'] ?? 'Email tidak ditemukan.');
        return false;
      }
    } catch (_) {
      _setError('Tidak dapat terhubung ke server.');
      return false;
    }
  }

  Future<void> _refreshCurrentUser() async {
    try {
      final token = await _storage.read(key: 'auth_token');
      final response = await http.get(
        Uri.parse(ApiConstants.me),
        headers: {'Accept': 'application/json', 'Authorization': 'Bearer $token'},
      );
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        _currentUser = UserModel.fromJson(data['data']);
        notifyListeners();
      }
    } catch (_) {}
  }

  Future<void> logout() async {
    try {
      final token = await _storage.read(key: 'auth_token');
      // Beritahu server untuk revoke token
      await http.post(
        Uri.parse(ApiConstants.logout),
        headers: {'Accept': 'application/json', 'Authorization': 'Bearer $token'},
      );
    } catch (_) {}
    await _storage.delete(key: 'auth_token');
    _currentUser = null;
    notifyListeners();
  }
}