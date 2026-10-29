import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:sibuma_mobile/core/theme/app_theme.dart';
import 'package:sibuma_mobile/core/utils/validators.dart';
import 'package:sibuma_mobile/features/auth/providers/auth_provider.dart';
import 'package:sibuma_mobile/features/auth/screens/lupa_sandi_screen.dart';
import 'package:sibuma_mobile/features/auth/screens/register_screen.dart';
import 'package:sibuma_mobile/features/home/screens/home_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _obscurePassword = true;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _submitLogin() async {
    if (!_formKey.currentState!.validate()) return;
    FocusScope.of(context).unfocus();
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final success = await authProvider.login(_emailController.text.trim(), _passwordController.text);
    if (!mounted) return;
    if (success) {
      Navigator.pushAndRemoveUntil(context, MaterialPageRoute(builder: (_) => const HomeScreen()), (route) => false);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(authProvider.errorMessage), backgroundColor: AppTheme.danger));
    }
  }

  @override
  Widget build(BuildContext context) {
    SystemChrome.setSystemUIOverlayStyle(SystemUiOverlayStyle.light);

    return Scaffold(
      backgroundColor: AppTheme.primary,
      body: Column(
        children: [
          // === HEADER BIRU ===
          SizedBox(
            height: MediaQuery.of(context).size.height * 0.36,
            child: Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Image.asset('assets/images/BPS.png', height: 72,
                    errorBuilder: (context, error, stackTrace) =>
                        const Icon(Icons.query_stats, color: Colors.white, size: 64)),
                  const SizedBox(height: 20),
                  Text('Selamat Datang',
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 30, fontWeight: FontWeight.w800, color: Colors.white, letterSpacing: -0.5)),
                  const SizedBox(height: 6),
                  Text('Masuk ke akun SIBUMA Anda',
                    style: GoogleFonts.dmSans(fontSize: 15, color: Colors.white70)),
                ],
              ),
            ),
          ),

          // === FORMULIR PUTIH ===
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
                  child: Form(
                    key: _formKey,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        Text('Alamat Email', style: AppTheme.label),
                        const SizedBox(height: 8),
                        TextFormField(
                          controller: _emailController,
                          keyboardType: TextInputType.emailAddress,
                          decoration: const InputDecoration(hintText: 'nama@perusahaan.com'),
                          validator: AppValidators.validateEmail,
                        ),
                        const SizedBox(height: 24),

                        // Label + Lupa Sandi dalam satu baris
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text('Kata Sandi', style: AppTheme.label),
                            GestureDetector(
                              onTap: () => Navigator.push(context,
                                  MaterialPageRoute(builder: (_) => const LupaSandiScreen())),
                              child: Text('Lupa kata sandi?',
                                  style: GoogleFonts.plusJakartaSans(
                                      fontSize: 13, fontWeight: FontWeight.w600, color: AppTheme.accent)),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        TextFormField(
                          controller: _passwordController,
                          obscureText: _obscurePassword,
                          decoration: InputDecoration(
                            hintText: 'Masukkan kata sandi',
                            suffixIcon: IconButton(
                              icon: Icon(_obscurePassword ? Icons.visibility_off_outlined : Icons.visibility_outlined),
                              onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                            ),
                          ),
                          validator: AppValidators.validatePassword,
                        ),
                        const SizedBox(height: 40),

                        Consumer<AuthProvider>(
                          builder: (context, auth, child) {
                            return ElevatedButton(
                              onPressed: auth.isLoading ? null : _submitLogin,
                              child: auth.isLoading
                                  ? const CircularProgressIndicator()
                                  : Text('MASUK', style: AppTheme.button.copyWith(color: Colors.white)),
                            );
                          },
                        ),
                        const SizedBox(height: 24),

                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text('Belum punya akun?', style: AppTheme.bodySmall),
                            TextButton(
                              onPressed: () => Navigator.push(context,
                                  MaterialPageRoute(builder: (_) => const RegisterScreen())),
                              child: Text('Daftar Sekarang',
                                  style: GoogleFonts.plusJakartaSans(
                                      fontWeight: FontWeight.w700, color: AppTheme.accent)),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
