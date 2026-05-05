import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:timezone/data/latest.dart' as tz;
import 'package:timezone/timezone.dart' as tz;

import '../utils/date_formatters.dart';

class AppointmentNotificationService {
  AppointmentNotificationService();

  static const _channelId = 'appointments_today';
  static const _channelName = 'Consultas do dia';
  static const _channelDescription = 'Lembretes de consultas do paciente';
  static const _prefsKey = 'scheduled_appointment_notification_ids';

  final FlutterLocalNotificationsPlugin _plugin = FlutterLocalNotificationsPlugin();
  bool _initialized = false;

  Future<void> initialize() async {
    if (_initialized) {
      return;
    }

    tz.initializeTimeZones();
    try {
      tz.setLocalLocation(tz.getLocation('America/Sao_Paulo'));
    } catch (_) {}

    const androidSettings = AndroidInitializationSettings('@mipmap/ic_launcher');
    const settings = InitializationSettings(android: androidSettings);
    await _plugin.initialize(settings);

    final android = _plugin.resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>();
    await android?.requestNotificationsPermission();
    await android?.createNotificationChannel(
      const AndroidNotificationChannel(
        _channelId,
        _channelName,
        description: _channelDescription,
        importance: Importance.high,
      ),
    );

    _initialized = true;
  }

  Future<void> clearScheduledAppointments() async {
    await initialize();
    final prefs = await SharedPreferences.getInstance();
    final ids = prefs.getStringList(_prefsKey) ?? <String>[];
    for (final rawId in ids) {
      final id = int.tryParse(rawId);
      if (id != null) {
        await _plugin.cancel(id);
      }
    }
    await prefs.remove(_prefsKey);
  }

  Future<void> syncTodayAppointments(List<Map<String, dynamic>> appointments) async {
    await initialize();
    await clearScheduledAppointments();

    final now = DateTime.now();
    final prefs = await SharedPreferences.getInstance();
    final scheduledIds = <String>[];

    for (final appointment in appointments) {
      final appointmentId = (appointment['id'] as num?)?.toInt() ?? int.tryParse('${appointment['id']}') ?? 0;
      final sessionDate = DateFormatters.parse(appointment['session_date']);
      if (appointmentId <= 0 || sessionDate == null || !DateFormatters.isSameDay(sessionDate, now) || !sessionDate.isAfter(now)) {
        continue;
      }

      var scheduledAt = sessionDate.subtract(const Duration(hours: 3));
      if (!scheduledAt.isAfter(now)) {
        scheduledAt = now.add(const Duration(seconds: 5));
      }

      final notificationId = 800000 + appointmentId;
      final body = 'Sua consulta está agendada para ${DateFormatters.time(sessionDate)}. Organize seu momento com antecedência.';

      await _plugin.zonedSchedule(
        notificationId,
        'Tera-Tech: consulta hoje',
        body,
        tz.TZDateTime.from(scheduledAt, tz.local),
        const NotificationDetails(
          android: AndroidNotificationDetails(
            _channelId,
            _channelName,
            channelDescription: _channelDescription,
            importance: Importance.high,
            priority: Priority.high,
          ),
        ),
        androidScheduleMode: AndroidScheduleMode.inexactAllowWhileIdle,
        uiLocalNotificationDateInterpretation: UILocalNotificationDateInterpretation.absoluteTime,
      );

      scheduledIds.add('$notificationId');
    }

    await prefs.setStringList(_prefsKey, scheduledIds);
  }
}