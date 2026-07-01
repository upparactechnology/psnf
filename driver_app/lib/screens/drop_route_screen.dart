import 'dart:async';
import 'package:flutter/material.dart';
import '../theme/colors.dart';
import '../models/student.dart';
import '../services/api_service.dart';
import 'dropped_home_screen.dart';
import 'dashboard_screen.dart';

class DropRouteScreen extends StatefulWidget {
  const DropRouteScreen({super.key});

  @override
  State<DropRouteScreen> createState() => _DropRouteScreenState();
}

class _DropRouteScreenState extends State<DropRouteScreen> {
  // Setup list of exactly 2 students representing the drops
  final List<Student> _drops = [
    Student(
      id: 3,
      firstName: 'Aarav',
      lastName: 'Mehta',
      className: 'Class 5',
      section: 'A',
      address: '102, Shanti Kunj, Surat, Gujarat',
      phone: '98765 43212',
      parentName: 'Mr. Amit Mehta',
      distanceKm: 0.6,
      scheduledTime: '04:15 PM',
    ),
    Student(
      id: 4,
      firstName: 'Riya',
      lastName: 'Singh',
      className: 'Class 5',
      section: 'A',
      address: 'Flat 401, Galaxy Heights, Surat, Gujarat',
      phone: '98765 43213',
      parentName: 'Mrs. Pooja Singh',
      distanceKm: 1.1,
      scheduledTime: '04:22 PM',
    ),
  ];

  // _currentDropIndex states:
  // 0 -> Driver current location, heading to School
  // 1 -> Arrived at School, picking up students (checkmark list)
  // 2 -> Heading to Student 1 drop-off
  // 3 -> Heading to Student 2 drop-off
  // 4 -> All drops completed
  int _currentDropIndex = 0;
  Timer? _locationTimer;
  double _currentLat = 19.076090;
  double _currentLng = 72.877426;

  @override
  void initState() {
    super.initState();
    // Mark status as en_route in backend
    ApiService.updateRouteStatus('en_route');
    // Start background location updates simulation
    _locationTimer = Timer.periodic(const Duration(seconds: 5), (timer) {
      _currentLat -= 0.00013;
      _currentLng -= 0.00010;
      double speed = 25.0 + (timer.tick % 4) * 6.0;
      ApiService.updateLocation(_currentLat, _currentLng, speed);
    });
  }

