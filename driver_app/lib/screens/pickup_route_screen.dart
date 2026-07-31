import 'dart:async';
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';

import '../services/api_service.dart';

class PickupRouteScreen extends StatefulWidget {
  const PickupRouteScreen({super.key});

  @override
  State<PickupRouteScreen> createState() => _PickupRouteScreenState();
}

class _PickupRouteScreenState extends State<PickupRouteScreen> {
  List<dynamic> _students = [];
  bool _isLoading = true;
  StreamSubscription<Position>? _positionStream;
  DateTime? _lastPingTime;
  Position? _lastPosition;

  @override
  void initState() {
    super.initState();
    _startTripAndLoad();
    _startLocationTracking();
  }

  Future<void> _startTripAndLoad() async {
    final routeData = ApiService.assignedRoute;
    if (routeData == null || routeData['status'] != 'en_route') {
      await ApiService.startTrip();
    }
    await ApiService.fetchAssignedRoute();
    _loadStudents();
  }

  void _loadStudents() {
    if (ApiService.assignedRoute != null && ApiService.assignedRoute!['students'] != null) {
      setState(() {
        _students = ApiService.assignedRoute!['students'];
        _isLoading = false;
      });
    } else {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _updateStatus(int studentId, String status) async {
    setState(() => _isLoading = true);
    await ApiService.updateStudentStatus(studentId, status);
    await ApiService.fetchAssignedRoute();
    _loadStudents();
  }

  Future<void> _completeTrip() async {
    await ApiService.completeTrip();
    if (mounted) {
      Navigator.pop(context);
    }
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
      distanceFilter: 5,
    );

    _positionStream = Geolocator.getPositionStream(locationSettings: locationSettings).listen((Position position) {
      _handleLocationUpdate(position);
    });
  }

  void _handleLocationUpdate(Position position) {
    final now = DateTime.now();
    bool shouldPing = false;

    if (_lastPingTime == null) {
      shouldPing = true;
    } else {
      final diffSeconds = now.difference(_lastPingTime!).inSeconds;
      final speed = position.speed * 3.6; // km/h
      
      if (speed > 5 && diffSeconds >= 5) {
        shouldPing = true;
      } else if (speed <= 5 && diffSeconds >= 25) {
        shouldPing = true;
      }
    }

    if (shouldPing) {
      _lastPingTime = now;
      _lastPosition = position;
      ApiService.updateLocation(position.latitude, position.longitude, position.speed * 3.6);
    }
  }

  @override
  void dispose() {
    _positionStream?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        backgroundColor: Color(0xFF080D1A),
        body: Center(child: CircularProgressIndicator(color: Colors.blue)),
      );
    }

    final currentStudent = _students.firstWhere((s) => s['is_current'] == true, orElse: () => null);
    final waitingCount = _students.where((s) => s['status'] == 'Waiting').length;

    return Scaffold(
      backgroundColor: const Color(0xFF080D1A),
      appBar: AppBar(
        backgroundColor: const Color(0xFF0F172A),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text('Today\'s Trip', style: TextStyle(color: Colors.white)),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            if (currentStudent != null)
              _buildCurrentStudentCard(currentStudent)
            else
              const Expanded(
                child: Center(
                  child: Text('No more stops!', style: TextStyle(color: Colors.white, fontSize: 20)),
                ),
              ),
            
            const SizedBox(height: 20),
            
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: const Color(0xFF0F172A),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Remaining Students', style: TextStyle(color: Colors.white70, fontSize: 16)),
                  Text('$waitingCount', style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold)),
                ],
              ),
            ),

            const Spacer(),
            
            if (currentStudent == null)
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.green,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                  ),
                  onPressed: _completeTrip,
                  child: const Text('Complete Trip', style: TextStyle(fontSize: 18)),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildCurrentStudentCard(Map<String, dynamic> student) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: const Color(0xFF0F172A),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.blue.withOpacity(0.3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Current Stop', style: TextStyle(color: Colors.blue, fontSize: 14, fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          Text('${student['first_name']} ${student['last_name']}', style: const TextStyle(color: Colors.white, fontSize: 28, fontWeight: FontWeight.bold)),
          const SizedBox(height: 12),
          Row(
            children: [
              const Icon(Icons.location_on, color: Colors.redAccent, size: 20),
              const SizedBox(width: 8),
              Expanded(child: Text('${student['address']}', style: const TextStyle(color: Colors.white70, fontSize: 16))),
            ],
          ),
          const SizedBox(height: 20),
          Row(
            children: [
              Expanded(
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(backgroundColor: Colors.blue, padding: const EdgeInsets.symmetric(vertical: 12)),
                  onPressed: () => _updateStatus(student['id'], 'Picked Up'),
                  child: const Text('Picked Up'),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(backgroundColor: Colors.orange, padding: const EdgeInsets.symmetric(vertical: 12)),
                  onPressed: () => _updateStatus(student['id'], 'Skipped'),
                  child: const Text('Skip'),
                ),
              ),
            ],
          )
        ],
      ),
    );
  }
}
