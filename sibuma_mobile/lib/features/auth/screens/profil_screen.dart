import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:sibuma_mobile/core/theme/app_theme.dart';
import 'package:sibuma_mobile/features/auth/providers/auth_provider.dart';
import 'package:sibuma_mobile/features/auth/screens/edit_profil_screen.dart';
import 'package:sibuma_mobile/features/auth/screens/login_screen.dart';
import 'package:sibuma_mobile/features/auth/screens/ubah_sandi_screen.dart';

class ProfilScreen extends StatelessWidget {
  const ProfilScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final user = auth.currentUser;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Profil & Pengaturan Akun'),
        actions: [
          IconButton(
            icon: const Icon(Icons.edit_outlined),
            tooltip: 'Edit Profil',
            onPressed: () => Navigator.push(context,
                MaterialPageRoute(builder: (_) => const EditProfilScreen())),
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(vertical: 16.0),
        child: Column(
          children: [
            // --- HEADER PROFIL ---
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16.0),
              child: Center(
                child: Column(
                  children: [
                    Stack(
                      children: [
                        Container(
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            boxShadow: [
                              BoxShadow(
                                  color:
                                      AppTheme.primary.withValues(alpha: 0.3),
                                  blurRadius: 20,
                                  offset: const Offset(0, 8))
                            ],
                          ),
                          child: const CircleAvatar(
                            radius: 50,
                            backgroundColor: AppTheme.primary,
                            child: Icon(Icons.person,
                                size: 50, color: Colors.white),
                          ),
                        ),
                        Positioned(
                          bottom: 0,
                          right: 0,
                          child: GestureDetector(
                            onTap: () => Navigator.push(
                                context,
                                MaterialPageRoute(
                                    builder: (_) => const EditProfilScreen())),
                            child: Container(
                              padding: const EdgeInsets.all(6),
                              decoration: const BoxDecoration(
                                  color: AppTheme.accent,
                                  shape: BoxShape.circle),
                              child: const Icon(Icons.edit,
                                  size: 14, color: Colors.white),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    Text(user?.name ?? 'Nama Pengguna',
                        style: GoogleFonts.plusJakartaSans(
                            fontSize: 24,
                            fontWeight: FontWeight.w800,
                            color: AppTheme.textPrimary)),
                    const SizedBox(height: 4),
                    Text(user?.email ?? 'email@perusahaan.com',
                        style: AppTheme.bodySmall.copyWith(fontSize: 15)),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 32),

            // --- INFORMASI USAHA ---
            _buildSection(title: 'Informasi Usaha', children: [
              _buildInfoRow(Icons.business_center_outlined, 'Nama Usaha',
                  user?.namaUsaha ?? '-'),
              _buildInfoRow(Icons.phone_iphone_rounded, 'Nomor WhatsApp',
                  user?.noWa ?? '-'),
              _buildInfoRow(
                  Icons.location_on_outlined, 'Alamat', user?.alamat ?? '-'),
            ]),

            // --- PENGATURAN PELAPORAN ---
            _buildSection(title: 'Pengaturan Pelaporan', children: [
              _buildInfoRow(Icons.category_outlined, 'Kategori Usaha',
                  user?.kategoriFormatted ?? '-'),
              _buildInfoRow(
                  Icons.monetization_on_outlined,
                  'Jenis Pelaporan Utama',
                  user?.jenisPendapatanFormatted ?? '-'),
            ]),

            // --- TOMBOL EDIT PROFIL ---
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
              child: OutlinedButton.icon(
                onPressed: () => Navigator.push(
                    context,
                    MaterialPageRoute(
                        builder: (_) => const EditProfilScreen())),
                icon: const Icon(Icons.edit_outlined),
                label: Text('Edit Profil',
                    style: GoogleFonts.plusJakartaSans(
                        fontWeight: FontWeight.w600)),
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppTheme.accent,
                  side: const BorderSide(color: AppTheme.accent),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12)),
                ),
              ),
            ),

            // --- TOMBOL UBAH SANDI ---
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 4, 16, 8),
              child: OutlinedButton.icon(
                onPressed: () => Navigator.push(context,
                    MaterialPageRoute(builder: (_) => const UbahSandiScreen())),
                icon: const Icon(Icons.lock_outline_rounded),
                label: Text('Ubah Kata Sandi',
                    style: GoogleFonts.plusJakartaSans(
                        fontWeight: FontWeight.w600)),
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppTheme.warning,
                  side: const BorderSide(color: AppTheme.warning),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12)),
                ),
              ),
            ),

            // --- TOMBOL LOGOUT ---
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 4, 16, 16),
              child: OutlinedButton.icon(
                onPressed: () => _showLogoutDialog(context),
                icon: const Icon(Icons.logout),
                label: Text('Keluar dari Akun',
                    style: GoogleFonts.plusJakartaSans(
                        fontWeight: FontWeight.w600)),
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppTheme.danger,
                  side: const BorderSide(color: AppTheme.danger),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSection(
      {required String title, required List<Widget> children}) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.only(left: 4.0, bottom: 8.0),
            child: Text(title.toUpperCase(),
                style: GoogleFonts.plusJakartaSans(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.textSecondary,
                    letterSpacing: 1.2)),
          ),
          Card(
            child: Padding(
              padding:
                  const EdgeInsets.symmetric(vertical: 8.0, horizontal: 16.0),
              child: Column(children: children),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String title, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 14.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: AppTheme.primary, size: 22),
          const SizedBox(width: 20),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: AppTheme.bodySmall),
                const SizedBox(height: 4),
                Text(value,
                    style: GoogleFonts.plusJakartaSans(
                        fontSize: 15,
                        fontWeight: FontWeight.w600,
                        color: AppTheme.textPrimary)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  void _showLogoutDialog(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text('Keluar dari Akun?',
            style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.w700)),
        content: Text('Anda harus masuk kembali untuk melanjutkan.',
            style: AppTheme.body),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: Text('Batal',
                  style: GoogleFonts.plusJakartaSans(
                      fontWeight: FontWeight.w600))),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppTheme.danger),
            onPressed: () async {
              Navigator.pop(ctx);
              await authProvider.logout();
              if (!context.mounted) return;
              Navigator.pushAndRemoveUntil(
                  context,
                  MaterialPageRoute(builder: (_) => const LoginScreen()),
                  (route) => false);
            },
            child: Text('Ya, Keluar',
                style: AppTheme.button.copyWith(color: Colors.white)),
          ),
        ],
      ),
    );
  }
}
