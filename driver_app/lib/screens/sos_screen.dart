import 'dart:async';
import 'package:flutter/material.dart';
import '../theme/colors.dart';

class SosScreen extends StatefulWidget {
  const SosScreen({super.key});

  @override
  State<SosScreen> createState() => _SosScreenState();
}

class _SosScreenState extends State<SosScreen> with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  bool _isHolding = false;
  bool _alertSent = false;
  String _alertType = 'General Emergency';

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 2),
    );

    _controller.addStatusListener((status) {
      if (status == AnimationStatus.completed) {
        setState(() {
          _alertSent = true;
          _isHolding = false;
        });
        _controller.reset();
      }
    });
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _onTapDown(TapDownDetails details) {
    if (_alertSent) return;
    setState(() {
      _isHolding = true;
    });
    _controller.forward();
  }

  void _onTapUp(TapUpDetails details) {
    if (_alertSent) return;
    setState(() {
      _isHolding = false;
    });
    if (_controller.value < 1.0) {
      _controller.reverse();
    }
  }

  @override
  Widget build(BuildContext context) {
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
              'SOS Emergency',
              style: TextStyle(
                color: textColor,
                fontSize: 24,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Request immediate assistance from school dispatchers',
              style: TextStyle(
                color: subTextColor,
                fontSize: 14,
              ),
            ),
            const Spacer(),

            // SOS Center Button
            Center(
              child: _alertSent 
                  ? _buildSuccessAlert(context)
                  : _buildSosHoldButton(context),
            ),

            const Spacer(),

            // Help types grid
            Text(
              'Or select specific issue:',
              style: TextStyle(
                color: textColor,
                fontSize: 14,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(child: _helpCard(context, 'Breakdown', Icons.car_repair_rounded, Colors.orangeAccent)),
                const SizedBox(width: 12),
                Expanded(child: _helpCard(context, 'Accident', Icons.airport_shuttle_rounded, Colors.redAccent)),
                const SizedBox(width: 12),
                Expanded(child: _helpCard(context, 'Medical', Icons.local_hospital_rounded, Colors.greenAccent)),
              ],
            ),
            const SizedBox(height: 10),
          ],
        ),
      ),
    );
  }

  Widget _buildSosHoldButton(BuildContext context) {
    final bool isDark = Theme.of(context).brightness == Brightness.dark;
    return GestureDetector(
      onTapDown: _onTapDown,
      onTapUp: _onTapUp,
      onTapCancel: () {
        if (!_alertSent) {
          setState(() {
            _isHolding = false;
          });
          _controller.reverse();
        }
      },
      child: Stack(
        alignment: Alignment.center,
        clipBehavior: Clip.none,
        children: [
          // Outer pulse glow
          Container(
            width: 220,
            height: 220,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: const Color(0xFFEF4444).withOpacity(_isHolding ? 0.15 : 0.05),
            ),
          ),
          
          // Progress Radial Ring
          SizedBox(
            width: 180,
            height: 180,
            child: AnimatedBuilder(
              animation: _controller,
              builder: (context, child) {
                return CircularProgressIndicator(
                  value: _controller.value,
                  strokeWidth: 6,
                  backgroundColor: const Color(0xFFEF4444).withOpacity(0.15),
                  valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFFEF4444)),
                );
              },
            ),
          ),

          // Main Button Core
          Container(
            width: 160,
            height: 160,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: const Color(0xFFEF4444),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFFEF4444).withOpacity(0.4),
                  blurRadius: 20,
                  spreadRadius: 2,
                )
              ]
            ),
            child: const Center(
              child: Text(
                'SOS',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 40,
                  fontWeight: FontWeight.w900,
                  letterSpacing: 1.0,
                ),
              ),
            ),
          ),

          // Subtext
          Positioned(
            bottom: -30,
            child: Text(
              _isHolding ? 'Hold to confirm...' : 'Press and hold to send SOS',
              style: TextStyle(
                color: _isHolding 
                    ? const Color(0xFFEF4444) 
                    : (isDark ? AppColors.slate.shade400 : const Color(0xFF64748B)),
                fontSize: 12,
                fontWeight: FontWeight.bold,
              ),
            ),
          )
        ],
      ),
    );
  }

  Widget _buildSuccessAlert(BuildContext context) {
    final bool isDark = Theme.of(context).brightness == Brightness.dark;
    final Color textColor = isDark ? Colors.white : const Color(0xFF0F172A);
    final Color subTextColor = isDark ? AppColors.slate.shade400 : const Color(0xFF64748B);

    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF0F172A) : Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: const Color(0xFFEF4444).withOpacity(0.4)),
        boxShadow: isDark ? [] : [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 16,
            offset: const Offset(0, 8),
          )
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.check_circle_rounded, color: Color(0xFF10B981), size: 48),
          const SizedBox(height: 16),
          Text(
            'Emergency Alert Dispatched',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: textColor,
              fontSize: 18,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'School dispatchers have received your $_alertType. Help is on the way.',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: subTextColor,
              fontSize: 13,
            ),
          ),
          const SizedBox(height: 20),
          ElevatedButton(
            onPressed: () {
              setState(() {
                _alertSent = false;
              });
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: isDark ? const Color(0xFF1E293B) : const Color(0xFFF1F5F9),
              foregroundColor: textColor,
              elevation: 0,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
              ),
            ),
            child: const Text('Cancel Alert'),
          )
        ],
      ),
    );
  }

  Widget _helpCard(BuildContext context, String label, IconData icon, Color color) {
    final bool isDark = Theme.of(context).brightness == Brightness.dark;
    return InkWell(
      onTap: () {
        setState(() {
          _alertType = label;
          _alertSent = true;
        });
      },
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 8),
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF1E293B).withOpacity(0.3) : Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: isDark ? AppColors.slate.shade850 : const Color(0xFFE2E8F0)),
        ),
        child: Column(
          children: [
            Icon(icon, color: color, size: 28),
            const SizedBox(height: 8),
            Text(
              label,
              style: TextStyle(
                color: isDark ? Colors.white : const Color(0xFF0F172A),
                fontSize: 12,
                fontWeight: FontWeight.bold,
              ),
            )
          ],
        ),
      ),
    );
  }
}

