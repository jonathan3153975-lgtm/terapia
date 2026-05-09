import 'dart:convert';

import 'package:http/http.dart' as http;

class ApiException implements Exception {
  const ApiException(this.message, {this.statusCode, this.code});

  final String message;
  final int? statusCode;
  final String? code;

  @override
  String toString() => 'Exception: $message';
}

class ApiClient {
  ApiClient({required this.baseUrl});

  final String baseUrl;
  String? _bearerToken;

  Map<String, String> get mediaHeaders {
    if (_bearerToken == null || _bearerToken!.isEmpty) {
      return const <String, String>{};
    }

    return <String, String>{
      'Authorization': 'Bearer $_bearerToken',
    };
  }

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

  Future<Map<String, dynamic>> fetchAuthConfig() {
    return _request(action: 'patient-auth-config');
  }

  Future<Map<String, dynamic>> logout() {
    return _request(action: 'patient-logout', method: 'POST');
  }

  Future<Map<String, dynamic>> fetchMe() {
    return _request(action: 'patient-me');
  }

  Future<Map<String, dynamic>> fetchDashboard() {
    return _request(action: 'patient-dashboard');
  }

  Future<Map<String, dynamic>> fetchTasks() {
    return _request(action: 'patient-tasks');
  }

  Future<Map<String, dynamic>> fetchTaskDetails({required int taskId}) {
    return _request(action: 'patient-task-show', queryParameters: {'id': '$taskId'});
  }

  Future<Map<String, dynamic>> respondTask({
    required int taskId,
    required String responseHtml,
  }) {
    return _request(
      action: 'patient-task-respond',
      method: 'POST',
      body: {
        'task_id': taskId,
        'response_html': responseHtml,
      },
    );
  }

  Future<Map<String, dynamic>> completeVirtualTask({
    required int taskId,
    required Map<String, List<String>> answers,
    required Map<String, String> finalReflections,
    String reflectionHtml = '',
  }) {
    return _request(
      action: 'patient-virtual-task-complete',
      method: 'POST',
      body: {
        'task_id': taskId,
        'answers': answers,
        'final_reflections': finalReflections,
        'reflection': reflectionHtml,
      },
    );
  }

  Future<Map<String, dynamic>> fetchMaterials() {
    return _request(action: 'patient-materials');
  }

  Future<Map<String, dynamic>> fetchBooks({String? search}) {
    return _request(
      action: 'patient-books',
      queryParameters: {
        if (search != null && search.trim().isNotEmpty) 'search': search.trim(),
      },
    );
  }

  Future<Map<String, dynamic>> toggleBookFavorite({required int bookId}) {
    return _request(
      action: 'patient-book-favorite-toggle',
      method: 'POST',
      body: {'book_id': bookId},
    );
  }

  Future<Map<String, dynamic>> fetchVideos({String? search}) {
    return _request(
      action: 'patient-videos',
      queryParameters: {
        if (search != null && search.trim().isNotEmpty) 'search': search.trim(),
      },
    );
  }

  Future<Map<String, dynamic>> fetchVideoDetails({required int videoId}) {
    return _request(action: 'patient-video-show', queryParameters: {'id': '$videoId'});
  }

  Future<Map<String, dynamic>> toggleVideoFavorite({required int videoId}) {
    return _request(
      action: 'patient-video-favorite-toggle',
      method: 'POST',
      body: {'video_id': videoId},
    );
  }

  Future<Map<String, dynamic>> rateVideo({
    required int videoId,
    required int rating,
  }) {
    return _request(
      action: 'patient-video-rate',
      method: 'POST',
      body: {
        'video_id': videoId,
        'rating': rating,
      },
    );
  }

  Future<Map<String, dynamic>> commentVideo({
    required int videoId,
    required String comment,
  }) {
    return _request(
      action: 'patient-video-comment',
      method: 'POST',
      body: {
        'video_id': videoId,
        'comment': comment,
      },
    );
  }

  Future<Map<String, dynamic>> fetchMessengerEntries() {
    return _request(action: 'patient-messenger-entries');
  }

  Future<Map<String, dynamic>> drawMessenger() {
    return _request(action: 'patient-messenger-draw');
  }

  Future<Map<String, dynamic>> saveMessenger({
    required int? messageId,
    required String messageCategory,
    required String messageText,
    required String patientNote,
    required bool shareWithTherapist,
  }) {
    return _request(
      action: 'patient-messenger-save',
      method: 'POST',
      body: {
        'message_id': messageId,
        'message_category': messageCategory,
        'message_text': messageText,
        'patient_note': patientNote,
        'share_with_therapist': shareWithTherapist,
      },
    );
  }

  Future<Map<String, dynamic>> fetchFatherWordEntries() {
    return _request(action: 'patient-father-word-entries');
  }

  Future<Map<String, dynamic>> drawFatherWord() {
    return _request(action: 'patient-father-word-draw');
  }

