import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:sibuma_mobile/core/constants/api_constants.dart';
import 'package:sibuma_mobile/features/pelaporan/models/laporan_model.dart';

class RiwayatProvider with ChangeNotifier {
  final _storage = const FlutterSecureStorage();
  
  bool _isLoading = false;
  String _errorMessage = '';
  List<LaporanModel> _listRiwayat = [];

  bool get isLoading => _isLoading;
  String get errorMessage => _errorMessage;
  List<LaporanModel> get listRiwayat => _listRiwayat;

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

  Future<void> fetchRiwayat() async {
    _setLoading(true);
    try {
      String? token = await _storage.read(key: 'auth_token');
      if (token == null) {
        _setError('Sesi Anda telah berakhir.');
        return;
      }
      final response = await http.get(
        Uri.parse('${ApiConstants.baseUrl}/pendapatan'), 
        headers: {'Accept': 'application/json', 'Authorization': 'Bearer $token'},
      );
      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        final List<dynamic> rawData = data['data'];
        _listRiwayat = rawData.map((item) => LaporanModel.fromJson(item)).toList();
        _setLoading(false);
      } else {
        _setError(data['message'] ?? 'Gagal memuat riwayat.');
      }
    } catch (e) {
      _setError('Gagal terhubung ke server. Error: $e');
    }
  }
}
