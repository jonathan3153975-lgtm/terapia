import '../network/api_client.dart';

class AnalyticsService {
  AnalyticsService({required this.apiClient});

  final ApiClient apiClient;

  Future<void> track(String eventName, {String context = '', Map<String, dynamic>? payload}) async {
    try {
      await apiClient.trackEvent(
        eventName: eventName,
        eventContext: context,
        payload: payload,
      );
    } catch (_) {}
  }
}