import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:sibuma_mobile/core/theme/app_theme.dart';
import 'package:sibuma_mobile/core/utils/validators.dart';
import 'package:sibuma_mobile/features/auth/providers/auth_provider.dart';
import 'package:sibuma_mobile/features/home/screens/home_screen.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final PageController _pageController = PageController();
  final _formKey1 = GlobalKey<FormState>();
  final _formKey2 = GlobalKey<FormState>();

  // --- Step 1 Controllers ---
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();

  // --- Step 2 Controllers ---
  final _namaUsahaController = TextEditingController();
  final _waController = TextEditingController();
  final _alamatController = TextEditingController();
  String? _selectedKategori;
  String? _selectedPendapatan;

  int _currentPage = 0;

  @override
  void dispose() {
    // --- 👇 BAGIAN INI SUDAH DIISI LENGKAP 👇 ---
    _pageController.dispose();
    _nameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _namaUsahaController.dispose();
    _waController.dispose();
    _alamatController.dispose();
    super.dispose();
  }

  void _nextPage() {
    if (_formKey1.currentState!.validate()) {
      _pageController.nextPage(
          duration: const Duration(milliseconds: 300), curve: Curves.easeOut);
    }
  }

  void _previousPage() {
    _pageController.previousPage(
        duration: const Duration(milliseconds: 300), curve: Curves.easeOut);
  }

  void _submitRegister() async {
    // --- 👇 BAGIAN INI SUDAH DIISI LENGKAP 👇 ---
    if (!_formKey2.currentState!.validate()) return;
    if (_selectedKategori == null || _selectedPendapatan == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Kategori dan Jenis Pelaporan wajib dipilih'),
          backgroundColor: AppTheme.danger,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    FocusScope.of(context).unfocus();
    final authProvider = Provider.of<AuthProvider>(context, listen: false);

    final success = await authProvider.register(
      name: _nameController.text.trim(),
      email: _emailController.text.trim(),
      password: _passwordController.text,
      namaUsaha: _namaUsahaController.text.trim(),
      noWa: _waController.text.trim(),
      alamat: _alamatController.text.trim(),
      kategori: _selectedKategori!,
      jenisPendapatan: _selectedPendapatan!,
    );

    if (!mounted) return;

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Registrasi Berhasil! Selamat datang!'),
          backgroundColor: AppTheme.success,
          behavior: SnackBarBehavior.floating,
        ),
      );
      // ✅ FIX: Langsung masuk HomeScreen, tidak perlu login lagi
      Navigator.pushAndRemoveUntil(
          context,
          MaterialPageRoute(builder: (_) => const HomeScreen()),
          (route) => false);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(authProvider.errorMessage),
          backgroundColor: AppTheme.danger,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Pendaftaran Baru (${_currentPage + 1}/2)'),
        leading: _currentPage == 0
            ? IconButton(
                icon: const Icon(Icons.close),
                onPressed: () => Navigator.of(context).pop(),
              )
            : IconButton(
                icon: const Icon(Icons.arrow_back_ios_new, size: 20),
                onPressed: _previousPage,
              ),
      ),
      body: PageView(
        controller: _pageController,
        physics: const NeverScrollableScrollPhysics(),
        onPageChanged: (page) => setState(() => _currentPage = page),
        children: [
          _buildStep(
            formKey: _formKey1,
            title: 'Langkah 1: Akun Login',
            subtitle:
                'Informasi ini akan Anda gunakan untuk masuk ke aplikasi.',
            fields: [
              _buildSectionTitle('Nama Lengkap Anda / PIC'),
              TextFormField(
                controller: _nameController,
                decoration:
                    const InputDecoration(hintText: 'Cth: Budi Santoso'),
                validator: (val) => AppValidators.validateNotEmpty(val, 'Nama'),
                textInputAction: TextInputAction.next,
              ),
              const SizedBox(height: 24),
              _buildSectionTitle('Email'),
              TextFormField(
                controller: _emailController,
                decoration: const InputDecoration(
                    hintText: 'Cth: budi.santoso@email.com'),
                validator: AppValidators.validateEmail,
                keyboardType: TextInputType.emailAddress,
                textInputAction: TextInputAction.next,
              ),
              const SizedBox(height: 24),
              _buildSectionTitle('Kata Sandi'),
              TextFormField(
                controller: _passwordController,
                obscureText: true,
                decoration:
                    const InputDecoration(hintText: 'Minimal 6 karakter'),
                validator: AppValidators.validatePassword,
                textInputAction: TextInputAction.done,
              ),
            ],
            onButtonPressed: _nextPage,
            buttonText: 'SELANJUTNYA',
          ),
          _buildStep(
            formKey: _formKey2,
            title: 'Langkah 2: Profil Usaha',
            subtitle: 'Data ini akan diverifikasi oleh tim BPS.',
            fields: [
              _buildSectionTitle('Nama Usaha'),
              TextFormField(
                controller: _namaUsahaController,
                decoration:
                    const InputDecoration(hintText: 'Cth: PT Maju Jaya'),
                validator: (val) =>
                    AppValidators.validateNotEmpty(val, 'Nama Usaha'),
              ),
              const SizedBox(height: 24),
              _buildSectionTitle('Nomor WhatsApp'),
              TextFormField(
                controller: _waController,
                decoration: const InputDecoration(hintText: 'Cth: 08123456789'),
                keyboardType: TextInputType.phone,
                validator: (val) =>
                    AppValidators.validateNotEmpty(val, 'Nomor WhatsApp'),
              ),
              const SizedBox(height: 24),
              _buildSectionTitle('Alamat Lengkap Usaha'),
              TextFormField(
                controller: _alamatController,
                decoration: const InputDecoration(
                    hintText: 'Cth: Jl. Pahlawan No. 1, Madiun'),
                maxLines: 3,
                validator: (val) =>
                    AppValidators.validateNotEmpty(val, 'Alamat Usaha'),
              ),
              const SizedBox(height: 24),
              _buildSectionTitle('Kategori Usaha'),
              DropdownButtonFormField<String>(
                initialValue: _selectedKategori,
                decoration: const InputDecoration(
                    hintText: 'Pilih kategori usaha Anda'),
                items: const [
                  DropdownMenuItem(
                      value: 'industri', child: Text('Industri / Manufaktur')),
                  DropdownMenuItem(value: 'jasa', child: Text('Jasa')),
                  DropdownMenuItem(
                      value: 'perdagangan', child: Text('Perdagangan')),
                ],
                onChanged: (value) => setState(() => _selectedKategori = value),
              ),
              const SizedBox(height: 24),
              _buildSectionTitle('Jenis Pelaporan Utama'),
              DropdownButtonFormField<String>(
                initialValue: _selectedPendapatan,
                decoration: const InputDecoration(
                    hintText: 'Pilih data yang akan dilaporkan'),
                items: const [
                  DropdownMenuItem(value: 'omzet', child: Text('Omzet')),
                  DropdownMenuItem(
                      value: 'nilai_produk', child: Text('Nilai Produk')),
                  DropdownMenuItem(
                      value: 'nilai_jasa', child: Text('Nilai Jasa')),
                ],
                onChanged: (value) =>
                    setState(() => _selectedPendapatan = value),
              ),
            ],
            onButtonPressed: _submitRegister,
            buttonText: 'SELESAIKAN PENDAFTARAN',
          ),
        ],
      ),
    );
  }

  // Helper untuk membangun setiap langkah
  Widget _buildStep({
    required GlobalKey<FormState> formKey,
    required String title,
    required String subtitle,
    required List<Widget> fields,
    required VoidCallback onButtonPressed,
    required String buttonText,
  }) {
    return GestureDetector(
      onTap: () => FocusScope.of(context).unfocus(),
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(24.0),
        child: Form(
          key: formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Text(title,
                  style: const TextStyle(
                      fontSize: 26,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.textPrimary)),
              const SizedBox(height: 8),
              Text(subtitle,
                  style: GoogleFonts.dmSans(
                      color: AppTheme.textSecondary, fontSize: 15)),
              const SizedBox(height: 40),
              ...fields,
              const SizedBox(height: 48),
              Consumer<AuthProvider>(
                builder: (context, auth, child) {
                  return ElevatedButton(
                    onPressed: auth.isLoading ? null : onButtonPressed,
                    child: auth.isLoading
                        ? const CircularProgressIndicator()
                        : Text(buttonText),
                  );
                },
              ),
            ],
          ),
        ),
      ),
    );
  }

  // Helper untuk judul setiap form
  Widget _buildSectionTitle(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8.0),
      child: Text(
        title,
        style: const TextStyle(
          fontWeight: FontWeight.w600,
          color: AppTheme.textPrimary,
          fontSize: 14,
        ),
      ),
    );
  }
}
