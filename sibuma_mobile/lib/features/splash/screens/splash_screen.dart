import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:sibuma_mobile/core/theme/app_theme.dart';
import 'package:sibuma_mobile/features/auth/providers/auth_provider.dart';
import 'package:sibuma_mobile/features/auth/screens/login_screen.dart';
import 'package:sibuma_mobile/features/home/screens/home_screen.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});
  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _checkLoginStatus());
  }

  Future<void> _checkLoginStatus() async {
    await Future.delayed(const Duration(seconds: 2));
    if (!mounted) return;

    final auth = Provider.of<AuthProvider>(context, listen: false);
    // Validasi token ke server via /me, bukan hanya cek lokal
    final bool isValid = await auth.validateSession();
    if (!mounted) return;

    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (_) => isValid ? const HomeScreen() : const LoginScreen()),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: AppTheme.gradientBackground,
        child: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Image.asset('assets/images/BPS.png', height: 90,
                  errorBuilder: (_, __, ___) => const Icon(Icons.query_stats, size: 90, color: Colors.white)),
              const SizedBox(height: 28),
              Text('SIBUMA',
                  style: GoogleFonts.plusJakartaSans(
                      fontSize: 38, fontWeight: FontWeight.w800, color: Colors.white, letterSpacing: 3)),
              const SizedBox(height: 8),
              Text('Sistem Informasi Bulanan Perusahaan',
                  style: GoogleFonts.dmSans(fontSize: 14, color: Colors.white.withValues(alpha: 0.8))),
              const SizedBox(height: 64),
              const CircularProgressIndicator(color: Colors.white),
            ],
          ),
        ),
      ),
    );
  }
}