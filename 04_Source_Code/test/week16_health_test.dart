import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:enrollment_requirement_validator/main.dart';
import 'package:enrollment_requirement_validator/auth_controller.dart';
import 'package:enrollment_requirement_validator/api_client.dart';

AuthController fixture(int healthCode) => AuthController(
  api: ApiClient(
    client: MockClient((request) async {
      if (request.url.path.endsWith('/auth/login')) {
        return http.Response(
          jsonEncode({
            'access_token': 'fictional',
            'user': {'name': 'Staff Demo', 'role': 'enrollment_staff'},
          }),
          200,
        );
      }
      if (request.url.path.endsWith('/health/ready')) {
        return http.Response(
          jsonEncode({'status': 'ready', 'release': 'w16-r1'}),
          healthCode,
        );
      }
      return http.Response('{"data":[],"meta":{"last_page":1}}', 200);
    }),
  ),
);

void main() {
  testWidgets('W16 health panel shows real readiness payload', (tester) async {
    final auth = fixture(200);
    await auth.login('demo@example.test', 'fictional');
    await tester.pumpWidget(EnrollmentApp(auth: auth));
    await tester.pumpAndSettle();
    await tester.tap(find.byTooltip('Check service health'));
    await tester.pumpAndSettle();
    expect(find.textContaining('Status: ready'), findsOneWidget);
    expect(find.textContaining('Release: w16-r1'), findsOneWidget);
  });
  testWidgets('W16 failed readiness shows a safe operator message', (
    tester,
  ) async {
    final auth = fixture(503);
    await auth.login('demo@example.test', 'fictional');
    await tester.pumpWidget(EnrollmentApp(auth: auth));
    await tester.pumpAndSettle();
    await tester.tap(find.byTooltip('Check service health'));
    await tester.pumpAndSettle();
    expect(find.textContaining('The service is not ready.'), findsOneWidget);
  });
}
