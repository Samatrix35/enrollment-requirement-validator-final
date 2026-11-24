import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;

class ApiException implements Exception {
  const ApiException(this.message, {this.statusCode});

  final String message;
  final int? statusCode;

  @override
  String toString() => message;
}

class ApiClient {
  ApiClient({http.Client? client, this.onUnauthorized})
    : _client = client ?? http.Client();

  final http.Client _client;
  void Function()? onUnauthorized;
  String? _token;

  static const _baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://127.0.0.1:8000/api',
  );

  Uri _endpoint(String path) {
    final base = _baseUrl.replaceFirst(RegExp(r'/+$'), '');
    final uri = Uri.parse('$base/$path');
    if (!uri.hasAuthority || uri.host.isEmpty) {
      throw const ApiException('The API address is not configured correctly.');
    }
    if (kReleaseMode && uri.scheme != 'https') {
      throw const ApiException('The API must use HTTPS in a release build.');
    }
    return uri;
  }

  void setToken(String? token) => _token = token;

  Future<Map<String, dynamic>> login(String email, String password) => _send(
    'POST',
    'auth/login',
    body: {'email': email.trim(), 'password': password},
  );

  Future<Map<String, dynamic>> me() => _send('GET', 'me');

  Future<Map<String, dynamic>> myEnrollment() => _send('GET', 'my/enrollment');

  Future<Map<String, dynamic>> submitRequirement(int requirementId) =>
      _send('PATCH', 'my/requirements/$requirementId/submit');

  Future<Map<String, dynamic>> staffQueue() =>
      _send('GET', 'requirements?status=Submitted');

  Future<Map<String, dynamic>> decideRequirement(
    int requirementId,
    String status, {
    String? remarks,
  }) => _send(
    'PATCH',
    'requirements/$requirementId',
    body: {
      'status': status,
      ...?(remarks == null ? null : {'remarks': remarks}),
    },
  );

  Future<void> logout() async {
    try {
      await _send('POST', 'auth/logout');
    } finally {
      _token = null;
    }
  }

  Future<Map<String, dynamic>> _send(
    String method,
    String path, {
    Map<String, dynamic>? body,
  }) async {
    final hadToken = _token != null;
    final headers = <String, String>{'Accept': 'application/json'};
    if (body != null) headers['Content-Type'] = 'application/json';
    if (_token case final token?) headers['Authorization'] = 'Bearer $token';

    try {
      final uri = _endpoint(path);
      final request = http.Request(method, uri)..headers.addAll(headers);
      if (body != null) request.body = jsonEncode(body);
      final streamed = await _client
          .send(request)
          .timeout(const Duration(seconds: 12));
      final response = await http.Response.fromStream(streamed);
      Map<String, dynamic> decoded = {};
      if (response.body.isNotEmpty) {
        final value = jsonDecode(response.body);
        if (value is Map<String, dynamic>) decoded = value;
      }

      if (response.statusCode < 200 || response.statusCode >= 300) {
        if (response.statusCode == 401 && hadToken) {
          _token = null;
          onUnauthorized?.call();
        }
        final message = _errorMessage(decoded, response.statusCode);
        throw ApiException(message, statusCode: response.statusCode);
      }

      return decoded;
    } on ApiException {
      rethrow;
    } on FormatException {
      throw const ApiException('The API response was not valid JSON.');
    } on Object {
      // Never log request bodies, credentials, access tokens, or student data.
      throw const ApiException(
        'The enrollment service is unavailable. Check the connection and try again.',
      );
    }
  }

  String _errorMessage(Map<String, dynamic> response, int statusCode) {
    final errors = response['errors'];
    if (errors is Map && errors.values.isNotEmpty) {
      final first = errors.values.first;
      if (first is List && first.isNotEmpty && first.first is String) {
        return first.first as String;
      }
    }
    if (response['message'] is String) return response['message'] as String;
    return 'The request could not be completed (HTTP $statusCode).';
  }

  void close() => _client.close();
}
