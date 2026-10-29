import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/date_symbol_data_local.dart';

// --- IMPORT TEMA & PROVIDER ---
import 'package:sibuma_mobile/core/theme/app_theme.dart';
import 'package:sibuma_mobile/features/auth/providers/auth_provider.dart';
import 'package:sibuma_mobile/features/home/providers/home_provider.dart';
import 'package:sibuma_mobile/features/pelaporan/providers/pelaporan_provider.dart';
import 'package:sibuma_mobile/features/riwayat/providers/riwayat_provider.dart';
import 'package:sibuma_mobile/features/splash/screens/splash_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  // Inisialisasi locale Indonesia agar DateFormat dengan 'id_ID' bekerja
  await initializeDateFormatting('id_ID', null);

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => HomeProvider()),
        ChangeNotifierProvider(create: (_) => PelaporanProvider()),
        ChangeNotifierProvider(create: (_) => RiwayatProvider()),
      ],
      child: const SibumaApp(),
    ),
  );
}

class SibumaApp extends StatelessWidget {
  const SibumaApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'SIBUMA BPS',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.theme,
      home: const SplashScreen(),
    );
  }
}