  @override
  void dispose() {
    _locationTimer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    bool isPickingUpAtSchool = _currentDropIndex == 1;
    bool hasRemainingDrops = _currentDropIndex >= 2 && (_currentDropIndex - 2) < _drops.length;
    Student? currentStudent = hasRemainingDrops ? _drops[_currentDropIndex - 2] : null;
    Student? nextStudent = (_currentDropIndex - 1 >= 0 && _currentDropIndex - 1 < _drops.length) ? _drops[_currentDropIndex - 1] : null;

    return Scaffold(
      backgroundColor: const Color(0xFF080D1A),
      appBar: AppBar(
        backgroundColor: const Color(0xFF0F172A),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Drop Route',
              style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
            ),
            Text(
              'Evening Route - 1',
              style: TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
            ),
          ],
        ),
      ),
      body: Stack(
        children: [
          // Custom Map Painter
          Positioned.fill(
            child: CustomPaint(
              painter: DropMapPainter(
                currentDropIndex: _currentDropIndex,
                totalDrops: 4, // Start (0) + School (1) + Student 1 (2) + Student 2 (3)
              ),
            ),
          ),

          // Floating Stop Status Indicator
          Positioned(
            top: 20,
            left: 20,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: const Color(0xFF0F172A).withOpacity(0.9),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.slate.shade850),
              ),
              child: Text(
                _currentDropIndex == 0
                    ? 'Heading to School'
                    : _currentDropIndex == 1
                        ? 'Picking Up Students'
                        : _currentDropIndex < 4
                            ? 'Drop ${_currentDropIndex - 1} of ${_drops.length}'
                            : 'All Drops Completed',
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 13,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ),

          // Bottom Action Sheet Card
          Positioned(
            left: 20,
            right: 20,
            bottom: 24,
            child: Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: const Color(0xFF0F172A).withOpacity(0.95),
                borderRadius: BorderRadius.circular(24),
                border: Border.all(color: AppColors.slate.shade850),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.5),
                    blurRadius: 20,
                    offset: const Offset(0, 10),
                  )
                ],
              ),
              child: _currentDropIndex == 0
                  ? Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Row(
                          children: [
                            CircleAvatar(
                              radius: 20,
                              backgroundColor: const Color(0xFF818CF8).withOpacity(0.2),
                              child: const Icon(Icons.school_rounded, color: Color(0xFF818CF8)),
                            ),
                            const SizedBox(width: 14),
                            const Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'First Destination',
                                    style: TextStyle(
                                      color: Color(0xFF94A3B8),
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                  SizedBox(height: 2),
                                  Text(
                                    'Green Valley School',
                                    style: TextStyle(
                                      color: Colors.white,
                                      fontSize: 16,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 20),
                        ElevatedButton(
                          onPressed: () {
                            setState(() {
                              _currentDropIndex = 1; // Arrive at school
                            });
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF6366F1),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(14),
                            ),
                          ),
                          child: const Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.directions_bus_rounded, size: 18),
                              SizedBox(width: 8),
                              Text(
                                'Navigate to School',
                                style: TextStyle(
                                  fontSize: 15,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    )
                  : isPickingUpAtSchool
                      ? Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Text(
                              'Pick Up All Students',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'Verify student boarding status (✓ or ✕)',
                              style: TextStyle(
                                color: AppColors.slate.shade400,
                                fontSize: 12,
                              ),
                            ),
                            const SizedBox(height: 16),
                            // Student checklist
                            ...List.generate(_drops.length, (index) {
                              final student = _drops[index];
                              return Container(
                                padding: const EdgeInsets.all(12),
                                margin: const EdgeInsets.only(bottom: 8),
                                decoration: BoxDecoration(
                                  color: const Color(0xFF1E293B).withOpacity(0.4),
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: AppColors.slate.shade850),
                                ),
                                child: Row(
                                  children: [
                                    CircleAvatar(
                                      radius: 16,
                                      backgroundColor: const Color(0xFF6366F1).withOpacity(0.2),
                                      child: Text(
                                        student.initials,
                                        style: const TextStyle(
                                          color: Color(0xFF818CF8),
                                          fontSize: 12,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 12),
                                    Expanded(
                                      child: Text(
                                        student.fullName,
                                        style: const TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.bold),
                                      ),
                                    ),
                                    // Toggle Boarded (✓)
                                    GestureDetector(
                                      onTap: () {
                                        setState(() {
                                          student.status = StudentStatus.onboard;
                                        });
                                      },
                                      child: Container(
                                        padding: const EdgeInsets.all(6),
                                        decoration: BoxDecoration(
                                          color: student.status == StudentStatus.onboard 
                                              ? const Color(0xFF10B981).withOpacity(0.2) 
                                              : Colors.transparent,
                                          shape: BoxShape.circle,
                                          border: Border.all(
                                            color: student.status == StudentStatus.onboard 
                                                ? const Color(0xFF10B981) 
                                                : AppColors.slate.shade700,
                                          ),
                                        ),
                                        child: Icon(
                                          Icons.check_rounded,
                                          size: 14,
                                          color: student.status == StudentStatus.onboard ? const Color(0xFF10B981) : AppColors.slate.shade400,
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    // Toggle Absent (✕)
                                    GestureDetector(
                                      onTap: () {
                                        setState(() {
                                          student.status = StudentStatus.missed;
                                        });
                                      },
                                      child: Container(
                                        padding: const EdgeInsets.all(6),
                                        decoration: BoxDecoration(
                                          color: student.status == StudentStatus.missed 
                                              ? const Color(0xFFEF4444).withOpacity(0.2) 
                                              : Colors.transparent,
                                          shape: BoxShape.circle,
                                          border: Border.all(
                                            color: student.status == StudentStatus.missed 
                                                ? const Color(0xFFEF4444) 
                                                : AppColors.slate.shade700,
                                          ),
                                        ),
                                        child: Icon(
                                          Icons.close_rounded,
                                          size: 14,
                                          color: student.status == StudentStatus.missed ? const Color(0xFFEF4444) : AppColors.slate.shade400,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              );
                            }),
                            const SizedBox(height: 16),
                            ElevatedButton(
                              onPressed: () {
                                setState(() {
                                  _currentDropIndex = 2; // Depart school, go to Drop 1
                                });
                              },
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF6366F1),
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(vertical: 16),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(14),
                                ),
                              ),
                              child: const Text(
                                'Confirm Pickups & Depart',
                                style: TextStyle(
                                  fontSize: 15,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ],
                        )
                      : hasRemainingDrops
                          ? Column(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Row(
                                  children: [
                                    CircleAvatar(
                                      radius: 20,
                                      backgroundColor: const Color(0xFF6366F1).withOpacity(0.2),
                                      child: Text(
                                        currentStudent!.initials,
                                        style: const TextStyle(
                                          color: Color(0xFF818CF8),
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 14),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            'Next Drop (Stop ${_currentDropIndex - 1})',
                                            style: const TextStyle(
                                              color: Color(0xFF94A3B8),
                                              fontSize: 11,
                                              fontWeight: FontWeight.bold,
                                            ),
                                          ),
                                          const SizedBox(height: 2),
                                          Text(
                                            currentStudent.fullName,
                                            style: const TextStyle(
                                              color: Colors.white,
                                              fontSize: 16,
                                              fontWeight: FontWeight.bold,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFF6366F1).withOpacity(0.15),
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: Text(
                                        '${currentStudent.distanceKm} km',
                                        style: const TextStyle(
                                          color: Color(0xFF818CF8),
                                          fontSize: 12,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 20),
                                ElevatedButton(
                                  onPressed: () async {
                                    // Launch drop success screen
                                    await Navigator.push(
                                      context,
                                      MaterialPageRoute(
                                        builder: (context) => DroppedHomeScreen(student: currentStudent),
                                      ),
                                    );

                                    setState(() {
                                      _drops[_currentDropIndex - 2].status = StudentStatus.dropped;
                                      _currentDropIndex++; // Move to next drop
                                    });
                                  },
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: const Color(0xFF6366F1),
                                    foregroundColor: Colors.white,
                                    padding: const EdgeInsets.symmetric(vertical: 16),
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(14),
                                    ),
                                  ),
                                  child: const Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Icon(Icons.near_me_rounded, size: 18),
                                      SizedBox(width: 8),
                                      Text(
                                        'Navigate',
                                        style: TextStyle(
                                          fontSize: 15,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                if (nextStudent != null) ...[
                                  const SizedBox(height: 14),
                                  Center(
                                    child: Text(
                                      'Next: ${nextStudent.fullName}',
                                      style: TextStyle(
                                        color: AppColors.slate.shade450,
                                        fontSize: 12,
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                  ),
                                ]
                              ],
                            )
                          : Column(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Row(
                                  children: [
                                    Icon(Icons.check_circle_rounded, color: Color(0xFF10B981), size: 28),
                                    SizedBox(width: 14),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            'Evening Shifts Completed',
                                            style: TextStyle(
                                              color: Colors.white,
                                              fontSize: 16,
                                              fontWeight: FontWeight.bold,
                                            ),
                                          ),
                                          SizedBox(height: 2),
                                          Text(
                                            'All student drop-offs completed.',
                                            style: TextStyle(
                                              color: Color(0xFF94A3B8),
                                              fontSize: 12,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 20),
                                ElevatedButton(
                                  onPressed: () async {
                                    await ApiService.updateRouteStatus('completed');
                                    DashboardScreen.isDropCompleted = true;
                                    Navigator.pop(context); // Go back to dashboard / complete task
                                  },
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: const Color(0xFF6366F1),
                                    foregroundColor: Colors.white,
                                    padding: const EdgeInsets.symmetric(vertical: 16),
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(14),
                                    ),
                                  ),
                                  child: const Text(
                                    'Complete Task',
                                    style: TextStyle(
                                      fontSize: 15,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                              ],
                            ),
            ),
          )
        ],
      ),
    );
  }
}

class DropMapPainter extends CustomPainter {
  final int currentDropIndex;
  final int totalDrops;

  DropMapPainter({required this.currentDropIndex, required this.totalDrops});

  @override
  void paint(Canvas canvas, Size size) {
    final Paint linePaint = Paint()
      ..color = const Color(0xFF818CF8)
      ..strokeWidth = 4
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round;

    final Paint dashedPaint = Paint()
      ..color = const Color(0xFF475569)
      ..strokeWidth = 3
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round;

    final Paint bgRoadPaint = Paint()
      ..color = const Color(0xFF1E293B).withOpacity(0.3)
      ..strokeWidth = 24
      ..style = PaintingStyle.stroke;

    // Define 4 nodes coordinates representing the evening routing graph:
    // Index 0: Driver start location
    // Index 1: School Location
    // Index 2: Student 1 drop location
    // Index 3: Student 2 drop location
    final List<Offset> points = [
      Offset(size.width * 0.15, size.height * 0.15), // Driver Start Location
      Offset(size.width * 0.50, size.height * 0.35), // School Location
      Offset(size.width * 0.70, size.height * 0.60), // Student 1
      Offset(size.width * 0.30, size.height * 0.80), // Student 2
    ];

    // Background road matching the path
    final Path roadPath = Path()
      ..moveTo(points[0].dx, points[0].dy)
      ..lineTo(points[1].dx, points[1].dy)
      ..lineTo(points[2].dx, points[2].dy)
      ..lineTo(points[3].dx, points[3].dy);
    canvas.drawPath(roadPath, bgRoadPaint);

    // Draw active path
    final Path activePath = Path();
    if (points.isNotEmpty) {
      activePath.moveTo(points[0].dx, points[0].dy);
      // Wait:
      // If currentDropIndex is 0 (heading to school), bus is at 0. Path stops at 0.
      // If currentDropIndex is 1 (picking up at school), bus is at 1. Path is 0->1.
      // If currentDropIndex is 2 (heading to student 1), bus is at 1. Path is 0->1.
      // If currentDropIndex is 3 (heading to student 2), bus is at 2. Path is 0->1->2.
      // If currentDropIndex is 4 (all completed), bus is at 3. Path is 0->1->2->3.
      int activeIndex = currentDropIndex;
      if (currentDropIndex == 2) activeIndex = 1; // Bus remains at School until drop-off navigation starts
      if (currentDropIndex == 3) activeIndex = 2; // Bus is at Student 1
      if (currentDropIndex == 4) activeIndex = 3; // Bus is at Student 2
      
      for (int i = 1; i <= activeIndex && i < points.length; i++) {
        activePath.lineTo(points[i].dx, points[i].dy);
      }
      canvas.drawPath(activePath, linePaint);
    }

    // Draw remaining route (dashed path)
    int busIndex = currentDropIndex;
    if (currentDropIndex == 2) busIndex = 1;
    if (currentDropIndex == 3) busIndex = 2;
    if (currentDropIndex == 4) busIndex = 3;
    
    if (busIndex < points.length - 1) {
      final Path inactivePath = Path();
      inactivePath.moveTo(points[busIndex].dx, points[busIndex].dy);
      for (int i = busIndex + 1; i < points.length; i++) {
        inactivePath.lineTo(points[i].dx, points[i].dy);
      }
      canvas.drawPath(inactivePath, dashedPaint);
    }

    // Draw Driver Current Location marker (Node 0)
    canvas.drawCircle(points[0], 12, Paint()..color = Colors.grey);
    canvas.drawCircle(points[0], 9, Paint()..color = const Color(0xFF0F172A));
    final TextPainter startTextPainter = TextPainter(
      text: const TextSpan(
        text: 'D',
        style: TextStyle(color: Colors.grey, fontSize: 10, fontWeight: FontWeight.bold),
      ),
      textDirection: TextDirection.ltr,
    )..layout();
    startTextPainter.paint(
      canvas,
      points[0] - Offset(startTextPainter.width / 2, startTextPainter.height / 2),
    );

    // Draw School Location marker (Node 1)
    bool schoolActive = currentDropIndex == 0;
    bool schoolReached = currentDropIndex >= 1;
    Color schoolColor = schoolReached 
        ? const Color(0xFF10B981) 
        : (schoolActive ? const Color(0xFF6366F1) : const Color(0xFF475569));

    canvas.drawCircle(points[1], 18, Paint()..color = Colors.black.withOpacity(0.3));
    canvas.drawCircle(points[1], 15, Paint()..color = schoolColor);
    canvas.drawCircle(points[1], 12, Paint()..color = const Color(0xFF0F172A));
    
    final TextPainter schoolTextPainter = TextPainter(
      text: TextSpan(
        text: 'S',
        style: TextStyle(color: schoolColor, fontSize: 11, fontWeight: FontWeight.bold),
      ),
      textDirection: TextDirection.ltr,
    )..layout();
    schoolTextPainter.paint(
      canvas,
      points[1] - Offset(schoolTextPainter.width / 2, schoolTextPainter.height / 2),
    );

    // Draw Student drop markers (Nodes 2 & 3)
    for (int i = 1; i <= 2; i++) {
      // Point index in points is i + 1
      int pointIdx = i + 1;
      bool isCompleted = currentDropIndex >= pointIdx + 1; // e.g. Student 1 is completed if index is >= 3
      bool isActive = currentDropIndex == pointIdx; // e.g. Student 1 is active target if index is 2

      Color markerColor = isCompleted
          ? const Color(0xFF10B981)
          : (isActive ? const Color(0xFF6366F1) : const Color(0xFF475569));

      canvas.drawCircle(points[pointIdx], 16, Paint()..color = Colors.black.withOpacity(0.3));
      canvas.drawCircle(points[pointIdx], 13, Paint()..color = markerColor);
      canvas.drawCircle(points[pointIdx], 10, Paint()..color = const Color(0xFF0F172A));

      final TextPainter textPainter = TextPainter(
        text: TextSpan(
          text: '$i',
          style: TextStyle(
            color: markerColor,
            fontSize: 11,
            fontWeight: FontWeight.bold,
          ),
        ),
        textDirection: TextDirection.ltr,
      )..layout();
      textPainter.paint(
        canvas, 
        points[pointIdx] - Offset(textPainter.width / 2, textPainter.height / 2),
      );
    }

    // Draw active Bus marker at current location
    int activeBusPosIdx = currentDropIndex;
    if (currentDropIndex == 2) activeBusPosIdx = 1;
    if (currentDropIndex == 3) activeBusPosIdx = 2;
    if (currentDropIndex == 4) activeBusPosIdx = 3;

    if (activeBusPosIdx < points.length) {
      Offset busPos = points[activeBusPosIdx];
      canvas.drawCircle(
        busPos, 
        24, 
        Paint()..color = const Color(0xFF6366F1).withOpacity(0.2),
      );
      canvas.drawCircle(busPos, 8, Paint()..color = const Color(0xFFFEF08A));
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => true;
}
