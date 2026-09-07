import 'package:flutter/material.dart';
import '../services/api_service.dart';

class ClassAttendanceScreen extends StatefulWidget {
  const ClassAttendanceScreen({super.key});

  @override
  State<ClassAttendanceScreen> createState() => _ClassAttendanceScreenState();
}

class _ClassAttendanceScreenState extends State<ClassAttendanceScreen> {
  bool _isLoading = true;
  List<dynamic> _attendanceList = [];
  List<String> _classes = ['All Classes'];
  String _selectedClass = 'All Classes';
  
  // Track local changes mapping studentId to status ('present' or 'absent')
  final Map<int, String> _localChanges = {};

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      final earlyStudents = await ApiService.getClassAttendance();

      if (mounted) {
        setState(() {
          if (earlyStudents['success'] == true) {
            _attendanceList = earlyStudents['students'] ?? [];
            List<dynamic> fetchedClasses = earlyStudents['classes'] ?? [];
            _classes = ['All Classes', ...fetchedClasses.map((e) => e.toString())];
          }
          _localChanges.clear(); // Clear changes after loading fresh database state
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _saveChanges() async {
    if (_localChanges.isEmpty) return;
    setState(() => _isLoading = true);
    
    try {
      int successCount = 0;
      for (var entry in _localChanges.entries) {
        final res = await ApiService.markStudentAttendance(entry.key, entry.value);
        if (res['success'] == true) {
          successCount++;
        }
      }
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Successfully saved $successCount student attendance records!'),
            backgroundColor: Colors.green,
          ),
        );
      }
      _loadData(); // Re-fetch the database list
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error saving: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }

    final filteredList = _selectedClass == 'All Classes'
        ? _attendanceList
        : _attendanceList.where((s) => s['class'] == _selectedClass).toList();

    return Scaffold(
      appBar: AppBar(
        title: const Text("Class Attendance", style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        backgroundColor: const Color(0xFF0A5C36),
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: RefreshIndicator(
        onRefresh: _loadData,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(20.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    "Today's Attendance Roster",
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                  ),
                  if (_classes.length > 1)
                    DropdownButton<String>(
                      value: _selectedClass,
                      icon: const Icon(Icons.arrow_drop_down, color: Color(0xFF0A5C36)),
                      underline: Container(height: 1, color: const Color(0xFF0A5C36)),
                      onChanged: (String? newValue) {
                        if (newValue != null) {
                          setState(() {
                            _selectedClass = newValue;
                          });
                        }
                      },
                      items: _classes.map<DropdownMenuItem<String>>((String value) {
                        return DropdownMenuItem<String>(
                          value: value,
                          child: Text(value, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                        );
                      }).toList(),
                    ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: filteredList.isEmpty ? null : () {
                        setState(() {
                          for (var student in filteredList) {
                            _localChanges[student['id']] = 'present';
                          }
                        });
                      },
                      icon: const Icon(Icons.check_circle_outline_rounded, size: 16, color: Color(0xFF137333)),
                      label: const Text("Mark All Present", style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF137333))),
                      style: OutlinedButton.styleFrom(
                        side: const BorderSide(color: Color(0xFF137333)),
                        padding: const EdgeInsets.symmetric(vertical: 8),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: filteredList.isEmpty ? null : () {
                        setState(() {
                          for (var student in filteredList) {
                            _localChanges[student['id']] = 'absent';
                          }
                        });
                      },
                      icon: const Icon(Icons.cancel_outlined, size: 16, color: Color(0xFFC5221F)),
                      label: const Text("Mark All Absent", style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFFC5221F))),
                      style: OutlinedButton.styleFrom(
                        side: const BorderSide(color: Color(0xFFC5221F)),
                        padding: const EdgeInsets.symmetric(vertical: 8),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              if (filteredList.isEmpty)
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(20.0),
                    child: Center(
                      child: Text(
                        'No students found for this class.',
                        style: TextStyle(color: Colors.grey.shade400),
                      ),
                    ),
                  ),
                )
              else
                ListView.separated(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: filteredList.length,
                  separatorBuilder: (context, index) => const SizedBox(height: 10),
                  itemBuilder: (context, index) {
                    final item = filteredList[index];
                    
                    // Prioritize local changes over saved database status
                    final status = _localChanges[item['id']] ?? item['status'] ?? 'pending';

                    Color statusColor = Colors.grey;
                    Color statusBg = Colors.grey.shade100;
                    String statusText = 'Pending';

                    if (status == 'present') {
                      statusColor = const Color(0xFF137333);
                      statusBg = const Color(0xFFE6F4EA);
                      statusText = 'Present';
                    } else if (status == 'absent') {
                      statusColor = Colors.red.shade700;
                      statusBg = Colors.red.shade50;
                      statusText = 'Absent';
                    }

                    return Card(
                      margin: EdgeInsets.zero,
                      child: Padding(
                        padding: const EdgeInsets.all(12.0),
                        child: Row(
                          children: [
                            CircleAvatar(
                              radius: 22,
                              backgroundColor: const Color(0xFFF1F5F9),
                              child: const Icon(Icons.person_rounded, color: Color(0xFF94A3B8)),
                            ),
                            const SizedBox(width: 14),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    item['name'] ?? 'Student',
                                    style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                                  ),
                                  const SizedBox(height: 4),
                                  Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Flexible(
                                        child: Text(
                                          '${item['class']}',
                                          overflow: TextOverflow.ellipsis,
                                          style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                        decoration: BoxDecoration(
                                          color: statusBg,
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Text(
                                          statusText,
                                          style: TextStyle(color: statusColor, fontSize: 10, fontWeight: FontWeight.bold),
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                            Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                InkWell(
                                  onTap: () {
                                    setState(() {
                                      // Toggle or select present instantly
                                      _localChanges[item['id']] = 'present';
                                    });
                                  },
                                  borderRadius: BorderRadius.circular(8),
                                  child: Container(
                                    width: 44,
                                    height: 44,
                                    decoration: BoxDecoration(
                                      color: status == 'present' ? const Color(0xFF137333) : const Color(0xFFE6F4EA),
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Icon(Icons.check, color: status == 'present' ? Colors.white : const Color(0xFF137333), size: 22),
                                  ),
                                ),
                                const SizedBox(width: 10),
                                InkWell(
                                  onTap: () {
                                    setState(() {
                                      // Toggle or select absent instantly
                                      _localChanges[item['id']] = 'absent';
                                    });
                                  },
                                  borderRadius: BorderRadius.circular(8),
                                  child: Container(
                                    width: 44,
                                    height: 44,
                                    decoration: BoxDecoration(
                                      color: status == 'absent' ? const Color(0xFFC5221F) : const Color(0xFFFCE8E6),
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Icon(Icons.close, color: status == 'absent' ? Colors.white : const Color(0xFFC5221F), size: 22),
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
        ),
      ),
      bottomNavigationBar: _localChanges.isEmpty
          ? null
          : Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.05),
                    blurRadius: 10,
                    offset: const Offset(0, -5),
                  ),
                ],
              ),
              child: ElevatedButton(
                onPressed: _saveChanges,
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF0A5C36),
                  foregroundColor: Colors.white,
                  minimumSize: const Size(double.infinity, 48),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: Text(
                  "Save & Submit (${_localChanges.length} Changes)",
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                ),
              ),
            ),
    );
  }
}
