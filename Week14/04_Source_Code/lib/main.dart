import 'package:flutter/material.dart';

import 'api_client.dart';
import 'auth_controller.dart';

void main() => runApp(const EnrollmentApp());

class EnrollmentApp extends StatefulWidget {
  const EnrollmentApp({super.key});

  @override
  State<EnrollmentApp> createState() => _EnrollmentAppState();
}

class _EnrollmentAppState extends State<EnrollmentApp> {
  late final AuthController _auth = AuthController();

  @override
  void dispose() {
    _auth.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => MaterialApp(
    debugShowCheckedModeBanner: false,
    title: 'Enrollment Requirement Validator',
    theme: ThemeData(
      colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF11665D)),
      useMaterial3: true,
      scaffoldBackgroundColor: const Color(0xFFF3F6F5),
      inputDecorationTheme: const InputDecorationTheme(
        border: OutlineInputBorder(),
      ),
    ),
    home: AnimatedBuilder(
      animation: _auth,
      builder: (context, _) {
        if (!_auth.isAuthenticated) return LoginPage(auth: _auth);
        if (_auth.isEnrollmentStaff) return StaffPage(auth: _auth);
        return StudentPage(auth: _auth);
      },
    ),
  );
}

class LoginPage extends StatefulWidget {
  const LoginPage({required this.auth, super.key});
  final AuthController auth;

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final _formKey = GlobalKey<FormState>();
  final _email = TextEditingController();
  final _password = TextEditingController();
  bool _obscure = true;

  @override
  void dispose() {
    _email.dispose();
    _password.dispose();
    super.dispose();
  }

