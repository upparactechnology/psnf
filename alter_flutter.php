<?php
$file = 'staff_app/lib/screens/home_screen.dart';
$content = file_get_contents($file);

// 1. Rename variables
$content = str_replace('List<dynamic> _earlyStudents = [];', "List<dynamic> _attendanceList = [];\n  List<String> _classes = ['All Classes'];\n  String _selectedClass = 'All Classes';", $content);
$content = str_replace('_earlyStudentsKey', '_attendanceListKey', $content);
$content = str_replace('_scrollToEarlyStudents', '_scrollToAttendanceList', $content);

// 2. Update API call
$content = str_replace(
    "final earlyStudents = await ApiService.getEarlyStudents();",
    "final earlyStudents = await ApiService.getClassAttendance();",
    $content
);
$content = str_replace(
    "if (earlyStudents['success'] == true) {\n            _earlyStudents = earlyStudents['students'] ?? [];\n          }",
    "if (earlyStudents['success'] == true) {\n            _attendanceList = earlyStudents['students'] ?? [];\n            List<dynamic> fetchedClasses = earlyStudents['classes'] ?? [];\n            _classes = ['All Classes', ...fetchedClasses.map((e) => e.toString())];\n          }",
    $content
);

// 3. Update Quick Access icon
$content = str_replace("'Early Students'", "'Attendance Roster'", $content);

// 4. Update the UI section
$oldSectionStart = '// 4. Early Students Today';
$oldSectionEnd = '// 5. Today\'s Summary Row';

$newSection = <<<DART
// 4. Today's Attendance Roster
              Padding(
                key: _attendanceListKey,
                padding: const EdgeInsets.symmetric(horizontal: 20.0),
                child: Column(
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
                    Builder(
                      builder: (context) {
                        final filteredList = _selectedClass == 'All Classes' 
                            ? _attendanceList 
                            : _attendanceList.where((s) => s['class'] == _selectedClass).toList();
                            
                        if (filteredList.isEmpty) {
                          return Card(
                            child: Padding(
                              padding: const EdgeInsets.all(20.0),
                              child: Center(
                                child: Text(
                                  'No students found for this class.',
                                  style: TextStyle(color: Colors.grey.shade400),
                                ),
                              ),
                            ),
                          );
                        }
                        
                        return ListView.separated(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: filteredList.length,
                          separatorBuilder: (context, index) => const SizedBox(height: 10),
                          itemBuilder: (context, index) {
                            final item = filteredList[index];
                            final status = item['status'] ?? 'pending';
                            
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
                                          const SizedBox(height: 2),
                                          Text(
                                            '\${item['class']}',
                                            style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
                                          ),
                                        ],
                                      ),
                                    ),
                                    Column(
                                      crossAxisAlignment: CrossAxisAlignment.end,
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                          decoration: BoxDecoration(
                                            color: statusBg,
                                            borderRadius: BorderRadius.circular(12),
                                          ),
                                          child: Text(
                                            statusText,
                                            style: TextStyle(color: statusColor, fontSize: 11, fontWeight: FontWeight.bold),
                                          ),
                                        ),
                                        const SizedBox(height: 6),
                                        Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            InkWell(
                                              onTap: () async {
                                                await ApiService.markStudentAttendance(item['id'], 'present');
                                                _loadDashboardData();
                                              },
                                              child: Container(
                                                padding: const EdgeInsets.all(4),
                                                decoration: BoxDecoration(color: Colors.green.shade50, borderRadius: BorderRadius.circular(4)),
                                                child: const Icon(Icons.check, size: 14, color: Colors.green),
                                              ),
                                            ),
                                            const SizedBox(width: 6),
                                            InkWell(
                                              onTap: () async {
                                                await ApiService.markStudentAttendance(item['id'], 'absent');
                                                _loadDashboardData();
                                              },
                                              child: Container(
                                                padding: const EdgeInsets.all(4),
                                                decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(4)),
                                                child: const Icon(Icons.close, size: 14, color: Colors.red),
                                              ),
                                            ),
                                          ],
                                        )
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        );
                      }
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              // 5. Today's Summary Row
DART;

$pattern = '/' . preg_quote($oldSectionStart, '/') . '.*?' . preg_quote($oldSectionEnd, '/') . '/s';
$content = preg_replace($pattern, $newSection, $content);

file_put_contents($file, $content);
echo "home_screen.dart updated successfully.";
