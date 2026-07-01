import 'package:flutter/material';
import '../data/api_service.dart';

class KioskPage extends StatefulWidget {
  const KioskPage({super.key});

  @override
  State<KioskPage> createState() => _KioskPageState();
}

class _KioskPageState extends State<KioskPage> {
  final ApiService _apiService = ApiService();
  String _statusMessage = "Stand in front of the camera to verify";
  Color _statusColor = Colors.grey;

  Future<void> _simulateScan() async {
    setState(() {
      _statusMessage = "Analyzing face & checking liveness...";
      _statusColor = Colors.orange;
    });

    // Mock embedding payload for demo scan simulation
    final mockEmbedding = List.generate(512, (index) => 0.0125);
    final result = await _apiService.matchFace(mockEmbedding, "FLUTTER_TAB_A");

    setState(() {
      if (result != null && result['matched'] == true) {
        final emp = result['employee'];
        final log = result['attendance_log'];
        _statusMessage = "Welcome ${emp['first_name']}! ${log['clock_type']} Registered.";
        _statusColor = Colors.green;
      } else {
        _statusMessage = "Access Denied. Face match not found.";
        _statusColor = Colors.red;
      }
    });

    // Reset status after 3 seconds
    await Future.delayed(const Duration(seconds: 3));
    if (mounted) {
      setState(() {
        _statusMessage = "Stand in front of the camera to verify";
        _statusColor = Colors.grey;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("AI Face Recognition Kiosk"),
        backgroundColor: const Color(0xFF005FAF),
        foregroundColor: Colors.white,
      ),
      body: Row(
        children: [
          // Left Side: Camera Placeholder Stream View
          Expanded(
            flex: 6,
            child: Container(
              color: Colors.black12,
              child: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.camera_front, size: 100, color: Colors.grey),
                    const SizedBox(height: 20),
                    ElevatedButton.icon(
                      onPressed: _simulateScan,
                      icon: const Icon(Icons.face),
                      label: const Text("Simulate Scan"),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF005FAF),
                        foregroundColor: Colors.white,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          // Right Side: Status and Settings Panel
          Expanded(
            flex: 4,
            child: Padding(
              padding: const EdgeInsets.all(24.0),
              key: const Key("status_panel"),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Text(
                    "Verification Status",
                    style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 20),
                  AnimatedContainer(
                    duration: const Duration(milliseconds: 300),
                    padding: const EdgeInsets.all(24),
                    decoration: BoxDecoration(
                      color: _statusColor.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: _statusColor, width: 2),
                    ),
                    child: Text(
                      _statusMessage,
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: _statusColor == Colors.grey ? Colors.black87 : _statusColor,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
