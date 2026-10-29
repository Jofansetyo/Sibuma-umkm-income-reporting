import 'package:intl/intl.dart';

// Nama bulan Indonesia tanpa bergantung pada locale
const _namaBulanId = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];
const _singkatBulanId = [
  'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
  'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'
];

class LaporanModel {
  final int id;
  final int usahaId;
  final double jumlah;
  final String bulanTahun; // Format dari DB: "2026-02"
  final String jenisInput; // 'omzet' atau 'nilai_produk'
  final String status; // 'draft', 'terkirim', 'diverifikasi'
  final String? catatanLaporan; 
  final DateTime createdAt; // Menggunakan DateTime untuk presisi tinggi

  LaporanModel({
    required this.id,
    required this.usahaId,
    required this.jumlah,
    required this.bulanTahun,
    required this.jenisInput,
    required this.status,
    this.catatanLaporan,
    required this.createdAt,
  });

  // --- MENGUBAH JSON DARI LARAVEL MENJADI OBJEK DART ---
  factory LaporanModel.fromJson(Map<String, dynamic> json) {
    return LaporanModel(
      id: json['id'] ?? 0,
      usahaId: json['usaha_id'] ?? 0,
      // Konversi aman dari string/int JSON ke double Dart
      jumlah: double.tryParse(json['jumlah'].toString()) ?? 0.0,
      bulanTahun: json['bulan_tahun'] ?? '',
      jenisInput: json['jenis_input'] ?? 'omzet',
      status: json['status'] ?? 'terkirim',
      catatanLaporan: json['catatan_laporan'],
      
      // Parsing ISO 8601 Timestamp dari Laravel ke DateTime Flutter
      // Contoh input dari Laravel: "2026-02-23T11:21:23.000000Z"
      createdAt: json['created_at'] != null 
          ? DateTime.parse(json['created_at']) 
          : DateTime.now(),
    );
  }

  // --- HELPER 1: FORMAT RUPIAH MENGGUNAKAN PACKAGE INTL ---
  // Ini adalah cara standar dan paling akurat untuk format mata uang
  String get jumlahFormatted {
    final formatter = NumberFormat.currency(
      locale: 'id_ID', 
      symbol: 'Rp ', 
      decimalDigits: 0
    );
    try {
      return formatter.format(jumlah);
    } catch (_) {
      // Fallback manual jika locale belum init
      final parts = jumlah.toInt().toString().replaceAllMapped(
        RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.');
      return 'Rp $parts';
    }
  }

  // --- HELPER 2: FORMAT BULAN YANG RAMAH PENGGUNA ---
  // Mengubah "2026-02" menjadi "Februari 2026"
  String get bulanFormatted {
    if (bulanTahun.length != 7) return bulanTahun;
    try {
      final parts = bulanTahun.split('-');
      final bulanIndex = int.parse(parts[1]) - 1;
      final tahun = parts[0];
      if (bulanIndex < 0 || bulanIndex > 11) return bulanTahun;
      return '${_namaBulanId[bulanIndex]} $tahun';
    } catch (_) {
      return bulanTahun;
    }
  }
  
  // --- HELPER 3: FORMAT TANGGAL PENGIRIMAN DETAIL ---
  // Mengubah timestamp menjadi format lokal, misal: "23 Feb 2026, 11:21 WIB"
  String get tanggalKirimFormatted {
    try {
      final d = createdAt;
      final bulan = _singkatBulanId[d.month - 1];
      final tgl = d.day.toString().padLeft(2, '0');
      final jam = d.hour.toString().padLeft(2, '0');
      final menit = d.minute.toString().padLeft(2, '0');
      return '$tgl $bulan ${d.year}, $jam:$menit WIB';
    } catch (_) {
      return '-';
    }
  }
}