  Future<void> _signIn() async {
    if (!_formKey.currentState!.validate()) return;
    await widget.auth.login(_email.text, _password.text);
    _password.clear();
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    body: Center(
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 460),
        child: Card(
          margin: const EdgeInsets.all(20),
          child: Padding(
            padding: const EdgeInsets.all(28),
            child: Form(
              key: _formKey,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Icon(Icons.verified_user_outlined, size: 44),
                  const SizedBox(height: 16),
                  Text(
                    'Enrollment Requirement Validator',
                    style: Theme.of(context).textTheme.headlineSmall,
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'Sign in to view or review enrollment requirements.',
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 24),
                  TextFormField(
                    controller: _email,
                    keyboardType: TextInputType.emailAddress,
                    autofillHints: const [AutofillHints.username],
                    textInputAction: TextInputAction.next,
                    decoration: const InputDecoration(labelText: 'Email'),
                    validator: (value) {
                      final input = value?.trim() ?? '';
                      if (input.isEmpty || !input.contains('@')) {
                        return 'Enter a valid email address.';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 14),
                  TextFormField(
                    controller: _password,
                    obscureText: _obscure,
                    autofillHints: const [AutofillHints.password],
                    onFieldSubmitted: (_) => _signIn(),
                    decoration: InputDecoration(
                      labelText: 'Password',
                      suffixIcon: IconButton(
                        tooltip: _obscure ? 'Show password' : 'Hide password',
                        onPressed: () => setState(() => _obscure = !_obscure),
                        icon: Icon(
                          _obscure ? Icons.visibility : Icons.visibility_off,
                        ),
                      ),
                    ),
                    validator: (value) => value == null || value.isEmpty
                        ? 'Enter your password.'
                        : null,
                  ),
                  if (widget.auth.error != null) ...[
                    const SizedBox(height: 12),
                    Text(
                      widget.auth.error!,
                      style: TextStyle(
                        color: Theme.of(context).colorScheme.error,
                      ),
                    ),
                  ],
                  const SizedBox(height: 20),
                  FilledButton.icon(
                    onPressed: widget.auth.isBusy ? null : _signIn,
                    icon: widget.auth.isBusy
                        ? const SizedBox.square(
                            dimension: 18,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : const Icon(Icons.login),
                    label: const Text('Sign in'),
                  ),
                  const SizedBox(height: 14),
                  const Text(
                    'Project 23  •  Week 14  •  Fictional demo records only',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 12, color: Colors.black54),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    ),
  );
}

class StudentPage extends StatefulWidget {
  const StudentPage({required this.auth, super.key});
  final AuthController auth;

  @override
  State<StudentPage> createState() => _StudentPageState();
}

class _StudentPageState extends State<StudentPage> {
  late Future<Map<String, dynamic>> _enrollment;
  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    _enrollment = widget.auth.api.myEnrollment();
  }

  void _refresh() => setState(() {
    _enrollment = widget.auth.api.myEnrollment();
  });

  Future<void> _submit(int requirementId) async {
    setState(() => _submitting = true);
    try {
      await widget.auth.api.submitRequirement(requirementId);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Submitted for staff review.')),
        );
        _refresh();
      }
    } on ApiException catch (error) {
      if (mounted) _showError(error.message);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  void _showError(String message) =>
      ScaffoldMessenger.of(context)
          .showSnackBar(SnackBar(content: Text(message)));

  @override
  Widget build(BuildContext context) => _PageScaffold(
    auth: widget.auth,
    title: 'My enrollment',
    child: FutureBuilder<Map<String, dynamic>>(
      future: _enrollment,
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return const Center(child: CircularProgressIndicator());
        }
        if (snapshot.hasError) {
          final error = snapshot.error;
          return _RetryPanel(
            message: error is ApiException
                ? error.message
                : 'Enrollment could not be loaded.',
            onRetry: _refresh,
          );
        }

        final data = snapshot.data ?? {};
        final student = Map<String, dynamic>.from(
          data['student'] as Map? ?? {},
        );
        final requirements = (data['requirements'] as List? ?? [])
            .map((item) => Map<String, dynamic>.from(item as Map))
            .toList();
        final submitted = requirements
            .where((item) => item['status'] == 'Approved')
            .length;

        return ListView(
          padding: const EdgeInsets.all(20),
          children: [
            Text(
              'Enrollment checklist',
              style: Theme.of(context).textTheme.headlineMedium,
            ),
            const SizedBox(height: 8),
            const Text(
              'Submit your checklist items, follow correction notes, and track staff approval.',
            ),
            const SizedBox(height: 16),
            Card(
              child: ListTile(
                leading: const Icon(Icons.school_outlined),
                title: Text(
                  '${student['first_name'] ?? ''} ${student['last_name'] ?? ''}'
                      .trim(),
                ),
                subtitle: Text(
                  'Student number ${student['student_number'] ?? ''}',
                ),
              ),
            ),
            const SizedBox(height: 12),
            LinearProgressIndicator(
              value: requirements.isEmpty ? 0 : submitted / requirements.length,
              minHeight: 8,
            ),
            const SizedBox(height: 8),
            Text('$submitted of ${requirements.length} requirements approved'),
            const SizedBox(height: 12),
            if (requirements.isEmpty)
              const Card(
                child: ListTile(title: Text('No requirements are assigned.')),
              ),
            for (final item in requirements)
              _StudentRequirementCard(
                item: item,
                busy: _submitting,
                onSubmit: () => _submit(item['id'] as int),
              ),
            const SizedBox(height: 12),
            OutlinedButton.icon(
              onPressed: _submitting ? null : _refresh,
              icon: const Icon(Icons.refresh),
              label: const Text('Refresh status'),
            ),
          ],
        );
      },
    ),
  );
}

class _StudentRequirementCard extends StatelessWidget {
  const _StudentRequirementCard({
    required this.item,
    required this.busy,
    required this.onSubmit,
  });

  final Map<String, dynamic> item;
  final bool busy;
  final VoidCallback onSubmit;

  @override
  Widget build(BuildContext context) {
    final status = item['status'] as String? ?? 'Pending';
    final canSubmit = status == 'Pending' || status == 'Rejected';
    return Card(
      margin: const EdgeInsets.only(top: 10),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    item['requirement_name'] as String? ?? 'Requirement',
                    style: Theme.of(context).textTheme.titleMedium,
                  ),
                ),
                Chip(
                  backgroundColor: status == 'Approved'
                      ? const Color(0xFFD7EFE5)
                      : status == 'Rejected'
                      ? const Color(0xFFFFE3DD)
                      : status == 'Submitted'
                      ? const Color(0xFFDCEAF8)
                      : const Color(0xFFFFF0CE),
                  label: Text(
                    status == 'Rejected' ? 'Correction needed' : status,
                  ),
                ),
              ],
            ),
            if (item['feedback'] is String) ...[
              const SizedBox(height: 4),
              Text('Correction note: ${item['feedback']}'),
            ],
            if (canSubmit) ...[
              const SizedBox(height: 12),
              Align(
                alignment: Alignment.centerRight,
                child: FilledButton.tonal(
                  onPressed: busy ? null : onSubmit,
                  child: Text(
                    status == 'Rejected' ? 'Resubmit' : 'Submit for review',
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class StaffPage extends StatefulWidget {
  const StaffPage({required this.auth, super.key});
  final AuthController auth;

  @override
  State<StaffPage> createState() => _StaffPageState();
}

class _StaffPageState extends State<StaffPage> {
  late Future<List<Map<String, dynamic>>> _queue;

  @override
  void initState() {
    super.initState();
    _queue = _loadQueue();
  }

  Future<List<Map<String, dynamic>>> _loadQueue() async {
    final response = await widget.auth.api.staffQueue();
    final rows = response['data'];
    if (rows is! List) return [];
    return rows.map((item) => Map<String, dynamic>.from(item as Map)).toList();
  }

  void _refresh() => setState(() {
    _queue = _loadQueue();
  });

  Future<String?> _correctionNote() async {
    final controller = TextEditingController();
    final result = await showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Request a correction'),
        content: TextField(
          controller: controller,
          autofocus: true,
          maxLength: 500,
          maxLines: 3,
          decoration: const InputDecoration(
            labelText: 'Correction note',
            helperText: 'Keep the note brief and limited to what the student needs to fix.',
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () {
              final note = controller.text.trim();
              if (note.isNotEmpty) Navigator.pop(context, note);
            },
            child: const Text('Send note'),
          ),
        ],
      ),
    );
    await Future<void>.delayed(const Duration(milliseconds: 300));
    controller.dispose();
    return result;
  }

  Future<void> _decide(Map<String, dynamic> item, String status) async {
    String? remarks;
    if (status == 'Rejected') {
      remarks = await _correctionNote();
      if (remarks == null) return;
    }
    try {
      await widget.auth.api.decideRequirement(
        item['id'] as int,
        status,
        remarks: remarks,
      );
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              status == 'Approved'
                  ? 'Requirement approved.'
                  : 'Correction requested.',
            ),
          ),
        );
        _refresh();
      }
    } on ApiException catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(error.message)));
      }
    }
  }

  @override
  Widget build(BuildContext context) => _PageScaffold(
    auth: widget.auth,
    title: 'Staff review queue',
    child: FutureBuilder<List<Map<String, dynamic>>>(
      future: _queue,
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return const Center(child: CircularProgressIndicator());
        }
        if (snapshot.hasError) {
          final error = snapshot.error;
          return _RetryPanel(
            message: error is ApiException
                ? error.message
                : 'Review queue could not be loaded.',
            onRetry: _refresh,
          );
        }
        final items = snapshot.data ?? [];
        return ListView(
          padding: const EdgeInsets.all(20),
          children: [
            Text(
              'Submitted requirements',
              style: Theme.of(context).textTheme.headlineMedium,
            ),
            const SizedBox(height: 8),
            const Text(
              'Only enrollment staff can approve a submission or request a correction.',
            ),
            const SizedBox(height: 16),
            if (items.isEmpty)
              const Card(
                child: ListTile(title: Text('No submissions need review.')),
              ),
            for (final item in items)
              _StaffRequirementCard(
                item: item,
                onApprove: () => _decide(item, 'Approved'),
                onReject: () => _decide(item, 'Rejected'),
              ),
            OutlinedButton.icon(
              onPressed: _refresh,
              icon: const Icon(Icons.refresh),
              label: const Text('Refresh queue'),
            ),
          ],
        );
      },
    ),
  );
}

