class AppValidators {
  // Validasi tidak boleh kosong
  static String? validateNotEmpty(String? value, String fieldName) {
    if (value == null || value.trim().isEmpty) {
      return '$fieldName tidak boleh kosong.';
    }
    return null;
  }

  // Validasi format email
  static String? validateEmail(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Email tidak boleh kosong.';
    }
    final emailRegex = RegExp(r"^[a-zA-Z0-9.a-zA-Z0-9.!#$%&'*+-/=?^_`{|}~]+@[a-zA-Z0-9]+\.[a-zA-Z]+");
    if (!emailRegex.hasMatch(value)) {
      return 'Format email yang Anda masukkan tidak valid.';
    }
    return null;
  }

  // Validasi panjang minimal password
  static String? validatePassword(String? value, {int minLength = 6}) {
    if (value == null || value.isEmpty) {
      return 'Kata sandi tidak boleh kosong.';
    }
    if (value.length < minLength) {
      return 'Kata sandi minimal harus $minLength karakter.';
    }
    return null;
  }
}