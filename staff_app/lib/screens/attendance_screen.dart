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
    'status': 'Not Checked In',
    'face_attendance_time': null,
    'is_late': false,
    'late_minutes': 0,
    'warning_message': null,
  };

  List<dynamic> _attendanceHistory = [];
  late List<DateTime> _weekDays;
  late DateTime _selectedDay;
  
  // History filters selection
  String _selectedFilter = 'All';
  String _selectedMonthFilter = 'All Months';

  @override
  void initState() {
    super.initState();
    _selectedDay = DateTime.now();
    _generateWeekDays();
    _loadAttendanceData();
  }

  void _generateWeekDays() {
    _weekDays = List.generate(7, (index) {
      return _selectedDay.subtract(Duration(days: 3 - index));
    });
  }

  Future<void> _loadAttendanceData() async {
    setState(() => _isLoading = true);
    try {
      final results = await Future.wait([
        ApiService.getTodayAttendance(),
        ApiService.getAttendanceHistory(),
      ]);
      final attendance = results[0];
      final history = results[1];

      if (mounted) {
        setState(() {
          if (attendance['success'] == true) {
            _attendanceData = attendance;
          }
          if (history['success'] == true) {
            _attendanceHistory = history['history'] ?? [];
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

  Map<String, dynamic> _getSelectedDayDetails() {
    final selectedDayStr = DateFormat('yyyy-MM-dd').format(_selectedDay);
    final todayStr = DateFormat('yyyy-MM-dd').format(DateTime.now());
    
    if (selectedDayStr == todayStr) {
      return {
        'checked_in': _attendanceData['checked_in'] ?? false,
        'check_in_time': _attendanceData['check_in_time'],
        'check_out_time': _attendanceData['check_out_time'],
        'total_working': _attendanceData['total_working'] ?? '--:--',
        'status': _attendanceData['status'] ?? 'Not Checked In',
        'warning_message': _attendanceData['warning_message'],
        'face_attendance_time': _attendanceData['face_attendance_time'],
      };
    }
    
    final record = _attendanceHistory.firstWhere(
      (h) => h['raw_date'] == selectedDayStr,
      orElse: () => null,
    );
    
    if (record != null) {
      bool isPresent = record['status'] == 'Present' || record['status'] == 'Late';
      return {
        'checked_in': isPresent,
        'check_in_time': record['check_in'] == '--:--' ? null : record['check_in'],
        'check_out_time': record['check_out'] == '--:--' ? null : record['check_out'],
        'total_working': record['total_working'] ?? '--:--',
        'status': record['status'] ?? 'Absent',
        'warning_message': record['status'] == 'Late' ? 'Late Clock-In recorded on this day.' : null,
        'face_attendance_time': isPresent ? 'Face Verified on Clock-In' : null,
      };
    }
    
    if (_selectedDay.weekday == DateTime.saturday || _selectedDay.weekday == DateTime.sunday) {
      return {
        'checked_in': false,
        'check_in_time': null,
        'check_out_time': null,
        'total_working': '--:--',
        'status': 'Weekend',
        'warning_message': null,
        'face_attendance_time': null,
      };
    }

    return {
      'checked_in': false,
      'check_in_time': null,
      'check_out_time': null,
      'total_working': '--:--',
      'status': 'Absent',
      'warning_message': null,
      'face_attendance_time': null,
    };
  }

  // Calculate statistics from attendance history
  Map<String, dynamic> _getStats() {
    int total = _attendanceHistory.length;
    if (total == 0) {
      return {'present': 0, 'late': 0, 'absent': 0, 'rate': '0%'};
    }
    int present = 0;
    int late = 0;
    int absent = 0;
    
    for (var item in _attendanceHistory) {
      final status = (item['status'] ?? '').toString().toLowerCase();
      if (status.contains('present')) {
        present++;
      } else if (status.contains('late')) {
        late++;
      } else if (status.contains('absent')) {
        absent++;
      }
    }
    
    double rate = ((present + late) / total) * 100;
    
    return {
      'present': present,
      'late': late,
      'absent': absent,
      'rate': '${rate.toStringAsFixed(0)}%'
    };
  }

  // Generate dynamic, chronologically-sorted month-year options from raw dates
  List<String> _getMonthsList() {
    final Set<String> months = {};
    for (var item in _attendanceHistory) {
      if (item['raw_date'] != null) {
        try {
          final date = DateTime.parse(item['raw_date']);
          final key = DateFormat('yyyy-MM').format(date);
          months.add(key);
        } catch (e) {}
      }
    }
    
    final sortedKeys = months.toList();
    sortedKeys.sort((a, b) => b.compareTo(a)); // Descending order (newest first)
    
    final List<String> list = ['All Months'];
    for (var key in sortedKeys) {
      final parts = key.split('-');
      final year = int.parse(parts[0]);
      final month = int.parse(parts[1]);
      final formatted = DateFormat('MMMM yyyy').format(DateTime(year, month));
      list.add(formatted);
    }
    return list;
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final String formattedMonth = DateFormat('MMMM yyyy').format(_selectedDay);
    final selectedDetails = _getSelectedDayDetails();
    final stats = _getStats();
    
    // Ensure selected month filter exists in current months list (fail-safe)
    final availableMonths = _getMonthsList();
    if (!availableMonths.contains(_selectedMonthFilter)) {
      _selectedMonthFilter = 'All Months';
    }

    // Filter history based on status AND month-year selection
    final filteredHistory = _attendanceHistory.where((item) {
      // 1. Status Filter
      bool statusMatches = true;
      if (_selectedFilter != 'All') {
        final status = (item['status'] ?? '').toString().toLowerCase();
        statusMatches = status.contains(_selectedFilter.toLowerCase());
      }
      
      // 2. Month/Year Filter
      bool monthMatches = true;
      if (_selectedMonthFilter != 'All Months' && item['raw_date'] != null) {
        try {
          final date = DateTime.parse(item['raw_date']);
          final formatted = DateFormat('MMMM yyyy').format(date);
          monthMatches = formatted == _selectedMonthFilter;
        } catch (e) {
          monthMatches = false;
        }
      }
      
      return statusMatches && monthMatches;
    }).toList();

    if (_isLoading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Attendance', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        backgroundColor: const Color(0xFF0A5C36),
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: RefreshIndicator(
        onRefresh: _loadAttendanceData,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(20.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
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
                                _selectedDay = _selectedDay.subtract(const Duration(days: 7));
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
                                _selectedDay = _selectedDay.add(const Duration(days: 7));
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
              const SizedBox(height: 16),

              // 2. Attendance Summary / Statistics Row
              Card(
                margin: EdgeInsets.zero,
                elevation: 0,
                color: Colors.grey.shade50,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                  side: BorderSide(color: Colors.grey.shade200),
                ),
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      _buildSummaryStat("Present", stats['present'].toString(), Colors.green),
                      _buildSummaryStat("Late", stats['late'].toString(), Colors.amber.shade700),
                      _buildSummaryStat("Absent", stats['absent'].toString(), Colors.red),
                      _buildSummaryStat("Rate", stats['rate'], const Color(0xFF0A5C36)),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),

              // Late Clock-In Warning Banner for Selected Day
              if (selectedDetails['warning_message'] != null)
                Container(
                  margin: const EdgeInsets.only(bottom: 16),
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFEF3D6),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFFF59E0B), width: 1),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.warning_amber_rounded, color: Color(0xFFF59E0B), size: 24),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          selectedDetails['warning_message'],
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: Color(0xFF92400E),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

              // 3. Selected Day's details Card
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(18.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      if (selectedDetails['face_attendance_time'] != null)
                        Container(
                          margin: const EdgeInsets.only(bottom: 14),
                          padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 12),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF0FFF4),
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: const Color(0xFF86EFAC)),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.face_retouching_natural, color: Color(0xFF0A5C36), size: 18),
                              const SizedBox(width: 8),
                              Text(
                                selectedDetails['face_attendance_time'],
                                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF0A5C36)),
                              ),
                            ],
                          ),
                        ),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                DateFormat('EEEE, d MMMM yyyy').format(_selectedDay),
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
                              color: selectedDetails['status'] == 'Present' || selectedDetails['status'] == 'Late'
                                  ? const Color(0xFFE6F4EA)
                                  : selectedDetails['status'] == 'Weekend'
                                      ? const Color(0xFFE8F0FE)
                                      : const Color(0xFFFCE8E6),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              selectedDetails['status'] ?? 'Absent',
                              style: TextStyle(
                                color: selectedDetails['status'] == 'Present' || selectedDetails['status'] == 'Late'
                                    ? const Color(0xFF137333)
                                    : selectedDetails['status'] == 'Weekend'
                                        ? const Color(0xFF1A73E8)
                                        : const Color(0xFFC5221F),
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
                            selectedDetails['check_in_time'] ?? '--:--',
                          ),
                          _buildDetailStatItem(
                            Icons.highlight_off_rounded,
                            Colors.red,
                            'Check Out',
                            selectedDetails['check_out_time'] ?? '--:--',
                          ),
                          _buildDetailStatItem(
                            Icons.access_time_rounded,
                            Colors.blue,
                            'Total Working',
                            selectedDetails['total_working'] ?? '--:--',
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 24),

              // 4. Attendance History Section
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        "History Logs",
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                      ),
                      
                      Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          // Month/Year Filter Dropdown
                          DropdownButton<String>(
                            value: _selectedMonthFilter,
                            icon: const Icon(Icons.calendar_month_outlined, size: 14, color: Color(0xFF0A5C36)),
                            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF0A5C36)),
                            underline: Container(height: 0),
                            onChanged: (String? val) {
                              if (val != null) {
                                setState(() {
                                  _selectedMonthFilter = val;
                                });
                              }
                            },
                            items: availableMonths.map((m) {
                              return DropdownMenuItem<String>(
                                value: m,
                                child: Text(m),
                              );
                            }).toList(),
                          ),
                          const SizedBox(width: 8),
                          // Status Filter Dropdown
                          DropdownButton<String>(
                            value: _selectedFilter,
                            icon: const Icon(Icons.filter_list_rounded, size: 18, color: Color(0xFF0A5C36)),
                            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF0A5C36)),
                            underline: Container(height: 0),
                            onChanged: (String? val) {
                              if (val != null) {
                                setState(() {
                                  _selectedFilter = val;
                                });
                              }
                            },
                            items: ['All', 'Present', 'Late', 'Absent'].map((filter) {
                              return DropdownMenuItem<String>(
                                value: filter,
                                child: Text(filter),
                              );
                            }).toList(),
                          ),
                        ],
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  
                  if (filteredHistory.isEmpty)
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(24.0),
                        child: Center(
                          child: Text(
                            "No records found for the selected filters.",
                            style: TextStyle(color: Colors.grey.shade400, fontSize: 13),
                          ),
                        ),
                      ),
                    )
                  else
                    ListView.separated(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: filteredHistory.length,
                      separatorBuilder: (context, index) => const SizedBox(height: 10),
                      itemBuilder: (context, index) {
                        final item = filteredHistory[index];
                        bool isPresent = item['status'] == 'Present' || item['status'] == 'Late';

                        return InkWell(
                          onTap: () {
                            setState(() {
                              _selectedDay = DateTime.parse(item['raw_date']);
                              _generateWeekDays();
                            });
                          },
                          child: Container(
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
                                        color: isPresent
                                            ? (item['status'] == 'Late' ? const Color(0xFFFFF3CD) : const Color(0xFFE6F4EA))
                                            : const Color(0xFFFCE8E6),
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: Text(
                                        item['status'] ?? 'Present',
                                        style: TextStyle(
                                          color: isPresent
                                              ? (item['status'] == 'Late' ? const Color(0xFF856404) : const Color(0xFF137333))
                                              : const Color(0xFFC5221F),
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
                          ),
                        );
                      },
                    ),
                ],
              ),
              const SizedBox(height: 40),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSummaryStat(String title, String value, Color color) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(
          value,
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          title,
          style: TextStyle(
            fontSize: 10,
            fontWeight: FontWeight.w500,
            color: Colors.grey.shade500,
          ),
        ),
      ],
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
