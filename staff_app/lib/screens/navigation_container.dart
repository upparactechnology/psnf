import 'package:flutter/material.dart';
import 'home_screen.dart';
import 'attendance_screen.dart';
import 'class_attendance_screen.dart';
import 'students_screen.dart';
import 'profile_screen.dart';

class NavigationContainer extends StatefulWidget {
  const NavigationContainer({super.key});

  static final GlobalKey<NavigationContainerState> navKey = GlobalKey<NavigationContainerState>();

  @override
  State<NavigationContainer> createState() => NavigationContainerState();
}

class NavigationContainerState extends State<NavigationContainer> {
  int _selectedIndex = 0;

  void switchTab(int index) {
    setState(() {
      _selectedIndex = index;
    });
  }

  @override
  Widget build(BuildContext context) {
    final List<Widget> screens = [
      HomeScreen(onNavigateTab: switchTab),
      const ClassAttendanceScreen(),
      const AttendanceScreen(),
      const StudentsScreen(),
      const ProfileScreen(),
    ];

    return Scaffold(
      body: IndexedStack(
        index: _selectedIndex,
        children: screens,
      ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _selectedIndex,
        onTap: switchTab,
        type: BottomNavigationBarType.fixed,
        selectedItemColor: const Color(0xFF0A5C36),
        unselectedItemColor: Colors.grey,
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.home_outlined),
            activeIcon: Icon(Icons.home_rounded),
            label: 'Home',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.fact_check_outlined),
            activeIcon: Icon(Icons.fact_check_rounded),
            label: 'Class Att.',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.calendar_month_outlined),
            activeIcon: Icon(Icons.calendar_month_rounded),
            label: 'My Att.',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.school_outlined),
            activeIcon: Icon(Icons.school_rounded),
            label: 'Students',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.person_outline),
            activeIcon: Icon(Icons.person_rounded),
            label: 'Profile',
          ),
        ],
      ),
    );
  }
}
