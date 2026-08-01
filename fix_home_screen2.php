<?php
$f = 'staff_app/lib/screens/home_screen.dart';
$c = file_get_contents($f);

// 1. Add import for class_attendance_screen.dart
$c = str_replace("import 'guardian_directory_screen.dart';", "import 'guardian_directory_screen.dart';\nimport 'class_attendance_screen.dart';", $c);

// 2. Change the Quick Access item to push to the new screen
$search = "_buildQuickAccessItem(Icons.nature_people_rounded, Colors.orange.shade600, 'Attendance Roster', () {
                          _scrollToAttendanceList();
                        }),";
$replace = "_buildQuickAccessItem(Icons.nature_people_rounded, Colors.orange.shade600, 'Attendance Roster', () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(builder: (context) => const ClassAttendanceScreen()),
                          );
                        }),";
$c = str_replace($search, $replace, $c);

// 3. Remove the _scrollToAttendanceList function completely
$c = preg_replace('/void _scrollToAttendanceList\(\) \{.*?\}/s', '', $c);

// 4. Remove the "4. Today's Attendance Roster" section up to "5. Today's Summary Row"
$c = preg_replace('/\/\/\s*4\.\s*Today\'s Attendance Roster.*?(?=\/\/\s*5\.\s*Today\'s Summary Row)/s', '', $c);

// 5. Remove _attendanceListKey
$c = str_replace("final _attendanceListKey = GlobalKey();", "", $c);

file_put_contents($f, $c);
echo "Updated home_screen.dart safely\n";
