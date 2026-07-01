import 'dart:async';
import 'package:flutter/material.dart';
import '../theme/colors.dart';
import '../models/student.dart';
import '../services/api_service.dart';
import 'next_stop_screen.dart';
import 'students_onboard_screen.dart';
import 'school_arrival_screen.dart';

class PickupRouteScreen extends StatefulWidget {
  const PickupRouteScreen({super.key});

  @override
  State<PickupRouteScreen> createState() => _PickupRouteScreenState();
}

class _PickupRouteScreenState extends State<PickupRouteScreen> {
  // Setup list of 4 students representing the stops
  final List<Student> _stops = [
    Student(
      id: 1,
      firstName: 'Rahul',
      lastName: 'Sharma',
      className: 'Class 5',
      section: 'A',
      address: '12, Green Park Society, Surat, Gujarat',
      phone: '98765 43210',
      parentName: 'Mr. Vijay Sharma',
      distanceKm: 0.4,
      scheduledTime: '08:10 AM',
    ),
    Student(
      id: 2,
      firstName: 'Anjali',
      lastName: 'Patel',
      className: 'Class 5',
      section: 'A',
      address: '45, Royal Residency, Surat, Gujarat',
      phone: '98765 43211',
      parentName: 'Mrs. Rekha Patel',
      distanceKm: 0.8,
      scheduledTime: '08:12 AM',
    ),
    Student(
      id: 3,
      firstName: 'Aarav',
      lastName: 'Mehta',
      className: 'Class 5',
      section: 'A',
      address: '102, Shanti Kunj, Surat, Gujarat',
      phone: '98765 43212',
      parentName: 'Mr. Amit Mehta',
      distanceKm: 1.2,
      scheduledTime: '08:15 AM',
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
      distanceKm: 1.6,
      scheduledTime: '08:18 AM',
    ),
  ];

  int _currentStopIndex = 0;
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
      _currentLat += 0.00015;
      _currentLng += 0.00012;
      double speed = 30.0 + (timer.tick % 5) * 5.0;
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
    bool hasRemainingStops = _currentStopIndex < _stops.length;
    Student? currentStudent = hasRemainingStops ? _stops[_currentStopIndex] : null;

