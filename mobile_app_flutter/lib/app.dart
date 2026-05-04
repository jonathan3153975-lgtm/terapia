import 'package:flutter/material.dart';

import 'core/config/app_config.dart';
import 'core/network/api_client.dart';
import 'core/services/analytics_service.dart';
import 'core/services/push_token_service.dart';
import 'core/theme/app_theme.dart';
import 'features/auth/presentation/login_page.dart';
import 'features/home/presentation/home_page.dart';

class TerapiaPatientApp extends StatefulWidget {
  const TerapiaPatientApp({super.key});

  @override
  State<TerapiaPatientApp> createState() => _TerapiaPatientAppState();
}

class _TerapiaPatientAppState extends State<TerapiaPatientApp> {
  late final ApiClient _apiClient;
  late final AnalyticsService _analyticsService;
  late final PushTokenService _pushTokenService;

  String? _token;
  Map<String, dynamic>? _profile;

  @override
  void initState() {
    super.initState();
    _apiClient = ApiClient(baseUrl: AppConfig.baseUrl);
    _analyticsService = AnalyticsService(apiClient: _apiClient);
    _pushTokenService = const PushTokenService();
  }

  Future<void> _handleLogin(String email, String password) async {
    final result = await _apiClient.login(email: email, password: password);
    final data = result['data'] as Map<String, dynamic>? ?? <String, dynamic>{};
    final accessToken = data['access_token'] as String?;
    if (accessToken == null || accessToken.isEmpty) {
      throw Exception('Token de acesso ausente na resposta.');
    }

    _apiClient.setBearerToken(accessToken);
    _token = accessToken;
    _profile = data['profile'] as Map<String, dynamic>?;

    await _analyticsService.track('login_success', context: 'auth');

    final pushToken = await _pushTokenService.getPushToken();
    await _apiClient.registerDevice(
      platform: Theme.of(context).platform == TargetPlatform.iOS ? 'ios' : 'android',
      deviceIdentifier: 'manual-device-placeholder',
      deviceName: 'Flutter Patient App',
      pushToken: pushToken,
      appVersion: '0.1.0+1',
      locale: 'pt-BR',
    );

    if (mounted) {
      setState(() {});
    }
  }

  Future<void> _handleLogout() async {
    try {
      await _apiClient.logout();
    } catch (_) {}

    _apiClient.setBearerToken(null);
    _token = null;
    _profile = null;

    if (mounted) {
      setState(() {});
    }
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Terapia Paciente',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.light(),
      home: _token == null
          ? LoginPage(
              onLogin: _handleLogin,
              baseUrl: AppConfig.baseUrl,
            )
          : HomePage(
              apiClient: _apiClient,
              analyticsService: _analyticsService,
              onLogout: _handleLogout,
              profile: _profile,
            ),
    );
  }
}