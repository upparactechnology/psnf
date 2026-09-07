import 'dart:async';
import 'dart:math';
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import '../theme/colors.dart';

class SpeedMonitorScreen extends StatefulWidget {
  const SpeedMonitorScreen({super.key});

  @override
  State<SpeedMonitorScreen> createState() => _SpeedMonitorScreenState();
}

class _SpeedMonitorScreenState extends State<SpeedMonitorScreen> {
  int _currentSpeed = 0;
  StreamSubscription<Position>? _positionStream;

  @override
  void initState() {
    super.initState();
    _startLocationTracking();
  }

  Future<void> _startLocationTracking() async {
    bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) return;

    LocationPermission permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) return;
    }
    if (permission == LocationPermission.deniedForever) return;

    const LocationSettings locationSettings = LocationSettings(
      accuracy: LocationAccuracy.high,
      distanceFilter: 2,
      timeInterval: 1000,
    );

    _positionStream = Geolocator.getPositionStream(locationSettings: locationSettings).listen((Position position) {
      if (mounted) {
        setState(() {
          _currentSpeed = (position.speed * 3.6).round();
        });
      }
    });
  }

  @override
  void dispose() {
    _positionStream?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    bool isOverSpeed = _currentSpeed > 60;
    final bool isDark = Theme.of(context).brightness == Brightness.dark;
    final Color textColor = isDark ? Colors.white : const Color(0xFF0F172A);
    final Color subTextColor = isDark ? AppColors.slate.shade400 : const Color(0xFF64748B);

    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text(
              'Speed Monitor',
              style: TextStyle(
                color: textColor,
                fontSize: 24,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Real-time vehicle telemetry',
              style: TextStyle(
                color: subTextColor,
                fontSize: 14,
              ),
            ),
            const Spacer(),

            // Speedometer gauge widget
            Center(
              child: SizedBox(
                width: 240,
                height: 240,
                child: CustomPaint(
                  painter: SpeedometerPainter(speed: _currentSpeed, isDark: isDark),
                  child: Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const SizedBox(height: 20),
                        Text(
                          '$_currentSpeed',
                          style: TextStyle(
                            color: isOverSpeed ? const Color(0xFFEF4444) : textColor,
                            fontSize: 64,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        Text(
                          'km/h',
                          style: TextStyle(
                            color: isDark ? AppColors.slate.shade450 : const Color(0xFF475569),
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 30),

            // Speed limit warning card
            AnimatedOpacity(
              opacity: isOverSpeed ? 1.0 : 0.2,
              duration: const Duration(milliseconds: 300),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                decoration: BoxDecoration(
                  color: const Color(0xFFEF4444).withOpacity(isDark ? 0.1 : 0.08),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFEF4444).withOpacity(0.3)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.warning_rounded, color: Color(0xFFEF4444)),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        'Warning: Speed limit exceeds 60 km/h',
                        style: TextStyle(
                          color: isDark ? const Color(0xFFFCA5A5) : const Color(0xFF991B1B),
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),

            const Spacer(),

            // Metrics cards row
            Row(
              children: [
                Expanded(
                  child: _limitBox(context, 'Speed Limit', '60 km/h', const Color(0xFF6366F1)),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: _limitBox(context, 'Max Allowed', '70 km/h', const Color(0xFFEF4444)),
                ),
              ],
            ),
            const SizedBox(height: 10),
          ],
        ),
      ),
    );
  }

  Widget _limitBox(BuildContext context, String label, String value, Color color) {
    final bool isDark = Theme.of(context).brightness == Brightness.dark;
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 18),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B).withOpacity(0.3) : Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: isDark ? AppColors.slate.shade850 : const Color(0xFFE2E8F0)),
      ),
      child: Column(
        children: [
          Text(
            label,
            style: TextStyle(
              color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
              fontSize: 12,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            value,
            style: TextStyle(
              color: color,
              fontSize: 18,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    );
  }
}

class SpeedometerPainter extends CustomPainter {
  final int speed;
  final bool isDark;

  SpeedometerPainter({required this.speed, required this.isDark});

  @override
  void paint(Canvas canvas, Size size) {
    final double centerX = size.width / 2;
    final double centerY = size.height / 2;
    final Offset center = Offset(centerX, centerY);
    final double radius = size.width / 2 - 10;

    // Dial background paint
    final Paint trackPaint = Paint()
      ..color = isDark ? const Color(0xFF1E293B) : const Color(0xFFE2E8F0)
      ..strokeWidth = 14
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round;

    // Draw full arc of dial (from 135 deg to 45 deg, which spans 270 deg)
    canvas.drawArc(
      Rect.fromCircle(center: center, radius: radius),
      135 * pi / 180,
      270 * pi / 180,
      false,
      trackPaint,
    );

    // Speed value arc paint
    final Paint speedPaint = Paint()
      ..color = speed > 60 ? const Color(0xFFEF4444) : const Color(0xFF10B981)
      ..strokeWidth = 14
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round;

    double sweepAngle = (speed / 100) * 270;
    sweepAngle = sweepAngle.clamp(0, 270);

    canvas.drawArc(
      Rect.fromCircle(center: center, radius: radius),
      135 * pi / 180,
      sweepAngle * pi / 180,
      false,
      speedPaint,
    );
  }

  @override
  bool shouldRepaint(covariant SpeedometerPainter oldDelegate) => 
      oldDelegate.speed != speed || oldDelegate.isDark != isDark;
}
