import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;

import 'week15_screen_test.dart' show controller, signedIn;

void main() {
  testWidgets('AC07 empty correction explains required note without PATCH', (
    tester,
  ) async {
    var patches = 0;
    final auth = controller((request) async {
      if (request.method == 'PATCH') {
        patches++;
        return http.Response('{}', 200);
      }
      return http.Response(
        jsonEncode({
          'data': [
            {
              'id': 1,
              'requirement_name': 'Transcript',
              'status': 'Submitted',
              'student': {'first_name': 'Jordan', 'last_name': 'Example'},
            },
          ],
        }),
        200,
      );
    }, role: 'enrollment_staff');
    await signedIn(tester, auth);
    await tester.tap(find.text('Request correction'));
    await tester.pumpAndSettle();
    await tester.tap(find.text('Send note'));
    await tester.pumpAndSettle();
    expect(find.text('Enter a correction note.'), findsOneWidget);
    expect(patches, 0);
    auth.dispose();
  });
}
