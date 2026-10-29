import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AppTheme {
  // === PALET WARNA PROFESIONAL BPS ===
  static const Color primary = Color(0xFF0D47A1);
  static const Color accent = Color(0xFF1E88E5);
  static const Color background = Color(0xFFF4F7FA);
  static const Color surface = Colors.white;
  static const Color textPrimary = Color(0xFF212121);
  static const Color textSecondary = Color(0xFF757575);
  static const Color border = Color(0xFFE0E0E0);

  // Warna Status
  static const Color success = Color(0xFF28A745);
  static const Color danger = Color(0xFFDC3545);
  static const Color warning = Color(0xFFF59E0B);

  // === FONT HELPERS ===
  // Plus Jakarta Sans — untuk judul, heading, tombol
  static TextStyle heading1 = GoogleFonts.plusJakartaSans(
    fontSize: 28, fontWeight: FontWeight.w800, color: textPrimary, letterSpacing: -0.5,
  );
  static TextStyle heading2 = GoogleFonts.plusJakartaSans(
    fontSize: 22, fontWeight: FontWeight.w700, color: textPrimary, letterSpacing: -0.3,
  );
  static TextStyle heading3 = GoogleFonts.plusJakartaSans(
    fontSize: 18, fontWeight: FontWeight.w600, color: textPrimary,
  );
  static TextStyle label = GoogleFonts.plusJakartaSans(
    fontSize: 14, fontWeight: FontWeight.w600, color: textPrimary,
  );

  // DM Sans — untuk body text, hint, sub-teks
  static TextStyle body = GoogleFonts.dmSans(
    fontSize: 15, fontWeight: FontWeight.w400, color: textPrimary,
  );
  static TextStyle bodySmall = GoogleFonts.dmSans(
    fontSize: 13, fontWeight: FontWeight.w400, color: textSecondary,
  );
  static TextStyle hint = GoogleFonts.dmSans(
    fontSize: 15, fontWeight: FontWeight.w400, color: textSecondary,
  );
  static TextStyle button = GoogleFonts.plusJakartaSans(
    fontSize: 15, fontWeight: FontWeight.w700, letterSpacing: 0.8,
  );

  // === GRADASI UTAMA APLIKASI ===
  static BoxDecoration get gradientBackground {
    return const BoxDecoration(
      gradient: LinearGradient(
        colors: [Color(0xFF0D47A1), Color(0xFF1976D2), Color(0xFF42A5F5)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      ),
    );
  }

  // === DESIGN SYSTEM UTAMA ===
  static ThemeData get theme {
    final base = ThemeData(useMaterial3: true);

    return base.copyWith(
      scaffoldBackgroundColor: background,
      primaryColor: primary,

      textTheme: GoogleFonts.plusJakartaSansTextTheme(base.textTheme).copyWith(
        displayLarge: GoogleFonts.plusJakartaSans(fontSize: 32, fontWeight: FontWeight.w800, color: textPrimary, letterSpacing: -1.0),
        displayMedium: GoogleFonts.plusJakartaSans(fontSize: 26, fontWeight: FontWeight.w700, color: textPrimary, letterSpacing: -0.5),
        headlineLarge: GoogleFonts.plusJakartaSans(fontSize: 22, fontWeight: FontWeight.w700, color: textPrimary),
        headlineMedium: GoogleFonts.plusJakartaSans(fontSize: 18, fontWeight: FontWeight.w600, color: textPrimary),
        titleLarge: GoogleFonts.plusJakartaSans(fontSize: 18, fontWeight: FontWeight.w600, color: textPrimary),
        titleMedium: GoogleFonts.plusJakartaSans(fontSize: 16, fontWeight: FontWeight.w600, color: textPrimary),
        bodyLarge: GoogleFonts.dmSans(fontSize: 16, fontWeight: FontWeight.w400, color: textPrimary),
        bodyMedium: GoogleFonts.dmSans(fontSize: 14, fontWeight: FontWeight.w400, color: textPrimary),
        bodySmall: GoogleFonts.dmSans(fontSize: 12, fontWeight: FontWeight.w400, color: textSecondary),
        labelLarge: GoogleFonts.plusJakartaSans(fontSize: 15, fontWeight: FontWeight.w700, letterSpacing: 0.5),
        labelMedium: GoogleFonts.plusJakartaSans(fontSize: 13, fontWeight: FontWeight.w600),
        labelSmall: GoogleFonts.dmSans(fontSize: 11, fontWeight: FontWeight.w500, color: textSecondary),
      ),

      appBarTheme: AppBarTheme(
        backgroundColor: background,
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: true,
        iconTheme: const IconThemeData(color: textPrimary),
        titleTextStyle: GoogleFonts.plusJakartaSans(
          color: textPrimary,
          fontSize: 18,
          fontWeight: FontWeight.w700,
          letterSpacing: -0.2,
        ),
      ),

      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: accent,
          foregroundColor: Colors.white,
          elevation: 0,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          padding: const EdgeInsets.symmetric(vertical: 16),
          textStyle: GoogleFonts.plusJakartaSans(
            fontSize: 15,
            fontWeight: FontWeight.w700,
            letterSpacing: 0.8,
          ),
        ),
      ),

      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: surface,
        hintStyle: GoogleFonts.dmSans(color: textSecondary, fontSize: 15),
        labelStyle: GoogleFonts.plusJakartaSans(color: textSecondary, fontSize: 14, fontWeight: FontWeight.w500),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: border),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: border),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: accent, width: 2.0),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: danger, width: 1.5),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: danger, width: 2.0),
        ),
        errorStyle: GoogleFonts.dmSans(color: danger, fontSize: 12),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      ),

      cardTheme: const CardThemeData(
        elevation: 0,
        color: surface,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.all(Radius.circular(14.0)),
          side: BorderSide(color: border),
        ),
      ),

      snackBarTheme: SnackBarThemeData(
        contentTextStyle: GoogleFonts.dmSans(fontSize: 14, fontWeight: FontWeight.w500),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        behavior: SnackBarBehavior.floating,
      ),

      chipTheme: ChipThemeData(
        labelStyle: GoogleFonts.plusJakartaSans(fontSize: 13, fontWeight: FontWeight.w600),
      ),

      bottomNavigationBarTheme: BottomNavigationBarThemeData(
        selectedLabelStyle: GoogleFonts.plusJakartaSans(fontSize: 12, fontWeight: FontWeight.w700),
        unselectedLabelStyle: GoogleFonts.dmSans(fontSize: 12, fontWeight: FontWeight.w400),
      ),
    );
  }
}
