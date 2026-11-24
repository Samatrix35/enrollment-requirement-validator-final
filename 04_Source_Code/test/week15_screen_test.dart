import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:enrollment_requirement_validator/main.dart';
import 'package:enrollment_requirement_validator/api_client.dart';
import 'package:enrollment_requirement_validator/auth_controller.dart';

Map<String, dynamic> checklist(String status) => {
  'student': {
    'first_name': 'Jordan',
    'last_name': 'Example',
    'student_number': 'DEMO-001',
  },
  'requirements': [
    {
      'id': 1,
      'requirement_name': 'Transcript',
      'status': status,
      if (status == 'Rejected')
        'feedback': 'Please use a clearer demo reference.',
    },
  ],
};

AuthController controller(
  Future<http.Response> Function(http.Request) handler, {
  String role = 'student',
}) {
  return AuthController(
    api: ApiClient(
      client: MockClient((r) async {
        if (r.url.path.endsWith('/auth/login')) {
          return http.Response(
            jsonEncode({
              'access_token': 'fictional',
              'user': {'name': 'Jordan Example', 'role': role},
            }),
            200,
          );
        }
        return handler(r);
      }),
    ),
  );
}

Future<void> signedIn(WidgetTester tester, AuthController auth) async {
  await auth.login('demo@example.test', 'fictional');
  await tester.pumpWidget(EnrollmentApp(auth: auth));
  await tester.pumpAndSettle();
}

void main() {
  testWidgets('W01 blank login prevents network requests', (tester) async {
    var requests = 0;
    final auth = controller((r) async {
      requests++;
      return http.Response('{}', 200);
    });
    await tester.pumpWidget(EnrollmentApp(auth: auth));
    await tester.tap(find.text('Sign in'));
    await tester.pump();
    expect(find.text('Enter a valid email address.'), findsOneWidget);
    expect(find.text('Enter your password.'), findsOneWidget);
    expect(requests, 0);
  });

  testWidgets('W02 unavailable service shows retry and recovers', (
    tester,
  ) async {
    var calls = 0;
    final auth = controller(
      (r) async => ++calls == 1
          ? http.Response('{}', 503)
          : http.Response(jsonEncode(checklist('Pending')), 200),
    );
    await signedIn(tester, auth);
    expect(find.text('Try again'), findsOneWidget);
    await tester.tap(find.text('Try again'));
    await tester.pumpAndSettle();
    expect(find.text('Transcript'), findsOneWidget);
    expect(tester.takeException(), isNull);
  });

  testWidgets('W03 submitting refreshes state without approving enrollment', (
    tester,
  ) async {
    var status = 'Pending';
    var submissions = 0;
    final auth = controller((r) async {
      if (r.method == 'PATCH') {
        submissions++;
        status = 'Submitted';
        return http.Response('{}', 200);
      }
      return http.Response(jsonEncode(checklist(status)), 200);
    });
    await signedIn(tester, auth);
    await tester.tap(find.text('Submit for review'));
    await tester.pumpAndSettle();
    expect(find.text('Submitted'), findsOneWidget);
    expect(find.text('0 of 1 requirements approved'), findsOneWidget);
    expect(submissions, 1);
    expect(tester.takeException(), isNull);
  });

  testWidgets('W04 returned item shows correction and supports resubmission', (
    tester,
  ) async {
    var status = 'Rejected';
    final auth = controller((r) async {
      if (r.method == 'PATCH') {
        status = 'Submitted';
        return http.Response('{}', 200);
      }
      return http.Response(jsonEncode(checklist(status)), 200);
    });
    await signedIn(tester, auth);
    expect(
      find.textContaining('Please use a clearer demo reference.'),
      findsOneWidget,
    );
    await tester.tap(find.text('Resubmit'));
    await tester.pumpAndSettle();
    expect(find.text('Submitted'), findsOneWidget);
    expect(
      find.textContaining('Please use a clearer demo reference.'),
      findsNothing,
    );
  });

  testWidgets('W05 staff decision refreshes review queue', (tester) async {
    var approved = false;
    final auth = controller((r) async {
      if (r.method == 'PATCH') {
        approved = true;
        return http.Response('{}', 200);
      }
      return http.Response(
        jsonEncode({
          'data': approved
              ? []
              : [
                  {
                    'id': 1,
                    'requirement_name': 'Transcript',
                    'student': {
                      'first_name': 'Jordan',
                      'student_number': 'DEMO-001',
                    },
                  },
                ],
          'meta': {'last_page': 1},
        }),
        200,
      );
    }, role: 'enrollment_staff');
    await signedIn(tester, auth);
    await tester.tap(find.text('Approve'));
    await tester.pumpAndSettle();
    expect(find.text('No submissions need review.'), findsOneWidget);
    expect(tester.takeException(), isNull);
  });

  testWidgets('W06 expired session returns student to sign in', (tester) async {
    var expired = false;
    final auth = controller(
      (r) async => expired
          ? http.Response('<html>expired</html>', 401)
          : http.Response(jsonEncode(checklist('Pending')), 200),
    );
    await signedIn(tester, auth);
    expired = true;
    await tester.tap(find.text('Refresh status'));
    await tester.pumpAndSettle();
    expect(find.text('Sign in'), findsOneWidget);
    expect(auth.isAuthenticated, false);
  });
}
