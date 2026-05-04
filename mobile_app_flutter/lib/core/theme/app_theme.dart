import 'package:flutter/material.dart';

class AppTheme {
  const AppTheme._();

  static const _cream = Color(0xFFF8F3EA);
  static const _sand = Color(0xFFE7D6BE);
  static const _forest = Color(0xFF24493B);
  static const _sage = Color(0xFF7A9D8A);
  static const _ink = Color(0xFF1D2722);
  static const _coral = Color(0xFFD97757);

  static ThemeData light() {
    final scheme = ColorScheme.fromSeed(
      seedColor: _forest,
      brightness: Brightness.light,
      primary: _forest,
      secondary: _coral,
      surface: Colors.white,
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: scheme,
      scaffoldBackgroundColor: _cream,
      textTheme: const TextTheme(
        headlineMedium: TextStyle(fontSize: 28, fontWeight: FontWeight.w700, color: _ink),
        titleLarge: TextStyle(fontSize: 20, fontWeight: FontWeight.w700, color: _ink),
        bodyLarge: TextStyle(fontSize: 16, height: 1.4, color: _ink),
        bodyMedium: TextStyle(fontSize: 14, height: 1.4, color: _ink),
      ),
      cardTheme: CardThemeData(
        color: Colors.white,
        elevation: 0,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(24),
          side: const BorderSide(color: _sand),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(18),
          borderSide: const BorderSide(color: _sand),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(18),
          borderSide: const BorderSide(color: _sand),
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: _forest,
          foregroundColor: Colors.white,
          minimumSize: const Size.fromHeight(56),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        ),
      ),
      chipTheme: ChipThemeData(
        backgroundColor: _sage.withValues(alpha: 0.12),
        side: BorderSide.none,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
      ),
    );
  }
}