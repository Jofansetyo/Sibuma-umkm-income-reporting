import 'package:flutter/material.dart';

class PasswordTextField extends StatefulWidget {
  final TextEditingController controller;
  final String labelText;
  final String? hintText;
  final String? Function(String?)? validator;

  const PasswordTextField({
    super.key,
    required this.controller,
    required this.labelText,
    this.hintText,
    this.validator,
  });

  @override
  State<PasswordTextField> createState() => _PasswordTextFieldState();
}

class _PasswordTextFieldState extends State<PasswordTextField> {
  // State lokal khusus untuk widget ini saja
  // Secara default, password disembunyikan (titik-titik)
  bool _obscureText = true;

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller: widget.controller,
      obscureText: _obscureText, // Menyembunyikan/menampilkan teks
      
      // Mengubah icon kunci menjadi biru saat diklik (fokus)
      decoration: InputDecoration(
        labelText: widget.labelText,
        hintText: widget.hintText,
        prefixIcon: const Icon(Icons.lock_outline),
        
        // Tombol mata di sebelah kanan
        suffixIcon: IconButton(
          // Mengubah icon berdasarkan status
          icon: Icon(
            _obscureText ? Icons.visibility_off : Icons.visibility,
            color: _obscureText ? Colors.grey : const Color(0xFF2563EB), // Berubah biru jika dilihat
          ),
          onPressed: () {
            // Membalikkan status saat tombol ditekan
            setState(() {
              _obscureText = !_obscureText;
            });
          },
        ),
      ),
      
      // Jika validator tidak diberikan dari luar, gunakan validator default ini
      validator: widget.validator ?? (value) {
        if (value == null || value.isEmpty) {
          return 'Kata sandi tidak boleh kosong';
        }
        if (value.length < 6) {
          return 'Sandi minimal 6 karakter';
        }
        return null;
      },
    );
  }
}
