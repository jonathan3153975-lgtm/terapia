import 'package:intl/intl.dart';

class DateFormatters {
  const DateFormatters._();

  static final DateFormat _dateFormat = DateFormat('dd/MM/yyyy', 'pt_BR');
  static final DateFormat _dateTimeFormat = DateFormat('dd/MM/yyyy HH:mm', 'pt_BR');
  static final DateFormat _timeFormat = DateFormat('HH:mm', 'pt_BR');
  static final DateFormat _monthYearFormat = DateFormat('MM/yyyy', 'pt_BR');
  static final DateFormat _fullDateFormat = DateFormat("d 'de' MMMM", 'pt_BR');

  static DateTime? parse(Object? value) {
    if (value == null) {
      return null;
    }

    if (value is DateTime) {
      return value;
    }

    final text = '$value'.trim();
    if (text.isEmpty) {
      return null;
    }

    return DateTime.tryParse(text)?.toLocal();
  }

  static String date(Object? value, {String fallback = '-'}) {
    final dateTime = parse(value);
    return dateTime == null ? fallback : _dateFormat.format(dateTime);
  }

  static String dateTime(Object? value, {String fallback = '-'}) {
    final dateTime = parse(value);
    return dateTime == null ? fallback : _dateTimeFormat.format(dateTime);
  }

  static String time(Object? value, {String fallback = '-'}) {
    final dateTime = parse(value);
    return dateTime == null ? fallback : _timeFormat.format(dateTime);
  }

  static String monthYear(Object? value, {String fallback = '-'}) {
    final dateTime = parse(value);
    return dateTime == null ? fallback : _monthYearFormat.format(dateTime);
  }

  static String fullDate(Object? value, {String fallback = '-'}) {
    final dateTime = parse(value);
    return dateTime == null ? fallback : _fullDateFormat.format(dateTime);
  }

  static bool isSameDay(DateTime left, DateTime right) {
    return left.year == right.year && left.month == right.month && left.day == right.day;
  }
}