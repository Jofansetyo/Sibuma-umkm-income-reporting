class ApiConstants {
  // Lokal Laragon - ganti IP ini setiap kali pindah WiFi (cek dengan `ipconfig`)
  static const String baseUrl = 'http://10.101.87.227:8000/api';

  // === Autentikasi ===
  static const String login            = '$baseUrl/login';
  static const String register         = '$baseUrl/register';
  static const String logout           = '$baseUrl/logout';
  static const String me               = '$baseUrl/me';
  static const String forgotPassword   = '$baseUrl/password/forgot';
  static const String changePassword   = '$baseUrl/password/change';

  // === Pendapatan / Laporan ===
  static const String pendapatan           = '$baseUrl/pendapatan';
  static const String pendapatanStatistics = '$baseUrl/pendapatan/statistics';

  // Endpoint dengan ID dinamis
  static String pendapatanById(int id) => '$baseUrl/pendapatan/$id';

  // === Usaha ===
  static String usahaById(int id) => '$baseUrl/usaha/$id';
}