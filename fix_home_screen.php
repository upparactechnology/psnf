<?php
$f = 'staff_app/lib/screens/home_screen.dart';
$c = file_get_contents($f);

// 1. Add import for class_attendance_screen.dart
$c = preg_replace('/import \'guardian_directory_screen\.dart\';/', "import 'guardian_directory_screen.dart';\nimport 'class_attendance_screen.dart';", $c);

// 2. Change the Quick Access item to push to the new screen
$c = preg_replace('/_buildQuickAccessItem\(Icons\.nature_people_rounded, Colors\.orange\.shade600, \'Attendance Roster\', \(\) \{\s*_scrollToAttendanceList\(\);\s*\}\)/', "_buildQuickAccessItem(Icons.nature_people_rounded, Colors.orange.shade600, 'Attendance Roster', () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(builder: (context) => const ClassAttendanceScreen()),
                          );
                        })", $c);

// 3. Remove the _scrollToAttendanceList function
$c = preg_replace('/void _scrollToAttendanceList\(\) \{.*?\}/s', '', $c);

// 4. Remove the Roster section from the build method
// It starts with "// 4. Today's Attendance Roster"
// And ends right before "// 5. Today's Summary Row"
$c = preg_replace('/\/\/ 4\. Today\'s Attendance Roster.*?(\/\/ 5\. Today\'s Summary Row)/s', '$1', $c);

// 5. Remove _attendanceListKey
$c = preg_replace('/final _attendanceListKey = GlobalKey\(\);/', '', $c);

file_put_contents($f, $c);
echo "Updated home_screen.dart\n";
