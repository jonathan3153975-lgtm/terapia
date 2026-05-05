import 'package:flutter/material.dart';

class AppTheme {
  const AppTheme._();

  static const bgMain = Color(0xFFFFF8F1);
  static const bgPanel = Color(0xFFFFFDFA);
  static const bgSoft = Color(0xFFF9EFE4);
  static const textMain = Color(0xFF264653);
  static const textSoft = Color(0xFF6C7A80);
  static const line = Color(0xFFECDCCF);
  static const lineStrong = Color(0xFFDEC4B1);
  static const accent = Color(0xFF4D908E);
  static const secondary = Color(0xFF90BE6D);
  static const cta = Color(0xFFF9844A);
  static const affection = Color(0xFFF6BD60);
  static const info = Color(0xFF577590);
  static const success = Color(0xFF43AA8B);
  static const danger = Color(0xFFD1495B);

  static ThemeData light() {
    final scheme = ColorScheme.fromSeed(
      seedColor: accent,
      brightness: Brightness.light,
      primary: accent,
      secondary: secondary,
      error: danger,
      surface: bgPanel,
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: scheme,
      scaffoldBackgroundColor: bgMain,
      textTheme: const TextTheme(
        headlineLarge: TextStyle(fontSize: 34, fontWeight: FontWeight.w800, color: textMain, height: 1.1),
        headlineMedium: TextStyle(fontSize: 28, fontWeight: FontWeight.w800, color: textMain, height: 1.1),
        titleLarge: TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: textMain),
        titleMedium: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: textMain),
        bodyLarge: TextStyle(fontSize: 16, height: 1.45, color: textMain),
        bodyMedium: TextStyle(fontSize: 14, height: 1.45, color: textMain),
        bodySmall: TextStyle(fontSize: 12, height: 1.4, color: textSoft),
      ),
      cardTheme: CardThemeData(
        color: bgPanel,
        elevation: 0,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(28),
          side: const BorderSide(color: line),
        ),
      ),
      appBarTheme: const AppBarTheme(
        backgroundColor: Colors.transparent,
        foregroundColor: textMain,
        elevation: 0,
        scrolledUnderElevation: 0,
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        labelStyle: const TextStyle(color: textSoft, fontWeight: FontWeight.w600),
        helperStyle: const TextStyle(color: textSoft),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(20),
          borderSide: const BorderSide(color: line),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(20),
          borderSide: const BorderSide(color: line),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(20),
          borderSide: const BorderSide(color: accent, width: 1.6),
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: accent,
          foregroundColor: Colors.white,
          minimumSize: const Size.fromHeight(56),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
          textStyle: const TextStyle(fontWeight: FontWeight.w700),
        ),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: accent,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
          textStyle: const TextStyle(fontWeight: FontWeight.w700),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: textMain,
          side: const BorderSide(color: lineStrong),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        ),
      ),
      chipTheme: ChipThemeData(
        backgroundColor: accent.withValues(alpha: 0.12),
        side: BorderSide.none,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        labelStyle: const TextStyle(color: textMain, fontWeight: FontWeight.w600),
      ),
      dividerColor: line,
    );
  }
}