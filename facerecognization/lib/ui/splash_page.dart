import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'login_page.dart';
import 'kiosk_page.dart';

class SplashPage extends StatefulWidget {
  const SplashPage({super.key});

  @override
  State<SplashPage> createState() => _SplashPageState();
}

class _SplashPageState extends State<SplashPage> {
  @override
  void initState() {
    super.initState();
    _checkAuthentication();
  }

  Future<void> _checkAuthentication() async {
    // Add brief artificial delay for splash logo branding presentation
    await Future.delayed(const Duration(seconds: 2));

    final prefs = await SharedPreferences.getInstance();
    final hasToken = prefs.containsKey('access_token');
    final hasHost = prefs.containsKey('host_url');

    if (!mounted) return;

    if (hasToken && hasHost) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (context) => const KioskPage()),
      );
    } else {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (context) => const LoginPage()),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      backgroundColor: Color(0xFF0F172A),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.camera_front,
              size: 80,
              color: Color(0xFF64FFDA),
            ),
            SizedBox(height: 24),
            Text(
              "AI Attendance Kiosk",
              style: TextStyle(
                color: Colors.white,
                fontSize: 26,
                fontWeight: FontWeight.bold,
                letterSpacing: 0.8,
              ),
            ),
            SizedBox(height: 12),
            SizedBox(
              width: 150,
              child: LinearProgressIndicator(
                color: Color(0xFF64FFDA),
                backgroundColor: Colors.white12,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
