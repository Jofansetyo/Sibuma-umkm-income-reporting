import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

// Ganti nama package jika perlu
import 'package:sibuma_mobile/main.dart'; 

void main() {
  testWidgets('Smoke Test: Aplikasi SIBUMA berhasil dibuka', (WidgetTester tester) async {
    // Membuka aplikasi
    await tester.pumpWidget(const SibumaApp()); 

    // Mengecek apakah kerangka aplikasi (MaterialApp) berhasil dimuat tanpa crash
    expect(find.byType(MaterialApp), findsOneWidget);
  });
}
