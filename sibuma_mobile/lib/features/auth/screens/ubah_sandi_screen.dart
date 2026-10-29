import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:sibuma_mobile/core/theme/app_theme.dart';
import 'package:sibuma_mobile/features/auth/providers/auth_provider.dart';

class UbahSandiScreen extends StatefulWidget {
  const UbahSandiScreen({super.key});

  @override
  State<UbahSandiScreen> createState() => _UbahSandiScreenState();
}

class _UbahSandiScreenState extends State<UbahSandiScreen> {
  final _formKey = GlobalKey<FormState>();
  final _sandiLamaController = TextEditingController();
  final _sandiBaruController = TextEditingController();
  final _konfirmasiController = TextEditingController();
  bool _obscureLama = true;
  bool _obscureBaru = true;
  bool _obscureKonfirmasi = true;

  @override
  void dispose() {
    _sandiLamaController.dispose();
    _sandiBaruController.dispose();
    _konfirmasiController.dispose();
    super.dispose();
  }

  void _simpan() async {
    if (!_formKey.currentState!.validate()) return;
    FocusScope.of(context).unfocus();

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final success = await authProvider.ubahSandi(
      sandiLama: _sandiLamaController.text,
      sandiBaru: _sandiBaruController.text,
    );

    if (!mounted) return;

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
            content: Text('Kata sandi berhasil diubah!'),
            backgroundColor: AppTheme.success),
      );
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
            content: Text(authProvider.errorMessage),
            backgroundColor: AppTheme.danger),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Ubah Kata Sandi')),
      body: GestureDetector(
        onTap: () => FocusScope.of(context).unfocus(),
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24.0),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppTheme.primary.withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                        color: AppTheme.primary.withValues(alpha: 0.2)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.lock_outline_rounded,
                          color: AppTheme.primary, size: 24),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          'Gunakan minimal 6 karakter untuk kata sandi baru Anda.',
                          style: GoogleFonts.dmSans(
                              color: AppTheme.textPrimary, fontSize: 13),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 32),
                _buildLabel('Kata Sandi Lama'),
                TextFormField(
                  controller: _sandiLamaController,
                  obscureText: _obscureLama,
                  decoration: InputDecoration(
                    hintText: 'Masukkan kata sandi lama',
                    suffixIcon: IconButton(
                      icon: Icon(_obscureLama
                          ? Icons.visibility_off_outlined
                          : Icons.visibility_outlined),
                      onPressed: () =>
                          setState(() => _obscureLama = !_obscureLama),
                    ),
                  ),
                  validator: (val) => val == null || val.isEmpty
                      ? 'Kata sandi lama tidak boleh kosong'
                      : null,
                ),
                const SizedBox(height: 24),
                _buildLabel('Kata Sandi Baru'),
                TextFormField(
                  controller: _sandiBaruController,
                  obscureText: _obscureBaru,
                  decoration: InputDecoration(
                    hintText: 'Minimal 6 karakter',
                    suffixIcon: IconButton(
                      icon: Icon(_obscureBaru
                          ? Icons.visibility_off_outlined
                          : Icons.visibility_outlined),
                      onPressed: () =>
                          setState(() => _obscureBaru = !_obscureBaru),
                    ),
                  ),
                  validator: (val) {
                    if (val == null || val.isEmpty)
                      return 'Kata sandi baru tidak boleh kosong';
                    if (val.length < 6) return 'Minimal 6 karakter';
                    return null;
                  },
                ),
                const SizedBox(height: 24),
                _buildLabel('Konfirmasi Kata Sandi Baru'),
                TextFormField(
                  controller: _konfirmasiController,
                  obscureText: _obscureKonfirmasi,
                  decoration: InputDecoration(
                    hintText: 'Ulangi kata sandi baru',
                    suffixIcon: IconButton(
                      icon: Icon(_obscureKonfirmasi
                          ? Icons.visibility_off_outlined
                          : Icons.visibility_outlined),
                      onPressed: () => setState(
                          () => _obscureKonfirmasi = !_obscureKonfirmasi),
                    ),
                  ),
                  validator: (val) {
                    if (val == null || val.isEmpty)
                      return 'Konfirmasi tidak boleh kosong';
                    if (val != _sandiBaruController.text)
                      return 'Kata sandi tidak cocok';
                    return null;
                  },
                ),
                const SizedBox(height: 48),
                Consumer<AuthProvider>(
                  builder: (context, auth, child) {
                    return ElevatedButton(
                      onPressed: auth.isLoading ? null : _simpan,
                      child: auth.isLoading
                          ? const CircularProgressIndicator(
                              color: Colors.white, strokeWidth: 2)
                          : Text('SIMPAN KATA SANDI BARU',
                              style: GoogleFonts.plusJakartaSans(
                                  fontWeight: FontWeight.w700,
                                  letterSpacing: 0.8,
                                  color: Colors.white)),
                    );
                  },
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildLabel(String label) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8.0),
      child: Text(label,
          style: GoogleFonts.plusJakartaSans(
              fontSize: 14,
              fontWeight: FontWeight.w600,
              color: AppTheme.textPrimary)),
    );
  }
}
