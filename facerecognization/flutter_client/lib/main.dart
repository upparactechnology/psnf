import 'package:flutter/material';
import 'package:workmanager/workmanager.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'domain/sync_worker.dart';
import 'ui/kiosk_page.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize SharedPreferences with default host configurations if not set
  final prefs = await SharedPreferences.getInstance();
  if (!prefs.containsKey('host_url')) {
    await prefs.setString('host_url', 'http://10.0.2.2:8000'); // Standard Android Emulator host mapping
  }

  // Initialize Workmanager background execution dispatcher
  await Workmanager().initialize(
    callbackDispatcher,
    isInDebugMode: true,
  );

  runApp(const AttendanceApp());
}

class AttendanceApp extends StatelessWidget {
  const AttendanceApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'AI Face Attendance Kiosk',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF005FAF)),
        useMaterial3: true,
      ),
      home: const KioskPage(),
    );
  }
}