  Future<Map<String, dynamic>> saveFatherWordEntry({
    required int? wordId,
    required String wordReference,
    required String wordText,
    required String patientNote,
    required bool shareWithTherapist,
  }) {
    return _request(
      action: 'patient-father-word-save',
      method: 'POST',
      body: {
        'word_id': wordId,
        'word_reference': wordReference,
        'word_text': wordText,
        'patient_note': patientNote,
        'share_with_therapist': shareWithTherapist,
      },
    );
  }

  Future<Map<String, dynamic>> fetchGuidedMeditations() {
    return _request(action: 'patient-guided-meditations');
  }

  Future<Map<String, dynamic>> fetchGuidedMeditationDetails({required int meditationId}) {
    return _request(action: 'patient-guided-meditation-show', queryParameters: {'id': '$meditationId'});
  }

  Future<Map<String, dynamic>> drawGuidedMeditationLetter({required int meditationId}) {
    return _request(
      action: 'patient-guided-meditation-draw-letter',
      queryParameters: {'meditation_id': '$meditationId'},
    );
  }

  Future<Map<String, dynamic>> saveGuidedMeditationEntry({
    required int meditationId,
    required int? letterId,
    required String letterCategory,
    required String letterText,
    required String patientNote,
    required bool shareWithTherapist,
  }) {
    return _request(
      action: 'patient-guided-meditation-save',
      method: 'POST',
      body: {
        'meditation_id': meditationId,
        'letter_id': letterId,
        'letter_category': letterCategory,
        'letter_text': letterText,
        'patient_note': patientNote,
        'share_with_therapist': shareWithTherapist,
      },
    );
  }

  Future<Map<String, dynamic>> fetchPrayers() {
    return _request(action: 'patient-prayers');
  }

  Future<Map<String, dynamic>> fetchPrayerDetails({required int prayerId}) {
    return _request(action: 'patient-prayer-show', queryParameters: {'id': '$prayerId'});
  }

  Future<Map<String, dynamic>> savePrayerEntry({
    required int prayerId,
    required String patientNote,
    required bool shareWithTherapist,
  }) {
    return _request(
      action: 'patient-prayer-save',
      method: 'POST',
      body: {
        'prayer_id': prayerId,
        'patient_note': patientNote,
        'share_with_therapist': shareWithTherapist,
      },
    );
  }

  Future<Map<String, dynamic>> fetchGratitude() {
    return _request(action: 'patient-gratitude');
  }

  Future<Map<String, dynamic>> saveGratitudeEntry({required String contentHtml}) {
    return _request(
      action: 'patient-gratitude-store',
      method: 'POST',
      body: {'content_html': contentHtml},
    );
  }

  Future<Map<String, dynamic>> fetchDevotionals() {
    return _request(action: 'patient-devotionals');
  }

  Future<Map<String, dynamic>> fetchDevotionalToday() {
    return _request(action: 'patient-devotional-today');
  }

  Future<Map<String, dynamic>> saveDevotionalReflection({
    required int devotionalEntryId,
    required String reflectionHtml,
  }) {
    return _request(
      action: 'patient-devotional-save',
      method: 'POST',
      body: {
        'devotional_entry_id': devotionalEntryId,
        'reflection_html': reflectionHtml,
      },
    );
  }

  Future<Map<String, dynamic>> fetchMyAccount() {
    return _request(action: 'patient-my-account');
  }

  Future<Map<String, dynamic>> updateMyAccountProfile({
    required String name,
    required String email,
    required String phone,
  }) {
    return _request(
      action: 'patient-my-account-update',
      method: 'POST',
      body: {
        'section': 'profile',
        'name': name,
        'email': email,
        'phone': phone,
      },
    );
  }

  Future<Map<String, dynamic>> updateMyPassword({
    required String newPassword,
    required String confirmPassword,
  }) {
    return _request(
      action: 'patient-my-account-update',
      method: 'POST',
      body: {
        'section': 'password',
        'new_password': newPassword,
        'confirm_password': confirmPassword,
      },
    );
  }

  Future<Map<String, dynamic>> fetchSubscriptionPlans() {
    return _request(action: 'patient-subscription-plans');
  }

  Future<Map<String, dynamic>> startSubscriptionCheckout({required int planId}) {
    return _request(
      action: 'patient-subscription-checkout',
      method: 'POST',
      body: {
        'plan_id': planId,
      },
    );
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
    Map<String, String>? queryParameters,
  }) async {
    final uri = Uri.parse('$baseUrl/api.php').replace(
      queryParameters: <String, String>{
        'action': action,
        ...?queryParameters,
      },
    );
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
      throw ApiException(
        '${decoded['message'] ?? 'Falha na comunicação com a API.'}',
        statusCode: response.statusCode,
        code: decoded['code'] as String?,
      );
    }

    return decoded;
  }
}