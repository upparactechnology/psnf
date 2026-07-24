import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';

class AttendanceScreen extends StatefulWidget {
  const AttendanceScreen({super.key});

  @override
  State<AttendanceScreen> createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen> {
  bool _isLoading = true;

  Map<String, dynamic> _attendanceData = {
    'checked_in': false,
    'check_in_time': null,
    'check_out_time': null,
    'total_working': '--:--',
    'status': 'Not Checked In'
  };

  List<dynamic> _attendanceHistory = [];
  List<dynamic> _earlyStudents = [];
  late List<DateTime> _weekDays;
  late DateTime _selectedDay;

  @override
  void initState() {
    super.initState();
    _selectedDay = DateTime.now();
    _generateWeekDays();
    _loadAttendanceData();
  }

  void _generateWeekDays() {
    // Generate 7 days centered around the selected day (3 before, 3 after)
    _weekDays = List.generate(7, (index) {
      return _selectedDay.subtract(Duration(days: 3 - index));
    });
  }

  Future<void> _loadAttendanceData() async {
    setState(() => _isLoading = true);
    try {
      final attendance = await ApiService.getTodayAttendance();
      final history = await ApiService.getAttendanceHistory();
      final earlyStudents = await ApiService.getEarlyStudents();

      if (mounted) {
        setState(() {
          if (attendance['success'] == true) {
            _attendanceData = attendance;
          }
          if (history['success'] == true) {
            _attendanceHistory = history['history'] ?? [];
          }
          if (earlyStudents['success'] == true) {
            _earlyStudents = earlyStudents['students'] ?? [];
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

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final String formattedMonth = DateFormat('MMMM yyyy').format(_selectedDay);
    final String todayString = DateFormat('EEEE, d MMM yyyy').format(DateTime.now());

    if (_isLoading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, size: 20),
          onPressed: () {},
        ),
        title: const Text('My Attendance'),
        actions: [
          IconButton(
            icon: const Icon(Icons.calendar_today_rounded, size: 20),
            onPressed: () {},
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadAttendanceData,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.symmetric(horizontal: 20.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(height: 12),
              // 1. Calendar Widget Area
              Card(
                color: const Color(0xFF0A5C36),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(24),
                ),
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    children: [
                      // Month Selector Row
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          IconButton(
                            icon: const Icon(Icons.chevron_left_rounded, color: Colors.white, size: 28),
                            onPressed: () {
                              setState(() {
                                _selectedDay = _selectedDay.subtract(const Duration(days: 30));
                                _generateWeekDays();
                              });
                            },
                          ),
                          Text(
                            formattedMonth,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          IconButton(
                            icon: const Icon(Icons.chevron_right_rounded, color: Colors.white, size: 28),
                            onPressed: () {
                              setState(() {
                                _selectedDay = _selectedDay.add(const Duration(days: 30));
                                _generateWeekDays();
                              });
                            },
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      // Weekdays list
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: _weekDays.map((date) {
                          bool isToday = DateFormat('yyyyMMdd').format(date) == DateFormat('yyyyMMdd').format(DateTime.now());
                          bool isSelected = DateFormat('yyyyMMdd').format(date) == DateFormat('yyyyMMdd').format(_selectedDay);

                          return InkWell(
                            onTap: () {
                              setState(() {
                                _selectedDay = date;
                              });
                            },
                            child: Column(
                              children: [
                                Text(
                                  DateFormat('E').format(date),
                                  style: TextStyle(
                                    color: isToday ? const Color(0xFF86EFAC) : Colors.white70,
                                    fontSize: 12,
                                    fontWeight: isToday ? FontWeight.bold : FontWeight.normal,
                                  ),
                                ),
                                const SizedBox(height: 8),
                                Container(
                                  width: 36,
                                  height: 36,
                                  decoration: BoxDecoration(
                                    color: isToday
                                        ? Colors.white
                                        : isSelected
                                            ? Colors.white24
                                            : Colors.transparent,
                                    shape: BoxShape.circle,
                                  ),
                                  child: Center(
                                    child: Text(
                                      date.day.toString(),
                                      style: TextStyle(
                                        color: isToday
                                            ? const Color(0xFF0A5C36)
                                            : Colors.white,
                                        fontWeight: FontWeight.bold,
                                        fontSize: 14,
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          );
                        }).toList(),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 20),

              // 2. Today's details Card
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(18.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Today, ${DateFormat('d MMMM yyyy').format(DateTime.now())}',
                                style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                              ),
                              Text(
                                'Daily work summary details',
                                style: TextStyle(fontSize: 11, color: Colors.grey.shade400),
                              ),
                            ],
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: _attendanceData['checked_in'] == true ? const Color(0xFFE6F4EA) : const Color(0xFFFCE8E6),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              _attendanceData['checked_in'] == true ? (_attendanceData['status'] ?? 'Present') : 'Absent',
                              style: TextStyle(
                                color: _attendanceData['checked_in'] == true ? const Color(0xFF137333) : const Color(0xFFC5221F),
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 20),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          _buildDetailStatItem(
                            Icons.check_circle_outline_rounded,
                            Colors.green,
                            'Check In',
                            _attendanceData['check_in_time'] ?? '08:45 AM',
                          ),
                          _buildDetailStatItem(
                            Icons.highlight_off_rounded,
                            Colors.red,
                            'Check Out',
                            _attendanceData['check_out_time'] ?? '--:--',
                          ),
                          _buildDetailStatItem(
                            Icons.access_time_rounded,
                            Colors.blue,
                            'Total Working',
                            _attendanceData['total_working'] ?? '--:--',
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 24),

              // 3. Attendance History List
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    "Attendance History",
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                  ),
                  const SizedBox(height: 12),
                  ListView.separated(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: _attendanceHistory.length,
                    separatorBuilder: (context, index) => const SizedBox(height: 10),
                    itemBuilder: (context, index) {
                      final item = _attendanceHistory[index];
                      bool isPresent = item['status'] == 'Present' || item['status'] == 'Late';

                      return Container(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: const Color(0xFFE2E8F0), width: 1),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              item['date'] ?? 'Date',
                              style: const TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF334155)),
                            ),
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: isPresent ? const Color(0xFFE6F4EA) : const Color(0xFFFCE8E6),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Text(
                                    item['status'] ?? 'Present',
                                    style: TextStyle(
                                      color: isPresent ? const Color(0xFF137333) : const Color(0xFFC5221F),
                                      fontSize: 10,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 14),
                                Text(
                                  isPresent ? (item['check_in'] ?? '--:--') : '--:--',
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    color: isPresent ? Colors.grey.shade700 : Colors.grey.shade400,
                                    fontSize: 13,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      );
                    },
                  ),
                  const SizedBox(height: 16),
                  Center(
                    child: TextButton.icon(
                      onPressed: () {},
                      icon: const Icon(Icons.arrow_forward_rounded, size: 16, color: Color(0xFF0A5C36)),
                      label: const Text(
                        'View Full History',
                        style: TextStyle(color: Color(0xFF0A5C36), fontWeight: FontWeight.bold),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),

              // 4. Early Students list again to match mockup
              if (_earlyStudents.isNotEmpty) ...[
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      "Early Students Today",
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                    ),
                    TextButton(
                      onPressed: () {},
                      child: const Text('View All', style: TextStyle(color: Color(0xFF0A5C36), fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
                ListView.separated(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: _earlyStudents.length > 2 ? 2 : _earlyStudents.length,
                  separatorBuilder: (context, index) => const SizedBox(height: 10),
                  itemBuilder: (context, index) {
                    final item = _earlyStudents[index];
                    return Card(
                      margin: EdgeInsets.zero,
                      child: Padding(
                        padding: const EdgeInsets.all(12.0),
                        child: Row(
                          children: [
                            CircleAvatar(
                              radius: 20,
                              backgroundColor: const Color(0xFFF1F5F9),
                              child: const Icon(Icons.person_rounded, color: Color(0xFF94A3B8)),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    item['name'] ?? 'Student',
                                    style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                                  ),
                                  Text(
                                    '${item['class']}  •  Arrival: ${item['arrival_time']}',
                                    style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                                  ),
                                ],
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(
                                color: const Color(0xFFE6F4EA),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: const Text(
                                'Early',
                                style: TextStyle(color: Color(0xFF137333), fontSize: 10, fontWeight: FontWeight.bold),
                              ),
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
                const SizedBox(height: 24),
              ],

              // 5. Bottom Illustration & Dedication Message
              Container(
                margin: const EdgeInsets.only(top: 10, bottom: 40),
                child: Column(
                  children: [
                    // School Illustration Placeholder using Flutter Icons & Container
                    Container(
                      height: 120,
                      width: double.infinity,
                      decoration: BoxDecoration(
                        color: Colors.green.shade50,
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Stack(
                        alignment: Alignment.center,
                        children: [
                          Icon(Icons.school_rounded, size: 70, color: Colors.green.shade700),
                          Positioned(
                            bottom: 12,
                            child: Icon(Icons.nature_rounded, size: 36, color: Colors.green.shade800),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                    const Text(
                      'Thank you for your dedication!',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF0F5B3C),
                      ),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'Your presence shapes their tomorrow.',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDetailStatItem(IconData icon, Color color, String title, String val) {
    return Column(
      children: [
        Icon(icon, color: color, size: 24),
        const SizedBox(height: 8),
        Text(
          title,
          style: TextStyle(fontSize: 11, color: Colors.grey.shade400),
        ),
        const SizedBox(height: 2),
        Text(
          val,
          style: const TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.bold,
            color: Color(0xFF1E293B),
          ),
        ),
      ],
    );
  }
}
