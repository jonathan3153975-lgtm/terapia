import 'dart:math';

import 'package:package_info_plus/package_info_plus.dart';
import 'package:shared_preferences/shared_preferences.dart';

class DeviceRegistrationService {
  const DeviceRegistrationService();

  static const _deviceIdKey = 'device_registration_id';

  Future<DeviceRegistrationData> load() async {
    final prefs = await SharedPreferences.getInstance();
    var deviceIdentifier = prefs.getString(_deviceIdKey) ?? '';
    if (deviceIdentifier.trim().isEmpty) {
      deviceIdentifier = _generateIdentifier();
      await prefs.setString(_deviceIdKey, deviceIdentifier);
    }

    final packageInfo = await PackageInfo.fromPlatform();
    final buildNumber = packageInfo.buildNumber.trim();
    final version = packageInfo.version.trim();
    final appVersion = buildNumber.isEmpty ? version : '$version+$buildNumber';

    return DeviceRegistrationData(
      deviceIdentifier: deviceIdentifier,
      appName: packageInfo.appName.trim(),
      appVersion: appVersion,
    );
  }

  String _generateIdentifier() {
    final random = Random.secure();
    final bytes = List<int>.generate(16, (_) => random.nextInt(256));
    return bytes.map((value) => value.toRadixString(16).padLeft(2, '0')).join();
  }
}

class DeviceRegistrationData {
  const DeviceRegistrationData({
    required this.deviceIdentifier,
    required this.appName,
    required this.appVersion,
  });

  final String deviceIdentifier;
  final String appName;
  final String appVersion;
}