import 'dart:async';
import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:enrollment_requirement_validator/api_client.dart';

void main() {
  test('D01 malformed 401 still clears token and expires session', () async {
    var expired = 0;
    final sent = <http.Request>[];
    final api = ApiClient(
      client: MockClient((r) async {
        sent.add(r);
        return http.Response('<html>Session expired</html>', 401);
      }),
      onUnauthorized: () => expired++,
    );
    api.setToken('fictional-token');
    await expectLater(api.me(), throwsA(isA<ApiException>()));
    await expectLater(api.me(), throwsA(isA<ApiException>()));
    expect(expired, 1);
    expect(sent.last.headers.containsKey('Authorization'), false);
  });

  test('D02 server failures do not expose internal diagnostics', () async {
    final api = ApiClient(
      client: MockClient(
        (r) async => http.Response(
          jsonEncode({'message': 'SQL password=private-database-value'}),
          500,
        ),
      ),
    );
    await expectLater(
      api.me(),
      throwsA(
        isA<ApiException>().having(
          (e) => e.message,
          'safe message',
          isNot(contains('private-database-value')),
        ),
      ),
    );
  });

  test('D03 successful non-object JSON is rejected', () async {
    final api = ApiClient(
      client: MockClient((r) async => http.Response('[]', 200)),
    );
    await expectLater(api.me(), throwsA(isA<ApiException>()));
  });

  test('D04 staff queue follows all result pages', () async {
    final api = ApiClient(
      client: MockClient((r) async {
        final page = int.tryParse(r.url.queryParameters['page'] ?? '1') ?? 1;
        return http.Response(
          jsonEncode({
            'data': [
              {'id': page},
            ],
            'meta': {'current_page': page, 'last_page': 2},
          }),
          200,
        );
      }),
    );
    final response = await api.staffQueue();
    expect((response['data'] as List).length, 2);
  });

  test('D05 malformed JSON response yields safe failure', () async {
    final api = ApiClient(
      client: MockClient((r) async => http.Response('{', 200)),
    );
    await expectLater(api.me(), throwsA(isA<ApiException>()));
  });

  test('D06 manually retrying after service failure can recover', () async {
    var count = 0;
    final api = ApiClient(
      client: MockClient(
        (r) async => ++count == 1
            ? http.Response('{}', 503)
            : http.Response('{"role":"student"}', 200),
      ),
    );
    await expectLater(api.me(), throwsA(isA<ApiException>()));
    expect((await api.me())['role'], 'student');
    expect(count, 2);
  });

  test('D07 failed logout still removes bearer token', () async {
    var count = 0;
    String? auth;
    final api = ApiClient(
      client: MockClient((r) async {
        auth = r.headers['Authorization'];
        return ++count == 1
            ? http.Response('{}', 503)
            : http.Response('{}', 200);
      }),
    );
    api.setToken('fictional-token');
    await expectLater(api.logout(), throwsA(isA<ApiException>()));
    await api.me();
    expect(auth, isNull);
  });

  test('D08 field validation message is preserved', () async {
    final api = ApiClient(
      client: MockClient(
        (r) async => http.Response(
          '{"errors":{"remarks":["A correction note is required."]}}',
          422,
        ),
      ),
    );
    await expectLater(
      api.me(),
      throwsA(
        isA<ApiException>().having(
          (e) => e.message,
          'message',
          'A correction note is required.',
        ),
      ),
    );
  });

  test('D09 login trims email and sends JSON without a token', () async {
    final api = ApiClient(
      client: MockClient((r) async {
        expect(jsonDecode(r.body)['email'], 'student@example.test');
        expect(r.headers['Content-Type'], contains('application/json'));
        expect(r.headers['Authorization'], isNull);
        return http.Response('{}', 200);
      }),
    );
    await api.login(' student@example.test ', 'fictional');
  });

  test('D10 transport error does not echo credentials', () async {
    final api = ApiClient(
      client: MockClient(
        (r) async => throw http.ClientException('password=do-not-display'),
      ),
    );
    await expectLater(
      api.me(),
      throwsA(
        isA<ApiException>().having(
          (e) => e.message,
          'message',
          isNot(contains('do-not-display')),
        ),
      ),
    );
  });

  test('D11 timeout includes stalled response body', () async {
    final body = StreamController<List<int>>();
    final api = ApiClient(
      client: _StreamClient(body.stream),
      requestTimeout: const Duration(milliseconds: 20),
    );
    await expectLater(api.me(), throwsA(isA<ApiException>()));
    await body.close();
  });

  test(
    'D12 invalid pagination is reported instead of hiding records',
    () async {
      final api = ApiClient(
        client: MockClient(
          (r) async =>
              http.Response('{"data":[],"meta":{"last_page":"bad"}}', 200),
        ),
      );
      await expectLater(api.staffQueue(), throwsA(isA<ApiException>()));
    },
  );
}

class _StreamClient extends http.BaseClient {
  _StreamClient(this.stream);
  final Stream<List<int>> stream;
  @override
  Future<http.StreamedResponse> send(http.BaseRequest request) async =>
      http.StreamedResponse(stream, 200);
}
