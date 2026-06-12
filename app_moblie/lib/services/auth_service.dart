import 'package:flutter/services.dart';
import 'package:local_auth/local_auth.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AuthService {
  final LocalAuthentication _auth = LocalAuthentication();
  final FlutterSecureStorage _storage = const FlutterSecureStorage();

  Future<void> saveCredentials(String username, String password) async {
    await _storage.write(key: 'saved_username', value: username);
    await _storage.write(key: 'saved_password', value: password);
  }

  Future<String?> getSavedUsername() async => await _storage.read(key: 'saved_username');

  Future<String?> getSavedPassword() async => await _storage.read(key: 'saved_password');

  Future<void> saveAuthToken(String token) async {
    await _storage.write(key: 'auth_token', value: token);
  }

  Future<String?> getAuthToken() async => await _storage.read(key: 'auth_token');

  Future<void> setBiometricEnabled(bool isEnabled) async {
    await _storage.write(key: 'biometric_enabled', value: isEnabled ? 'true' : 'false');
  }

  Future<bool> isBiometricEnabled() async {
    String? val = await _storage.read(key: 'biometric_enabled');
    return val == 'true';
  }

  Future<void> logout() async {
    await _storage.delete(key: 'saved_password');
    await _storage.delete(key: 'auth_token');

    SharedPreferences prefs = await SharedPreferences.getInstance();
    await prefs.remove('userId');
    await prefs.remove('userRole');
  }

  Future<bool> authenticateWithBiometrics() async {
    try {
      bool canCheckBiometrics = await _auth.canCheckBiometrics;
      bool isDeviceSupported = await _auth.isDeviceSupported();

      if (!canCheckBiometrics || !isDeviceSupported) return false;

      return await _auth.authenticate(
        localizedReason: 'Xác thực để đăng nhập hệ thống EasyWMS',
      );
    } on PlatformException catch (e) {
      print('Lỗi sinh trắc học: $e');
      return false;
    }
  }
}