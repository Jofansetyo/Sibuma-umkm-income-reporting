import 'package:intl/intl.dart';

const _namaBulanId = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];
const _singkatBulanId = [
  'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
  'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'
];

class AppFormatters {
  // Mengubah angka double menjadi format Rupiah "Rp 50.000.000"
  static String formatRupiah(double amount) {
    try {
      final formatter = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);
      return formatter.format(amount);
    } catch (_) {
      final parts = amount.toInt().toString().replaceAllMapped(
        RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.');
      return 'Rp $parts';
    }
  }

  // Mengubah "2026-02" menjadi "Februari 2026" — tanpa bergantung locale
  static String formatBulanTahun(String dbBulanTahun) {
    if (dbBulanTahun.length != 7) return dbBulanTahun;
    try {
      final parts = dbBulanTahun.split('-');
      final bulanIndex = int.parse(parts[1]) - 1;
      if (bulanIndex < 0 || bulanIndex > 11) return dbBulanTahun;
      return '${_namaBulanId[bulanIndex]} ${parts[0]}';
    } catch (_) {
      return dbBulanTahun;
    }
  }

  // Mengubah timestamp menjadi "24 Feb 2026, 11:30 WIB" — tanpa bergantung locale
  static String formatDetailTanggal(DateTime date) {
    final bulan = _singkatBulanId[date.month - 1];
    final tgl = date.day.toString().padLeft(2, '0');
    final jam = date.hour.toString().padLeft(2, '0');
    final menit = date.minute.toString().padLeft(2, '0');
    return '$tgl $bulan ${date.year}, $jam:$menit WIB';
  }
}