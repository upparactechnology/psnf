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
    await Future.delayed(const Duration(seconds: 2));

    if (!mounted) return;

    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (context) => const KioskPage()),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 80,
              height: 80,
              decoration: BoxDecoration(
                color: const Color(0xFF111111),
                borderRadius: BorderRadius.circular(4),
              ),
              child: const Icon(
                Icons.face_retouching_natural,
                size: 44,
                color: Colors.white,
              ),
            ),
            const SizedBox(height: 32),
            const Text(
              "PSNF",
              style: TextStyle(
                color: Color(0xFF111111),
                fontSize: 32,
                fontWeight: FontWeight.w800,
                letterSpacing: 4,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              "ATTENDANCE SYSTEM",
              style: TextStyle(
                color: Color(0xFF999999),
                fontSize: 12,
                fontWeight: FontWeight.w500,
                letterSpacing: 3,
              ),
            ),
            const SizedBox(height: 40),
            const SizedBox(
              width: 120,
              child: LinearProgressIndicator(
                color: Color(0xFF111111),
                backgroundColor: Color(0xFFEEEEEE),
                minHeight: 2,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
