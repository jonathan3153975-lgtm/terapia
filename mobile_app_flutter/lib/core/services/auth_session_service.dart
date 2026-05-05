import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';

class AuthSessionService {
  const AuthSessionService();

  static const _tokenKey = 'auth_token';
  static const _profileKey = 'auth_profile';
  static const _rememberKey = 'auth_remember';
  static const _emailKey = 'auth_email';

  Future<AuthSessionSnapshot> load() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString(_tokenKey);
    final profileRaw = prefs.getString(_profileKey);
    final remember = prefs.getBool(_rememberKey) ?? true;
    final email = prefs.getString(_emailKey) ?? '';

    Map<String, dynamic>? profile;
    if (profileRaw != null && profileRaw.trim().isNotEmpty) {
      try {
        final decoded = jsonDecode(profileRaw);
        if (decoded is Map<String, dynamic>) {
          profile = decoded;
        } else if (decoded is Map) {
          profile = Map<String, dynamic>.from(decoded);
        }
      } catch (_) {}
    }

    return AuthSessionSnapshot(
      token: token,
      profile: profile,
      rememberSession: remember,
      rememberedEmail: email,
    );
  }

  Future<void> save({
    required String token,
    required Map<String, dynamic>? profile,
    required bool rememberSession,
    required String email,
  }) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_rememberKey, rememberSession);
    await prefs.setString(_emailKey, email);

    if (!rememberSession) {
      await prefs.remove(_tokenKey);
      await prefs.remove(_profileKey);
      return;
    }

    await prefs.setString(_tokenKey, token);
    if (profile != null) {
      await prefs.setString(_profileKey, jsonEncode(profile));
    } else {
      await prefs.remove(_profileKey);
    }
  }

  Future<void> clearSession({bool keepEmail = true}) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    await prefs.remove(_profileKey);
    await prefs.setBool(_rememberKey, false);
    if (!keepEmail) {
      await prefs.remove(_emailKey);
    }
  }
}

class AuthSessionSnapshot {
  const AuthSessionSnapshot({
    required this.token,
    required this.profile,
    required this.rememberSession,
    required this.rememberedEmail,
  });

  final String? token;
  final Map<String, dynamic>? profile;
  final bool rememberSession;
  final String rememberedEmail;
}