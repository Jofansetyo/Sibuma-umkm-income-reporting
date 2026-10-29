import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:sibuma_mobile/core/theme/app_theme.dart';
import 'package:sibuma_mobile/core/utils/validators.dart';
import 'package:sibuma_mobile/features/auth/providers/auth_provider.dart';

class LupaSandiScreen extends StatefulWidget {
  const LupaSandiScreen({super.key});

  @override
  State<LupaSandiScreen> createState() => _LupaSandiScreenState();
}

class _LupaSandiScreenState extends State<LupaSandiScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  bool _emailTerkirim = false;

  @override
  void dispose() {
    _emailController.dispose();
    super.dispose();
  }

  void _kirimEmail() async {
    if (!_formKey.currentState!.validate()) return;
    FocusScope.of(context).unfocus();

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final success = await authProvider.lupaSandi(email: _emailController.text.trim());

    if (!mounted) return;

    if (success) {
      setState(() => _emailTerkirim = true);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(authProvider.errorMessage), backgroundColor: AppTheme.danger),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    SystemChrome.setSystemUIOverlayStyle(SystemUiOverlayStyle.light);

    return Scaffold(
      backgroundColor: AppTheme.primary,
      body: Column(
        children: [
          // === HEADER ===
          SizedBox(
            height: MediaQuery.of(context).size.height * 0.30,
            child: SafeArea(
              child: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.15),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.lock_reset_rounded, size: 48, color: Colors.white),
                    ),
                    const SizedBox(height: 16),
                    Text('Lupa Kata Sandi?',
                        style: GoogleFonts.plusJakartaSans(
                            fontSize: 26, fontWeight: FontWeight.w800, color: Colors.white, letterSpacing: -0.5)),
                    const SizedBox(height: 6),
                    Text('Kami akan kirimkan link reset ke email Anda',
                        style: GoogleFonts.dmSans(fontSize: 13, color: Colors.white70)),
                  ],
                ),
              ),
            ),
          ),

          // === FORM ===
          Expanded(
            child: Container(
              decoration: const BoxDecoration(
                color: AppTheme.background,
                borderRadius: BorderRadius.vertical(top: Radius.circular(32.0)),
              ),
              child: GestureDetector(
                onTap: () => FocusScope.of(context).unfocus(),
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(32.0),
                  child: _emailTerkirim ? _buildSukses() : _buildForm(),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildForm() {
    return Form(
      key: _formKey,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text('Alamat Email', style: GoogleFonts.plusJakartaSans(fontSize: 14, fontWeight: FontWeight.w600, color: AppTheme.textPrimary)),
          const SizedBox(height: 8),
          TextFormField(
            controller: _emailController,
            keyboardType: TextInputType.emailAddress,
            decoration: const InputDecoration(
              hintText: 'nama@perusahaan.com',
              prefixIcon: Icon(Icons.email_outlined),
            ),
            validator: AppValidators.validateEmail,
          ),
          const SizedBox(height: 32),

          Consumer<AuthProvider>(
            builder: (context, auth, child) {
              return ElevatedButton(
                onPressed: auth.isLoading ? null : _kirimEmail,
                child: auth.isLoading
                    ? const CircularProgressIndicator(color: Colors.white, strokeWidth: 2)
                    : Text('KIRIM LINK RESET',
                        style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.w700, letterSpacing: 0.8, color: Colors.white)),
              );
            },
          ),
          const SizedBox(height: 24),

          Center(
            child: TextButton.icon(
              onPressed: () => Navigator.pop(context),
              icon: const Icon(Icons.arrow_back_ios_new, size: 14, color: AppTheme.accent),
              label: Text('Kembali ke Login',
                  style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.w600, color: AppTheme.accent)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSukses() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const SizedBox(height: 16),
        Center(
          child: Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: AppTheme.success.withValues(alpha: 0.1),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.mark_email_read_rounded, size: 56, color: AppTheme.success),
          ),
        ),
        const SizedBox(height: 24),
        Text('Email Terkirim!',
            textAlign: TextAlign.center,
            style: GoogleFonts.plusJakartaSans(fontSize: 22, fontWeight: FontWeight.w800, color: AppTheme.textPrimary)),
        const SizedBox(height: 12),
        Text(
          'Link reset kata sandi telah dikirim ke\n${_emailController.text}',
          textAlign: TextAlign.center,
          style: GoogleFonts.dmSans(fontSize: 14, color: AppTheme.textSecondary, height: 1.5),
        ),
        const SizedBox(height: 12),
        Text(
          'Periksa folder inbox atau spam Anda.',
          textAlign: TextAlign.center,
          style: GoogleFonts.dmSans(fontSize: 13, color: AppTheme.textSecondary),
        ),
        const SizedBox(height: 40),
        ElevatedButton(
          onPressed: () => Navigator.pop(context),
          child: Text('KEMBALI KE LOGIN',
              style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.w700, letterSpacing: 0.8, color: Colors.white)),
        ),
      ],
    );
  }
}
