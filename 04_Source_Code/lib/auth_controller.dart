import 'package:flutter/foundation.dart';

import 'api_client.dart';

class AuthController extends ChangeNotifier {
  AuthController() {
    _api.onUnauthorized = _expireSession;
  }

  final ApiClient _api = ApiClient();
  Map<String, dynamic>? _user;
  bool _busy = false;
  String? _error;

  bool get isAuthenticated => _user != null;
  bool get isBusy => _busy;
  String? get error => _error;
  String get role => _user?['role'] as String? ?? '';
  String get displayName => _user?['name'] as String? ?? 'Account';
  bool get isEnrollmentStaff => role == 'enrollment_staff';
  ApiClient get api => _api;

  Future<void> login(String email, String password) async {
    _busy = true;
    _error = null;
    notifyListeners();

    try {
      final result = await _api.login(email, password);
      final token = result['access_token'];
      final user = result['user'];
      if (token is! String || user is! Map<String, dynamic>) {
        throw const ApiException(
          'The API returned an invalid sign-in response.',
        );
      }
      _api.setToken(token);
      _user = user;
    } on ApiException catch (error) {
      _error = error.message;
    } finally {
      _busy = false;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    try {
      await _api.logout();
    } on ApiException {
      // Local session is cleared even if the service is unavailable.
    } finally {
      _api.setToken(null);
      _user = null;
      _error = null;
      notifyListeners();
    }
  }

  void _expireSession() {
    if (_user == null) return;
    _api.setToken(null);
    _user = null;
    _error = 'Your session ended. Sign in again.';
    notifyListeners();
  }

  @override
  void dispose() {
    _api.close();
    super.dispose();
  }
}
