import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:sibuma_mobile/core/constants/api_constants.dart';

class StatistikData {
  final double total;
  final double rataRata;
  final double tertinggi;
  final double terendah;
  final int jumlahBulan;
  final String? bulanTerbaru;
  final double? pertumbuhanPct;
  final List<TrenItem> tren;

  StatistikData({
    required this.total,
    required this.rataRata,
    required this.tertinggi,
    required this.terendah,
    required this.jumlahBulan,
    this.bulanTerbaru,
    this.pertumbuhanPct,
    required this.tren,
  });

  factory StatistikData.fromJson(Map<String, dynamic> json) {
    final trenList = (json['tren'] as List<dynamic>? ?? [])
        .map((e) => TrenItem.fromJson(e as Map<String, dynamic>))
        .toList();
    return StatistikData(
      total: double.tryParse(json['total'].toString()) ?? 0,
      rataRata: double.tryParse(json['rata_rata'].toString()) ?? 0,
      tertinggi: double.tryParse(json['tertinggi'].toString()) ?? 0,
      terendah: double.tryParse(json['terendah'].toString()) ?? 0,
      jumlahBulan: json['jumlah_bulan'] ?? 0,
      bulanTerbaru: json['bulan_terbaru'],
      pertumbuhanPct: json['pertumbuhan_pct'] != null
          ? double.tryParse(json['pertumbuhan_pct'].toString())
          : null,
      tren: trenList,
    );
  }
}

class TrenItem {
  final String bulanTahun;
  final String bulanLabel;
  final double jumlah;

  TrenItem({required this.bulanTahun, required this.bulanLabel, required this.jumlah});

  factory TrenItem.fromJson(Map<String, dynamic> json) => TrenItem(
        bulanTahun: json['bulan_tahun'] ?? '',
        bulanLabel: json['bulan_label'] ?? '',
        jumlah: double.tryParse(json['jumlah'].toString()) ?? 0,
      );
}

class HomeProvider with ChangeNotifier {
  final _storage = const FlutterSecureStorage();

  bool _isLoading = false;
  String _errorMessage = '';
  bool _sudahLaporBulanIni = false;
  StatistikData? _statistik;

  static String get _currentMonthYear {
    final now = DateTime.now();
    return '${now.year}-${now.month.toString().padLeft(2, '0')}';
  }

  bool get isLoading => _isLoading;
  String get errorMessage => _errorMessage;
  bool get sudahLaporBulanIni => _sudahLaporBulanIni;
  StatistikData? get statistik => _statistik;

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

  Future<void> fetchDashboardData() async {
    _setLoading(true);
    try {
      final token = await _storage.read(key: 'auth_token');
      if (token == null) {
        _setError('Sesi tidak valid.');
        return;
      }

      final response = await http.get(
        Uri.parse(ApiConstants.pendapatanStatistics),
        headers: {'Accept': 'application/json', 'Authorization': 'Bearer $token'},
      );

      if (response.statusCode == 401) {
        await _storage.delete(key: 'auth_token');
        _setError('Sesi Anda telah berakhir. Silakan masuk kembali.');
        return;
      }

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        final d = data['data'];

        // Cek apakah sudah lapor bulan ini dari field tren
        final tren = (d['tren'] as List<dynamic>? ?? []);
        _sudahLaporBulanIni = tren.any((t) => t['bulan_tahun'] == _currentMonthYear);

        // Simpan data statistik
        _statistik = StatistikData.fromJson(d);

        _setLoading(false);
      } else {
        // Jika endpoint statistics belum ada datanya (usaha baru)
        _sudahLaporBulanIni = false;
        _statistik = null;
        _setLoading(false);
      }
    } catch (e) {
      _setError('Gagal terhubung ke server.');
    }
  }
}