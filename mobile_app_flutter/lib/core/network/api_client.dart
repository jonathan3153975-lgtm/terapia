import 'dart:convert';

import 'package:http/http.dart' as http;

class ApiClient {
  ApiClient({required this.baseUrl});

  final String baseUrl;
  String? _bearerToken;

  void setBearerToken(String? token) {
    _bearerToken = token;
  }

  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) {
    return _request(
      action: 'patient-login',
      method: 'POST',
      body: {
        'email': email,
        'password': password,
        'device_name': 'flutter-patient-app',
      },
    );
  }

  Future<Map<String, dynamic>> logout() {
    return _request(action: 'patient-logout', method: 'POST');
  }

  Future<Map<String, dynamic>> fetchDashboard() {
    return _request(action: 'patient-dashboard');
  }

  Future<Map<String, dynamic>> registerDevice({
    required String platform,
    required String deviceIdentifier,
    required String deviceName,
    String? pushToken,
    String? appVersion,
    String? locale,
  }) {
    return _request(
      action: 'patient-device-register',
      method: 'POST',
      body: {
        'platform': platform,
        'device_identifier': deviceIdentifier,
        'device_name': deviceName,
        'push_token': pushToken,
        'app_version': appVersion,
        'locale': locale,
      },
    );
  }

  Future<Map<String, dynamic>> trackEvent({
    required String eventName,
    String eventContext = '',
    Map<String, dynamic>? payload,
    String platform = 'android',
  }) {
    return _request(
      action: 'patient-analytics-track',
      method: 'POST',
      body: {
        'event_name': eventName,
        'event_context': eventContext,
        'platform': platform,
        'payload': payload ?? <String, dynamic>{},
      },
    );
  }

  Future<Map<String, dynamic>> _request({
    required String action,
    String method = 'GET',
    Map<String, dynamic>? body,
  }) async {
    final uri = Uri.parse('$baseUrl/api.php?action=$action');
    final headers = <String, String>{
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    if (_bearerToken != null && _bearerToken!.isNotEmpty) {
      headers['Authorization'] = 'Bearer $_bearerToken';
    }

    late final http.Response response;
    if (method.toUpperCase() == 'POST') {
      response = await http.post(uri, headers: headers, body: jsonEncode(body ?? <String, dynamic>{}));
    } else {
      response = await http.get(uri, headers: headers);
    }

    final decoded = jsonDecode(response.body) as Map<String, dynamic>;
    if (response.statusCode >= 400 || decoded['success'] != true) {
      throw Exception(decoded['message'] ?? 'Falha na comunicação com a API.');
    }

    return decoded;
  }
}