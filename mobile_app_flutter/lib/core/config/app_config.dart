import 'package:flutter/foundation.dart';

class AppConfig {
  const AppConfig._();

  static const String _explicitBaseUrl = String.fromEnvironment('API_BASE_URL', defaultValue: '');
  static const String _explicitMediaBaseUrl = String.fromEnvironment('MEDIA_BASE_URL', defaultValue: '');
  static const String _debugBaseUrl = String.fromEnvironment('API_BASE_URL_DEBUG', defaultValue: 'http://10.0.2.2:8000');
  static const String _releaseBaseUrl = String.fromEnvironment('API_BASE_URL_RELEASE', defaultValue: 'https://jw-adminix.com.br/terapia');

  static String get baseUrl {
    final configuredBaseUrl = _sanitize(_explicitBaseUrl);
    if (configuredBaseUrl.isNotEmpty) {
      return configuredBaseUrl;
    }

    return kReleaseMode ? _sanitize(_releaseBaseUrl) : _sanitize(_debugBaseUrl);
  }

  static bool get isReleaseBuild => kReleaseMode;

  static bool get usesLocalApi => baseUrl.contains('10.0.2.2') || baseUrl.contains('localhost') || baseUrl.contains('127.0.0.1');

  static String get mediaBaseUrl {
    final configuredMediaBaseUrl = _sanitize(_explicitMediaBaseUrl);
    if (configuredMediaBaseUrl.isNotEmpty) {
      return configuredMediaBaseUrl;
    }

    return usesLocalApi ? _sanitize(_releaseBaseUrl) : baseUrl;
  }

  static const String devLoginEmail = String.fromEnvironment('DEV_LOGIN_EMAIL', defaultValue: '');
  static const String devLoginPassword = String.fromEnvironment('DEV_LOGIN_PASSWORD', defaultValue: '');

  static bool get hasDevLogin => !kReleaseMode && devLoginEmail.isNotEmpty && devLoginPassword.isNotEmpty;

  static String _sanitize(String value) => value.trim().replaceFirst(RegExp(r'/+$'), '');
}