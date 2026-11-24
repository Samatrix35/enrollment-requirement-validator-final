import 'package:flutter_test/flutter_test.dart';
import 'package:enrollment_requirement_validator/main.dart';

void main() {
  testWidgets('shows sign-in before displaying enrollment data', (
    WidgetTester tester,
  ) async {
    await tester.pumpWidget(const EnrollmentApp());

    expect(find.text('Enrollment Requirement Validator'), findsOneWidget);
    expect(find.text('Sign in'), findsOneWidget);
    expect(find.text('Email'), findsOneWidget);
    expect(find.text('Password'), findsOneWidget);
    expect(find.text('Student Enrollment Portal'), findsNothing);
  });
}
