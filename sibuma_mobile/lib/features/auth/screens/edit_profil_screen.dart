import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:sibuma_mobile/core/theme/app_theme.dart';
import 'package:sibuma_mobile/features/auth/providers/auth_provider.dart';

class EditProfilScreen extends StatefulWidget {
  const EditProfilScreen({super.key});

  @override
  State<EditProfilScreen> createState() => _EditProfilScreenState();
}

class _EditProfilScreenState extends State<EditProfilScreen> {
  final _formKey = GlobalKey<FormState>();
  late TextEditingController _nameController;
  late TextEditingController _namaUsahaController;
  late TextEditingController _waController;
  late TextEditingController _alamatController;
  String? _selectedKategori;

  @override
  void initState() {
    super.initState();
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    _nameController = TextEditingController(text: user?.name ?? '');
    _namaUsahaController = TextEditingController(text: user?.namaUsaha ?? '');
    _waController = TextEditingController(text: user?.noWa ?? '');
    _alamatController = TextEditingController(text: user?.alamat ?? '');
    _selectedKategori = user?.kategori;
  }

  @override
  void dispose() {
    _nameController.dispose();
    _namaUsahaController.dispose();
    _waController.dispose();
    _alamatController.dispose();
    super.dispose();
  }

  void _simpan() async {
    if (!_formKey.currentState!.validate()) return;
    FocusScope.of(context).unfocus();

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final success = await authProvider.updateProfil(
      name: _nameController.text.trim(),
      namaUsaha: _namaUsahaController.text.trim(),
      noWa: _waController.text.trim(),
      alamat: _alamatController.text.trim(),
      kategori: _selectedKategori!,
    );

    if (!mounted) return;

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Profil berhasil diperbarui!'), backgroundColor: AppTheme.success),
      );
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(authProvider.errorMessage), backgroundColor: AppTheme.danger),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Edit Profil')),
      body: GestureDetector(
        onTap: () => FocusScope.of(context).unfocus(),
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24.0),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // === SECTION: AKUN ===
                _buildSectionHeader('Data Akun'),
                const SizedBox(height: 12),
                _buildLabel('Nama Lengkap / PIC'),
                TextFormField(
                  controller: _nameController,
                  decoration: const InputDecoration(hintText: 'Cth: Budi Santoso'),
                  validator: (val) => val == null || val.isEmpty ? 'Nama tidak boleh kosong' : null,
                ),
                const SizedBox(height: 24),

                // === SECTION: USAHA ===
                _buildSectionHeader('Data Usaha'),
                const SizedBox(height: 12),
                _buildLabel('Nama Usaha'),
                TextFormField(
                  controller: _namaUsahaController,
                  decoration: const InputDecoration(hintText: 'Cth: PT Maju Jaya'),
                  validator: (val) => val == null || val.isEmpty ? 'Nama usaha tidak boleh kosong' : null,
                ),
                const SizedBox(height: 20),
                _buildLabel('Nomor WhatsApp'),
                TextFormField(
                  controller: _waController,
                  keyboardType: TextInputType.phone,
                  decoration: const InputDecoration(hintText: 'Cth: 08123456789'),
                  validator: (val) => val == null || val.isEmpty ? 'Nomor WhatsApp tidak boleh kosong' : null,
                ),
                const SizedBox(height: 20),
                _buildLabel('Alamat Lengkap Usaha'),
                TextFormField(
                  controller: _alamatController,
                  maxLines: 3,
                  decoration: const InputDecoration(hintText: 'Cth: Jl. Pahlawan No. 1, Madiun'),
                  validator: (val) => val == null || val.isEmpty ? 'Alamat tidak boleh kosong' : null,
                ),
                const SizedBox(height: 20),
                _buildLabel('Kategori Usaha'),
                DropdownButtonFormField<String>(
                  initialValue: _selectedKategori,
                  decoration: const InputDecoration(hintText: 'Pilih kategori usaha'),
                  items: const [
                    DropdownMenuItem(value: 'industri', child: Text('Industri / Manufaktur')),
                    DropdownMenuItem(value: 'jasa', child: Text('Jasa')),
                    DropdownMenuItem(value: 'perdagangan', child: Text('Perdagangan')),
                  ],
                  onChanged: (value) => setState(() => _selectedKategori = value),
                  validator: (val) => val == null ? 'Kategori wajib dipilih' : null,
                ),
                const SizedBox(height: 48),

                // === TOMBOL SIMPAN ===
                Consumer<AuthProvider>(
                  builder: (context, auth, child) {
                    return ElevatedButton(
                      onPressed: auth.isLoading ? null : _simpan,
                      child: auth.isLoading
                          ? const CircularProgressIndicator(color: Colors.white, strokeWidth: 2)
                          : Text('SIMPAN PERUBAHAN',
                              style: GoogleFonts.plusJakartaSans(
                                  fontWeight: FontWeight.w700, letterSpacing: 0.8, color: Colors.white)),
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

  Widget _buildSectionHeader(String title) {
    return Text(
      title.toUpperCase(),
      style: GoogleFonts.plusJakartaSans(
        fontSize: 11, fontWeight: FontWeight.w700,
        color: AppTheme.textSecondary, letterSpacing: 1.2,
      ),
    );
  }

  Widget _buildLabel(String label) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8.0),
      child: Text(label,
          style: GoogleFonts.plusJakartaSans(
              fontSize: 14, fontWeight: FontWeight.w600, color: AppTheme.textPrimary)),
    );
  }
}
