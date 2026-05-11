import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';

import 'core/config/app_config.dart';
import 'core/network/api_client.dart';
import 'core/services/analytics_service.dart';
import 'core/services/appointment_notification_service.dart';
import 'core/services/auth_session_service.dart';
import 'core/services/device_registration_service.dart';
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
  late final AppointmentNotificationService _appointmentNotificationService;
  late final AuthSessionService _authSessionService;
  late final DeviceRegistrationService _deviceRegistrationService;
  late final PushTokenService _pushTokenService;

  String? _token;
  Map<String, dynamic>? _profile;
  Map<String, dynamic>? _authConfig;
  String? _bootstrapWarning;
  String _rememberedEmail = '';
  bool _rememberSession = true;
  bool _bootstrapping = true;

  @override
  void initState() {
    super.initState();
    _apiClient = ApiClient(baseUrl: AppConfig.baseUrl);
    _analyticsService = AnalyticsService(apiClient: _apiClient);
    _appointmentNotificationService = AppointmentNotificationService();
    _authSessionService = const AuthSessionService();
    _deviceRegistrationService = const DeviceRegistrationService();
    _pushTokenService = const PushTokenService();
    _bootstrap();
  }

  Future<void> _bootstrap() async {
    try {
      final configResponse = await _apiClient.fetchAuthConfig();
      _authConfig = configResponse['data'] as Map<String, dynamic>?;
      _bootstrapWarning = null;
    } catch (_) {
      _bootstrapWarning = AppConfig.isReleaseBuild
          ? 'Não foi possível conectar ao servidor do aplicativo. Tente novamente mais tarde ou contate o suporte da clínica.'
          : 'Não foi possível carregar a configuração inicial da API em ${AppConfig.baseUrl}/api.php.';
    }

    final snapshot = await _authSessionService.load();
    _rememberedEmail = snapshot.rememberedEmail;
    _rememberSession = snapshot.rememberSession;

    if (snapshot.token != null && snapshot.token!.isNotEmpty) {
      _apiClient.setBearerToken(snapshot.token);
      try {
        final response = await _apiClient.fetchMe();
        final data = response['data'] as Map<String, dynamic>? ?? <String, dynamic>{};
        _token = snapshot.token;
        _profile = data['profile'] as Map<String, dynamic>? ?? snapshot.profile;
      } catch (_) {
        _apiClient.setBearerToken(null);
        await _authSessionService.clearSession();
      }
    }

    if (mounted) {
      setState(() {
        _bootstrapping = false;
      });
    }
  }

  Future<void> _handleLogin(String email, String password, bool rememberSession) async {
    final platform = defaultTargetPlatform;

    final result = await _apiClient.login(email: email, password: password);
    final data = result['data'] as Map<String, dynamic>? ?? <String, dynamic>{};
    final accessToken = data['access_token'] as String?;
    if (accessToken == null || accessToken.isEmpty) {
      throw Exception('Token de acesso ausente na resposta.');
    }

    _apiClient.setBearerToken(accessToken);
    _token = accessToken;
    _profile = data['profile'] as Map<String, dynamic>?;
    _rememberedEmail = email;
    _rememberSession = rememberSession;

    await _authSessionService.save(
      token: accessToken,
      profile: _profile,
      rememberSession: rememberSession,
      email: email,
    );

    await _analyticsService.track('login_success', context: 'auth');

    final pushToken = await _pushTokenService.getPushToken();
    try {
      final registrationData = await _deviceRegistrationService.load();
      await _apiClient.registerDevice(
        platform: platform == TargetPlatform.iOS ? 'ios' : 'android',
        deviceIdentifier: registrationData.deviceIdentifier,
        deviceName: registrationData.appName.isNotEmpty ? registrationData.appName : 'Tera-Tech',
        pushToken: pushToken,
        appVersion: registrationData.appVersion.isNotEmpty ? registrationData.appVersion : null,
        locale: 'pt-BR',
      );
    } catch (_) {}

    if (mounted) {
      setState(() {});
    }
  }

  Future<void> _handleLogout() async {
    try {
      await _apiClient.logout();
    } catch (_) {}

    await _appointmentNotificationService.clearScheduledAppointments();
    _apiClient.setBearerToken(null);
    _token = null;
    _profile = null;
    await _authSessionService.clearSession();

    if (mounted) {
      setState(() {});
    }
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Tera-Tech',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.light(),
      home: _bootstrapping
          ? const Scaffold(body: Center(child: CircularProgressIndicator()))
          : _token == null
          ? LoginPage(
              onLogin: _handleLogin,
              baseUrl: AppConfig.baseUrl,
              authConfig: _authConfig,
              bootstrapWarning: _bootstrapWarning,
              initialEmail: _rememberedEmail,
              initialRememberSession: _rememberSession,
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