class _StaffRequirementCard extends StatelessWidget {
  const _StaffRequirementCard({
    required this.item,
    required this.onApprove,
    required this.onReject,
  });

  final Map<String, dynamic> item;
  final VoidCallback onApprove;
  final VoidCallback onReject;

  @override
  Widget build(BuildContext context) {
    final student = Map<String, dynamic>.from(item['student'] as Map? ?? {});
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              item['requirement_name'] as String? ?? 'Requirement',
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 4),
            Text(
              '${student['first_name'] ?? ''} ${student['last_name'] ?? ''}'
                  .trim(),
            ),
            Text('Student number ${student['student_number'] ?? ''}'),
            const SizedBox(height: 12),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                FilledButton.tonal(
                  onPressed: onApprove,
                  child: const Text('Approve'),
                ),
                OutlinedButton(
                  onPressed: onReject,
                  child: const Text('Request correction'),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _PageScaffold extends StatelessWidget {
  const _PageScaffold({
    required this.auth,
    required this.title,
    required this.child,
  });

  final AuthController auth;
  final String title;
  final Widget child;

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(
      backgroundColor: const Color(0xFF11665D),
      foregroundColor: Colors.white,
      title: Text(title),
      actions: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 10),
          child: Center(child: Text(auth.displayName)),
        ),
        IconButton(
          tooltip: 'Sign out',
          onPressed: auth.logout,
          icon: const Icon(Icons.logout),
        ),
      ],
    ),
    body: Center(
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 960),
        child: child,
      ),
    ),
  );
}

class _RetryPanel extends StatelessWidget {
  const _RetryPanel({required this.message, required this.onRetry});
  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) => Center(
    child: Padding(
      padding: const EdgeInsets.all(24),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.cloud_off_outlined, size: 40),
          const SizedBox(height: 12),
          Text(message, textAlign: TextAlign.center),
          const SizedBox(height: 12),
          OutlinedButton.icon(
            onPressed: onRetry,
            icon: const Icon(Icons.refresh),
            label: const Text('Try again'),
          ),
        ],
      ),
    ),
  );
}