    int totalStudentsOnboard = _stops.where((s) => s.status == StudentStatus.onboard).length;

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
              'Pickup Route',
              style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
            ),
            Text(
              'Morning Route - 1',
              style: TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.people_rounded, color: Colors.white),
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => StudentsOnboardScreen(students: _stops),
                ),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.more_vert_rounded, color: Colors.white),
            onPressed: () {},
          ),
        ],
      ),
      body: Stack(
        children: [
          // Custom Canvas Map View
          Positioned.fill(
            child: CustomPaint(
              painter: MapPainter(
                currentStopIndex: _currentStopIndex,
                totalStops: 6, // Driver location (0) + 4 students + 1 school
              ),
            ),
          ),
          
          // Floating Stop Indicators
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
              child: Row(
                children: [
                  const Icon(Icons.location_on_rounded, color: Color(0xFF818CF8), size: 16),
                  const SizedBox(width: 8),
                  Text(
                    hasRemainingStops
                        ? 'Stop ${_currentStopIndex + 1} of ${_stops.length}'
                        : _currentStopIndex == 4
                            ? 'Heading to School'
                            : 'School Reached',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
            ),
          ),

          // Floating Onboard Student Summary Card
          Positioned(
            top: 20,
            right: 20,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: const Color(0xFF0F172A).withOpacity(0.9),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.slate.shade850),
              ),
              child: Text(
                'Onboard: $totalStudentsOnboard',
                style: const TextStyle(
                  color: Color(0xFF34D399),
                  fontSize: 13,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ),

          // Bottom Stop Action Card
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
              child: hasRemainingStops 
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
                                  const Text(
                                    'Next Stop',
                                    style: TextStyle(
                                      color: Color(0xFF94A3B8),
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                      letterSpacing: 0.5,
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
                        const SizedBox(height: 16),
                        Row(
                          children: [
                            const Icon(Icons.navigation_rounded, color: AppColors.slate, size: 16),
                            const SizedBox(width: 6),
                            Expanded(
                              child: Text(
                                currentStudent.address,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: TextStyle(
                                  color: AppColors.slate.shade400,
                                  fontSize: 13,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 20),
                        ElevatedButton(
                          onPressed: () async {
                            // Navigate to Next Stop Detail screen and await result
                            final result = await Navigator.push<StudentStatus>(
                              context,
                              MaterialPageRoute(
                                builder: (context) => NextStopScreen(student: currentStudent),
                              ),
                            );

                            if (result != null) {
                              setState(() {
                                _stops[_currentStopIndex].status = result;
                                _stops[_currentStopIndex].actionTime = 
                                    '${DateTime.now().hour.toString().padLeft(2, '0')}:${DateTime.now().minute.toString().padLeft(2, '0')} AM';
                                _currentStopIndex++;
                              });
                            }
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
                      ],
                    )
                  : _currentStopIndex == 4
                      ? Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Row(
                              children: [
                                CircleAvatar(
                                  radius: 20,
                                  backgroundColor: const Color(0xFF10B981).withOpacity(0.2),
                                  child: const Icon(
                                    Icons.school_rounded,
                                    color: Color(0xFF34D399),
                                  ),
                                ),
                                const SizedBox(width: 14),
                                const Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        'All Students Visited',
                                        style: TextStyle(
                                          color: Color(0xFF34D399),
                                          fontSize: 11,
                                          fontWeight: FontWeight.bold,
                                          letterSpacing: 0.5,
                                        ),
                                      ),
                                      SizedBox(height: 2),
                                      Text(
                                        'Heading to School',
                                        style: TextStyle(
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
                                    color: const Color(0xFFF59E0B).withOpacity(0.15),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: const Text(
                                    '2.4 km',
                                    style: TextStyle(
                                      color: Color(0xFFF59E0B),
                                      fontSize: 12,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 16),
                            Row(
                              children: [
                                const Icon(Icons.navigation_rounded, color: AppColors.slate, size: 16),
                                const SizedBox(width: 6),
                                Expanded(
                                  child: Text(
                                    'Green Valley School, Surat, Gujarat',
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                    style: TextStyle(
                                      color: AppColors.slate.shade400,
                                      fontSize: 13,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 20),
                            ElevatedButton(
                              onPressed: () {
                                setState(() {
                                  _currentStopIndex = 5; // Reached school
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
                                        'Arrived at School Location',
                                        style: TextStyle(
                                          color: Colors.white,
                                          fontSize: 16,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                      SizedBox(height: 2),
                                      Text(
                                        'Ready to confirm school arrival.',
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
                              onPressed: () {
                                Navigator.pushReplacement(
                                  context,
                                  MaterialPageRoute(
                                    builder: (context) => SchoolArrivalScreen(
                                      onboardCount: totalStudentsOnboard,
                                    ),
                                  ),
                                );
                              },
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF10B981),
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(vertical: 16),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(14),
                                ),
                              ),
                              child: const Text(
                                'Confirm School Arrival',
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

class MapPainter extends CustomPainter {
  final int currentStopIndex;
  final int totalStops;

  MapPainter({required this.currentStopIndex, required this.totalStops});

  @override
  void paint(Canvas canvas, Size size) {
    final Paint linePaint = Paint()
      ..color = const Color(0xFF6366F1)
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

    // Define 6 nodes coordinates representing the graph:
    // Index 0: Driver Current Start Location
    // Index 1-4: Student stops 1 to 4
    // Index 5: School Location
    final List<Offset> points = [
      Offset(size.width * 0.15, size.height * 0.15), // Driver Current Location
      Offset(size.width * 0.35, size.height * 0.28), // Student 1
      Offset(size.width * 0.75, size.height * 0.32), // Student 2
      Offset(size.width * 0.65, size.height * 0.52), // Student 3
      Offset(size.width * 0.25, size.height * 0.58), // Student 4
      Offset(size.width * 0.50, size.height * 0.80), // School Location
    ];

    // Background road matching the routing path
    final Path roadPath = Path()
      ..moveTo(points[0].dx, points[0].dy)
      ..lineTo(points[1].dx, points[1].dy)
      ..lineTo(points[2].dx, points[2].dy)
      ..lineTo(points[3].dx, points[3].dy)
      ..lineTo(points[4].dx, points[4].dy)
      ..lineTo(points[5].dx, points[5].dy);
    canvas.drawPath(roadPath, bgRoadPaint);

    // Draw active route path
    final Path activePath = Path();
    if (points.isNotEmpty) {
      activePath.moveTo(points[0].dx, points[0].dy);
      for (int i = 1; i <= currentStopIndex && i < points.length; i++) {
        activePath.lineTo(points[i].dx, points[i].dy);
      }
      canvas.drawPath(activePath, linePaint);
    }

    // Draw remaining route (dashed path)
    if (currentStopIndex < points.length - 1) {
      final Path inactivePath = Path();
      inactivePath.moveTo(points[currentStopIndex].dx, points[currentStopIndex].dy);
      for (int i = currentStopIndex + 1; i < points.length; i++) {
        inactivePath.lineTo(points[i].dx, points[i].dy);
      }
      canvas.drawPath(inactivePath, dashedPaint);
    }

    // Draw Driver Current Location marker
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

    // Draw Student stop markers
    for (int i = 1; i <= 4; i++) {
      bool isCompleted = currentStopIndex >= i;
      bool isActive = currentStopIndex == i - 1;

      Color markerColor = isCompleted
          ? const Color(0xFF10B981) // Green for complete
          : (isActive ? const Color(0xFF6366F1) : const Color(0xFF475569));

      canvas.drawCircle(points[i], 16, Paint()..color = Colors.black.withOpacity(0.3));
      canvas.drawCircle(points[i], 13, Paint()..color = markerColor);
      canvas.drawCircle(points[i], 10, Paint()..color = const Color(0xFF0F172A));

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
        points[i] - Offset(textPainter.width / 2, textPainter.height / 2),
      );
    }

    // Draw School Location marker
    bool schoolActive = currentStopIndex == 4;
    bool schoolReached = currentStopIndex == 5;
    Color schoolColor = schoolReached 
        ? const Color(0xFF10B981) 
        : (schoolActive ? const Color(0xFF6366F1) : const Color(0xFF475569));

    canvas.drawCircle(points[5], 20, Paint()..color = Colors.black.withOpacity(0.3));
    canvas.drawCircle(points[5], 16, Paint()..color = schoolColor);
    canvas.drawCircle(points[5], 13, Paint()..color = const Color(0xFF0F172A));

    final TextPainter schoolTextPainter = TextPainter(
      text: TextSpan(
        text: 'S',
        style: TextStyle(
          color: schoolColor,
          fontSize: 12,
          fontWeight: FontWeight.bold,
        ),
      ),
      textDirection: TextDirection.ltr,
    )..layout();
    schoolTextPainter.paint(
      canvas, 
      points[5] - Offset(schoolTextPainter.width / 2, schoolTextPainter.height / 2),
    );

    // Draw active Bus marker
    if (currentStopIndex < points.length) {
      Offset busPos = points[currentStopIndex];
      canvas.drawCircle(
        busPos, 
        24, 
        Paint()..color = const Color(0xFF6366F1).withOpacity(0.2),
      );
      canvas.drawCircle(busPos, 8, Paint()..color = const Color(0xFFFEF08A));
    }
  }

  @override
  bool shouldRepaint(covariant MapPainter oldDelegate) => 
      oldDelegate.currentStopIndex != currentStopIndex;
}
