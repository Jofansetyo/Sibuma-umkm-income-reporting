class UserModel {
  final int id;
  final String name;
  final String email;
  final String role;

  // ✅ Tambah usahaId untuk keperluan update
  final int? usahaId;

  final String? namaUsaha;
  final String? noWa;
  final String? alamat;
  final String? kategori;
  final String? jenisPendapatan;
  final String? catatanPendaftaran;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.usahaId,
    this.namaUsaha,
    this.noWa,
    this.alamat,
    this.kategori,
    this.jenisPendapatan,
    this.catatanPendaftaran,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    final userJson = json['user'] ?? json;
    final usahaJson = json['usaha'];

    return UserModel(
      id: userJson['id'] ?? 0,
      name: userJson['name'] ?? 'Pengguna',
      email: userJson['email'] ?? '',
      role: userJson['role'] ?? 'responden',
      usahaId: usahaJson?['id'],         // ✅ Ambil id usaha
      namaUsaha: usahaJson?['nama_usaha'],
      noWa: usahaJson?['no_wa'],
      alamat: usahaJson?['alamat'],
      kategori: usahaJson?['kategori'],
      jenisPendapatan: usahaJson?['jenis_pendapatan'],
      catatanPendaftaran: usahaJson?['catatan_pendaftaran'],
    );
  }

  String get kategoriFormatted {
    if (kategori == null) return '-';
    return kategori![0].toUpperCase() + kategori!.substring(1);
  }

  String get jenisPendapatanFormatted {
    if (jenisPendapatan == null) return '-';
    if (jenisPendapatan == 'nilai_produk') return 'Nilai Produk';
    if (jenisPendapatan == 'nilai_jasa') return 'Nilai Jasa';
    if (jenisPendapatan == 'omzet') return 'Omzet';
    return jenisPendapatan!;
  }
}
