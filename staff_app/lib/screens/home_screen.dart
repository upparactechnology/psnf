import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';
import 'guardian_directory_screen.dart';

class HomeScreen extends StatefulWidget {
  final Function(int)? onNavigateTab;
  const HomeScreen({super.key, this.onNavigateTab});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  bool _isLoading = true;
  bool _actionLoading = false;

  Map<String, dynamic> _attendanceData = {
    'checked_in': false,
    'check_in_time': null,
    'check_out_time': null,
    'total_working': '--:--',
    'status': 'Not Checked In'
  };

  List<dynamic> _earlyStudents = [];
  Map<String, dynamic> _summaryData = {
    'total': 0,
    'present': 0,
    'absent': 0
  };

  @override
  void initState() {
    super.initState();
    _loadDashboardData();
  }

  Future<void> _loadDashboardData() async {
    setState(() => _isLoading = true);
    try {
      final results = await Future.wait([
        ApiService.getTodayAttendance(),
        ApiService.getSummary(),
      ]);
      final attendance = results[0];
      final summary = results[1];

      if (mounted) {
        setState(() {
          if (attendance['success'] == true) {
            _attendanceData = attendance;
          }
          if (summary['success'] == true) {
            _summaryData = summary['summary'] ?? _summaryData;
          }
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _handleCheckIn() async {
    setState(() => _actionLoading = true);
    final result = await ApiService.checkIn();
    setState(() => _actionLoading = false);

    if (mounted) {
      if (result['success'] == true) {
        setState(() {
          _attendanceData = result;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Checked in successfully!'), backgroundColor: Colors.green),
        );
        _loadDashboardData(); // Reload statistics
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'] ?? 'Check in failed'), backgroundColor: Colors.red),
        );
      }
    }
  }

  Future<void> _handleCheckOut() async {
    // Show confirmation dialog
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Confirm Check Out'),
        content: const Text('Are you sure you want to check out for today?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancel')),
          TextButton(onPressed: () => Navigator.pop(context, true), child: const Text('Check Out')),
        ],
      ),
    );

    if (confirm != true) return;

    setState(() => _actionLoading = true);
    final result = await ApiService.checkOut();
    setState(() => _actionLoading = false);

    if (mounted) {
      if (result['success'] == true) {
        setState(() {
          _attendanceData = result;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Checked out successfully!'), backgroundColor: Colors.green),
        );
        _loadDashboardData(); // Reload statistics
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'] ?? 'Check out failed'), backgroundColor: Colors.red),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final profile = ApiService.userProfile ?? {'name': 'Priya Mehta', 'role': 'Educator', 'id': 'ST1025'};
    final String staffName = profile['name'] ?? 'Priya Mehta';
    final String staffEmail = profile['email'] ?? '';
    final String staffId = profile['id'] != null ? 'ID: ${profile['id']}' : 'ID: ST1025';
    
    // Format date
    final String formattedDate = DateFormat('dd MMM, yyyy').format(DateTime.now());

    if (_isLoading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }

    return Scaffold(
      body: RefreshIndicator(
        onRefresh: _loadDashboardData,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // 1. Dark Green Header with Profile Card
              Stack(
                clipBehavior: Clip.none,
                children: [
                  Container(
                    height: 220,
                    decoration: const BoxDecoration(
                      gradient: LinearGradient(
                        colors: [Color(0xFF0A5C36), Color(0xFF053E23)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.only(
                        bottomLeft: Radius.circular(36),
                        bottomRight: Radius.circular(36),
                      ),
                    ),
                    padding: const EdgeInsets.only(top: 60, left: 24, right: 24),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(4),
                                  decoration: const BoxDecoration(
                                    color: Colors.white,
                                    shape: BoxShape.circle,
                                  ),
                                  child: ClipRRect(
                                    borderRadius: BorderRadius.circular(12),
                                    child: Image.asset(
                                      'assets/images/logo.png',
                                      width: 28,
                                      height: 28,
                                      fit: BoxFit.contain,
                                      errorBuilder: (context, error, stackTrace) => const Icon(
                                        Icons.spa_rounded,
                                        color: Color(0xFF0A5C36),
                                        size: 24,
                                      ),
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: const [
                                    Text(
                                      'PSNF',
                                      style: TextStyle(
                                        color: Colors.white,
                                        fontSize: 20,
                                        fontWeight: FontWeight.bold,
                                        height: 1.1,
                                      ),
                                    ),
                                    Text(
                                      'Pearl Special Needs Foundation',
                                      style: TextStyle(
                                        color: Colors.white70,
                                        fontSize: 10,
                                      ),
                                    ),
                                    Text(
                                      'Staff App',
                                      style: TextStyle(
                                        color: Color(0xFF86EFAC),
                                        fontSize: 10,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                            IconButton(
                              icon: const Icon(Icons.notifications_none_rounded, color: Colors.white, size: 28),
                              onPressed: () {},
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  // Overlapping Profile Card
                  Positioned(
                    top: 130,
                    left: 20,
                    right: 20,
                    child: Card(
                      elevation: 4,
                      shadowColor: Colors.black12,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(24),
                        side: const BorderSide(color: Colors.white, width: 1.5),
                      ),
                      child: Padding(
                        padding: const EdgeInsets.all(18.0),
                        child: Row(
                          children: [
                            CircleAvatar(
                              radius: 30,
                              backgroundColor: const Color(0xFFE2E8F0),
                              child: Text(
                                staffName.isNotEmpty ? staffName.substring(0, 1) : 'P',
                                style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Color(0xFF0A5C36)),
                              ),
                            ),
                            const SizedBox(width: 16),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Hi, $staffName',
                                    style: const TextStyle(
                                      fontSize: 18,
                                      fontWeight: FontWeight.bold,
                                      color: Color(0xFF1E293B),
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  Row(
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                        decoration: BoxDecoration(
                                          color: const Color(0xFFE6F4EA),
                                          borderRadius: BorderRadius.circular(6),
                                        ),
                                        child: const Text(
                                          'Educator',
                                          style: TextStyle(
                                            color: Color(0xFF137333),
                                            fontSize: 11,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      Text(
                                        staffId,
                                        style: TextStyle(
                                          color: Colors.grey.shade400,
                                          fontSize: 12,
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 70),

              // 2. Today's Attendance Card
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20.0),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          "Today's Attendance",
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                        ),
                        Text(
                          formattedDate,
                          style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(20.0),
                        child: Column(
                          children: [
                            GridView.count(
                              shrinkWrap: true,
                              physics: const NeverScrollableScrollPhysics(),
                              crossAxisCount: 2,
                              childAspectRatio: 2.2,
                              crossAxisSpacing: 16,
                              mainAxisSpacing: 16,
                              children: [
                                _buildAttendanceItem(
                                  Icons.login_rounded,
                                  const Color(0xFF137333),
                                  const Color(0xFFE6F4EA),
                                  'Check In',
                                  _attendanceData['check_in_time'] ?? '--:--',
                                  _attendanceData['check_in_time'] != null,
                                ),
                                _buildAttendanceItem(
                                  Icons.logout_rounded,
                                  const Color(0xFFC5221F),
                                  const Color(0xFFFCE8E6),
                                  'Check Out',
                                  _attendanceData['check_out_time'] ?? '--:--',
                                  _attendanceData['check_out_time'] != null,
                                ),
                                _buildAttendanceItem(
                                  Icons.alarm_on_rounded,
                                  const Color(0xFF1A73E8),
                                  const Color(0xFFE8F0FE),
                                  'Total Working',
                                  _attendanceData['total_working'] ?? '--:--',
                                  _attendanceData['checked_in'] == true,
                                ),
                                _buildAttendanceItem(
                                  Icons.offline_pin_rounded,
                                  const Color(0xFFE37400),
                                  const Color(0xFFFEF3D6),
                                  'Status',
                                  _attendanceData['status'] ?? 'Absent',
                                  _attendanceData['checked_in'] == true,
                                ),
                              ],
                            ),
                            const SizedBox(height: 16),
                            if (_actionLoading)
                              const CircularProgressIndicator()
                            else if (_attendanceData['checked_in'] == false)
                              ElevatedButton.icon(
                                onPressed: _handleCheckIn,
                                icon: const Icon(Icons.login_rounded, color: Colors.white),
                                label: const Text('Check In Now', style: TextStyle(fontWeight: FontWeight.bold)),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFF0A5C36),
                                  foregroundColor: Colors.white,
                                  minimumSize: const Size(double.infinity, 48),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                              )
                            else if (_attendanceData['checked_in'] == true && _attendanceData['check_out_time'] == null)
                              ElevatedButton.icon(
                                onPressed: _handleCheckOut,
                                icon: const Icon(Icons.logout_rounded, color: Colors.white),
                                label: const Text('Check Out Now', style: TextStyle(fontWeight: FontWeight.bold)),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFFC5221F),
                                  foregroundColor: Colors.white,
                                  minimumSize: const Size(double.infinity, 48),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                              )
                            else
                              Container(
                                padding: const EdgeInsets.symmetric(vertical: 12),
                                decoration: BoxDecoration(
                                  color: Colors.grey.shade50,
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: Colors.grey.shade100),
                                ),
                                width: double.infinity,
                                child: const Center(
                                  child: Text(
                                    'Today\'s Attendance Completed 🎉',
                                    style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF137333)),
                                  ),
                                ),
                              ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),




              // 5. Today's Summary Row
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      "Today's Summary",
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: _buildSummaryCard(
                            'Total Students',
                            _summaryData['total'].toString(),
                            Icons.people_alt_rounded,
                            Colors.blue.shade600,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: _buildSummaryCard(
                            'Present',
                            _summaryData['present'].toString(),
                            Icons.check_circle_rounded,
                            Colors.green.shade600,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: _buildSummaryCard(
                            'Absent',
                            _summaryData['absent'].toString(),
                            Icons.cancel_rounded,
                            Colors.red.shade600,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 40),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildAttendanceItem(IconData icon, Color color, Color bg, String title, String val, bool isActive) {
    return Container(
      padding: const EdgeInsets.all(8),
      decoration: BoxDecoration(
        color: Colors.grey.shade50,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade100),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(6),
            decoration: BoxDecoration(
              color: bg,
              shape: BoxShape.circle,
            ),
            child: Icon(icon, color: color, size: 18),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(title, style: TextStyle(fontSize: 10, color: Colors.grey.shade500)),
                Text(
                  val,
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                    color: isActive ? color : const Color(0xFF1E293B),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickAccessItem(IconData icon, Color color, String label, VoidCallback onTap) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: SizedBox(
        width: 76,
        child: Column(
          children: [
            Container(
              height: 54,
              width: 54,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(18),
                boxShadow: const [
                  BoxShadow(color: Colors.black12, blurRadius: 4, offset: Offset(0, 2)),
                ],
              ),
              child: Icon(icon, color: color, size: 26),
            ),
            const SizedBox(height: 8),
            Text(
              label,
              textAlign: TextAlign.center,
              maxLines: 2,
              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w500, color: Color(0xFF1E293B)),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSummaryCard(String title, String val, IconData icon, Color color) {
    return Card(
      margin: EdgeInsets.zero,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 12.0, vertical: 14.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  val,
                  style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                ),
                Icon(icon, color: color, size: 20),
              ],
            ),
            const SizedBox(height: 6),
            Text(
              title,
              style: TextStyle(fontSize: 11, color: Colors.grey.shade500, fontWeight: FontWeight.w500),
            ),
          ],
        ),
      ),
    );
  }
}
