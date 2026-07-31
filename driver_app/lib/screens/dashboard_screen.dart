import 'package:flutter/material.dart';
import '../theme/colors.dart';
import '../services/api_service.dart';
import '../main.dart';
import 'pickup_route_screen.dart';
import 'drop_route_screen.dart';
import 'speed_monitor_screen.dart';
import 'trip_logs_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  int _currentIndex = 0;
  bool _isOnline = true;

  @override
  Widget build(BuildContext context) {
    final bool isDark = Theme.of(context).brightness == Brightness.dark;
    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      body: _buildCurrentTab(),
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          border: Border(
            top: BorderSide(
              color: isDark ? AppColors.slate.shade850 : const Color(0xFFE2E8F0), 
              width: 1,
            ),
          ),
        ),
        child: BottomNavigationBar(
          currentIndex: _currentIndex,
          onTap: (index) {
            setState(() {
              _currentIndex = index;
            });
          },
          backgroundColor: isDark ? const Color(0xFF0F172A) : Colors.white,
          type: BottomNavigationBarType.fixed,
          selectedItemColor: const Color(0xFF6366F1),
          unselectedItemColor: isDark ? AppColors.slate.shade500 : const Color(0xFF94A3B8),
          selectedLabelStyle: const TextStyle(fontWeight: FontWeight.w600, fontSize: 11),
          unselectedLabelStyle: const TextStyle(fontSize: 11),
          items: const [
            BottomNavigationBarItem(
              icon: Icon(Icons.dashboard_rounded),
              label: 'Dashboard',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.speed_rounded),
              label: 'Speed',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.history_rounded),
              label: 'Trips',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.person_rounded),
              label: 'Profile',
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCurrentTab() {
    switch (_currentIndex) {
      case 0:
        return _buildHomeTab();
      case 1:
        return const SpeedMonitorScreen();
      case 2:
        return const TripLogsScreen();
      case 3:
        return _buildProfileTab();
      default:
        return _buildHomeTab();
    }
  }

  Widget _buildHomeTab() {
    final bool isDark = Theme.of(context).brightness == Brightness.dark;
    final Color mainText = isDark ? Colors.white : const Color(0xFF0F172A);
    final Color subText = isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B);
    final Color cardBg = isDark ? const Color(0xFF1E293B).withOpacity(0.4) : Colors.white;
    final Color borderColor = isDark ? AppColors.slate.shade850 : const Color(0xFFE2E8F0);

    final routeData = ApiService.assignedRoute;
    final bool hasRoute = routeData != null;
    final String status = hasRoute ? (routeData['status'] ?? 'inactive') : 'inactive';
    final bool isEnRoute = status == 'en_route';
    final bool isCompleted = status == 'completed';
    
    // Compute dynamic stats
    int totalStudents = 0;
    int pickedUp = 0;
    int pending = 0;
    int onLeave = 0;
    
    if (hasRoute && routeData['students'] != null) {
      final studentsList = routeData['students'] as List;
      totalStudents = studentsList.length;
      for (var student in studentsList) {
        final stuStatus = student['status'] ?? student['trip_status'] ?? 'Waiting';
        if (stuStatus == 'Picked Up' || stuStatus == 'Dropped') {
           pickedUp++;
        } else if (stuStatus == 'Absent') {
           onLeave++;
        } else {
           pending++;
        }
      }
      if (isCompleted) {
          pickedUp = totalStudents;
          pending = 0;
      }
    }

    return SafeArea(
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(20.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // User Header Row
            Row(
              children: [
                CircleAvatar(
                  radius: 26,
                  backgroundColor: const Color(0xFF4F46E5).withOpacity(0.2),
                  child: Text(
                    ApiService.driverName != null && ApiService.driverName!.isNotEmpty 
                        ? ApiService.driverName!.substring(0, 1).toUpperCase() 
                        : 'D',
                    style: const TextStyle(
                      color: Color(0xFF818CF8),
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        () {
                          final hour = DateTime.now().hour;
                          if (hour < 12) return 'Good Morning 🌅';
                          if (hour < 17) return 'Good Afternoon ☀️';
                          return 'Good Evening 🌙';
                        }(),
                        style: TextStyle(
                          color: subText,
                          fontSize: 14,
                        ),
                      ),
                      Text(
                        ApiService.driverName ?? 'Driver',
                        style: TextStyle(
                          color: mainText,
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
                // Custom toggle button for Online/Offline
                GestureDetector(
                  onTap: () {
                    setState(() {
                      _isOnline = !_isOnline;
                    });
                  },
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                    decoration: BoxDecoration(
                      color: _isOnline 
                          ? const Color(0xFF10B981).withOpacity(0.1) 
                          : (isDark ? AppColors.slate.shade850 : const Color(0xFFE2E8F0)),
                      borderRadius: BorderRadius.circular(100),
                      border: Border.all(
                        color: _isOnline 
                            ? const Color(0xFF10B981).withOpacity(0.3) 
                            : (isDark ? AppColors.slate.shade800 : const Color(0xFFCBD5E1)),
                      ),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        CircleAvatar(
                          radius: 4,
                          backgroundColor: _isOnline ? const Color(0xFF10B981) : AppColors.slate.shade500,
                        ),
                        const SizedBox(width: 6),
                        Text(
                          _isOnline ? 'Online' : 'Offline',
                          style: TextStyle(
                            color: _isOnline ? const Color(0xFF34D399) : subText,
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),

            // Stats grid (2x2)
            GridView.count(
              crossAxisCount: 2,
              crossAxisSpacing: 16,
              mainAxisSpacing: 16,
              childAspectRatio: 1.45,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              children: [
                _buildStatCard('Total Students', totalStudents.toString(), Icons.people_rounded, const Color(0xFF6366F1)),
                _buildStatCard('Picked Up', pickedUp.toString(), Icons.check_circle_outline_rounded, const Color(0xFF10B981)),
                _buildStatCard('Pending', pending.toString(), Icons.pending_actions_rounded, const Color(0xFFF59E0B)),
                _buildStatCard('On Leave', onLeave.toString(), Icons.airline_seat_recline_normal_rounded, const Color(0xFFEF4444)),
              ],
            ),
            const SizedBox(height: 28),

            // Today's Routes Header
            Row(
              children: [
                Text(
                  "Assigned Trip",
                  style: TextStyle(
                    color: mainText,
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            if (hasRoute && !isCompleted) ...[
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: isDark 
                        ? [const Color(0xFF1E293B).withOpacity(0.8), const Color(0xFF0F172A)]
                        : [Colors.white, const Color(0xFFF1F5F9)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: borderColor),
                ),
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
                              isEnRoute ? 'Current Trip (Active)' : 'Next Shift',
                              style: TextStyle(
                                color: subText,
                                fontSize: 12,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'Assigned Route',
                              style: TextStyle(
                                color: mainText,
                                fontSize: 18,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: isEnRoute ? const Color(0xFF10B981).withOpacity(0.1) : const Color(0xFFF59E0B).withOpacity(0.1),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            isEnRoute ? 'EN ROUTE' : 'READY',
                            style: TextStyle(
                              color: isEnRoute ? const Color(0xFF10B981) : const Color(0xFFF59E0B),
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        )
                      ],
                    ),
                    const SizedBox(height: 20),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        _routeInfoTile(totalStudents.toString(), 'Total Stops'),
                        _routeInfoTile(pending.toString(), 'Remaining'),
                      ],
                    ),
                    const SizedBox(height: 24),
                    ElevatedButton(
                      onPressed: () async {
                        await Navigator.push(
                          context,
                          MaterialPageRoute(builder: (context) => const PickupRouteScreen()),
                        );
                        setState(() {});
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF6366F1),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 16),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                        elevation: 4,
                      ),
                      child: Text(
                        isEnRoute ? 'Resume Trip' : 'Start Trip',
                        style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    )
                  ],
                ),
              ),
              const SizedBox(height: 16),
            ] else if (isCompleted) ...[
              Container(
                padding: const EdgeInsets.symmetric(vertical: 32, horizontal: 20),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF1E293B).withOpacity(0.2) : Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: borderColor, style: BorderStyle.solid),
                ),
                child: Column(
                  children: [
                    const Icon(Icons.done_all_rounded, color: Color(0xFF10B981), size: 48),
                    const SizedBox(height: 12),
                    Text(
                      'Trip Completed',
                      style: TextStyle(
                        color: mainText,
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'You have completed your assigned trip.',
                      style: TextStyle(
                        color: subText,
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ),
            ] else ...[
              Container(
                padding: const EdgeInsets.symmetric(vertical: 32, horizontal: 20),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF1E293B).withOpacity(0.2) : Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: borderColor, style: BorderStyle.solid),
                ),
                child: Column(
                  children: [
                    const Icon(Icons.route_outlined, color: Color(0xFF64748B), size: 48),
                    const SizedBox(height: 12),
                    Text(
                      'No Active Assignment',
                      style: TextStyle(
                        color: mainText,
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'You are not currently assigned to any active route.',
                      style: TextStyle(
                        color: subText,
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard(String label, String value, IconData icon, Color color) {
    final bool isDark = Theme.of(context).brightness == Brightness.dark;
    final Color textColor = isDark ? Colors.white : const Color(0xFF0F172A);
    final Color subTextColor = isDark ? AppColors.slate.shade400 : const Color(0xFF64748B);

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Theme.of(context).brightness == Brightness.dark ? const Color(0xFF1E293B).withOpacity(0.3) : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Theme.of(context).brightness == Brightness.dark ? AppColors.slate.shade850 : const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                value,
                style: TextStyle(
                  color: textColor,
                  fontSize: 26,
                  fontWeight: FontWeight.bold,
                ),
              ),
              Icon(icon, color: color.withOpacity(0.8), size: 20),
            ],
          ),
          const Spacer(),
          Text(
            label,
            style: TextStyle(
              color: subTextColor,
              fontSize: 12,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  Widget _routeInfoTile(String value, String title) {
    final bool isDark = Theme.of(context).brightness == Brightness.dark;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          value,
          style: TextStyle(
            color: isDark ? Colors.white : const Color(0xFF0F172A),
            fontSize: 20,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          title,
          style: TextStyle(
            color: isDark ? AppColors.slate.shade500 : const Color(0xFF64748B),
            fontSize: 12,
          ),
        ),
      ],
    );
  }

  Widget _routeInfoTileCompleted(String value, String title) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          value,
          style: const TextStyle(
            color: Color(0xFF64748B),
            fontSize: 20,
            fontWeight: FontWeight.bold,
            decoration: TextDecoration.lineThrough,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          title,
          style: const TextStyle(
            color: Color(0xFF475569),
            fontSize: 12,
          ),
        ),
      ],
    );
  }

  Widget _buildProfileTab() {
    final bool isDark = Theme.of(context).brightness == Brightness.dark;
    final Color mainText = isDark ? Colors.white : const Color(0xFF0F172A);
    final Color subText = isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B);

    return SafeArea(
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          children: [
            const SizedBox(height: 20),
            CircleAvatar(
              radius: 50,
              backgroundColor: const Color(0xFF6366F1).withOpacity(0.15),
              child: const Icon(
                Icons.directions_bus_filled_rounded,
                color: Color(0xFF6366F1),
                size: 48,
              ),
            ),
            const SizedBox(height: 18),
            Text(
              ApiService.assignedRoute != null ? ApiService.assignedRoute!['driver'] : 'John Driver',
              style: TextStyle(
                color: mainText,
                fontSize: 22,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              ApiService.assignedRoute != null ? ApiService.assignedRoute!['phone'] ?? 'john.driver@psnf.edu' : 'john.driver@psnf.edu',
              style: TextStyle(
                color: subText,
                fontSize: 14,
              ),
            ),
            const SizedBox(height: 32),
            _profileTile(
              Icons.history_rounded, 
              'Trip Logs',
              onTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (context) => const TripLogsScreen()),
                );
              },
            ),
            _profileTile(
              isDark ? Icons.dark_mode_rounded : Icons.light_mode_rounded,
              'App Theme',
              onTap: () {
                DriverApp.themeNotifier.value =
                    DriverApp.themeNotifier.value == ThemeMode.dark
                        ? ThemeMode.light
                        : ThemeMode.dark;
                setState(() {});
              },
              trailing: Switch(
                value: DriverApp.themeNotifier.value == ThemeMode.dark,
                onChanged: (val) {
                  DriverApp.themeNotifier.value =
                      val ? ThemeMode.dark : ThemeMode.light;
                  setState(() {});
                },
                activeColor: const Color(0xFF6366F1),
              ),
            ),
            const SizedBox(height: 24),
            ListTile(
              onTap: () {
                Navigator.pushReplacementNamed(context, '/login');
              },
              leading: const Icon(Icons.logout_rounded, color: Colors.redAccent),
              title: const Text(
                'Log Out',
                style: TextStyle(color: Colors.redAccent, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _profileTile(IconData icon, String title, {VoidCallback? onTap, Widget? trailing}) {
    final bool isDark = Theme.of(context).brightness == Brightness.dark;
    final Color textColor = isDark ? Colors.white : const Color(0xFF0F172A);
    final Color borderC = isDark ? AppColors.slate.shade850 : const Color(0xFFE2E8F0);

    return Card(
      color: isDark ? const Color(0xFF1E293B).withOpacity(0.3) : Colors.white,
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(color: borderC),
      ),
      child: ListTile(
        onTap: onTap,
        leading: Icon(icon, color: const Color(0xFF818CF8)),
        title: Text(
          title, 
          style: TextStyle(
            color: textColor, 
            fontWeight: FontWeight.bold,
            fontSize: 14,
          ),
        ),
        trailing: trailing ?? Icon(Icons.chevron_right_rounded, color: isDark ? const Color(0xFF64748B) : const Color(0xFF94A3B8)),
      ),
    );
  }

  void _showPastTripsBottomSheet(BuildContext context) {
    final bool isDark = Theme.of(context).brightness == Brightness.dark;
    final Color textColor = isDark ? Colors.white : const Color(0xFF0F172A);
    final Color subTextColor = isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B);
    final Color sheetBg = isDark ? const Color(0xFF0F172A) : Colors.white;
    final Color cardBg = isDark ? const Color(0xFF1E293B).withOpacity(0.4) : const Color(0xFFF1F5F9);
    final Color borderC = isDark ? const Color(0xFF334155).withOpacity(0.5) : const Color(0xFFE2E8F0);

    showModalBottomSheet(
      context: context,
      backgroundColor: sheetBg,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Drag handle
                Center(
                  child: Container(
                    width: 48,
                    height: 5,
                    decoration: BoxDecoration(
                      color: const Color(0xFF334155),
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                ),
                const SizedBox(height: 24),
                Text(
                  'Past Trips Today',
                  style: TextStyle(
                    color: textColor,
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 20),
                if (ApiService.assignedRoute == null || ApiService.assignedRoute!["status"] != "completed")
                  Container(
                    padding: const EdgeInsets.symmetric(vertical: 40),
                    child: Column(
                      children: [
                        const Icon(
                          Icons.history_rounded,
                          color: Color(0xFF475569),
                          size: 48,
                        ),
                        const SizedBox(height: 12),
                        Text(
                          'No completed trips today',
                          style: TextStyle(
                            color: subTextColor,
                            fontSize: 14,
                          ),
                        ),
                      ],
                    ),
                  ),
                if (ApiService.assignedRoute != null && ApiService.assignedRoute!["status"] == "completed") ...[
                  Container(
                    padding: const EdgeInsets.all(18),
                    decoration: BoxDecoration(
                      color: cardBg,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: borderC),
                    ),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: const Color(0xFF6366F1).withOpacity(0.15),
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(
                            Icons.directions_bus_rounded,
                            color: Color(0xFF818CF8),
                            size: 24,
                          ),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Morning Shift',
                                style: TextStyle(
                                  color: subTextColor,
                                  fontSize: 12,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                'Pickup Route - 1',
                                style: TextStyle(
                                  color: textColor,
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 4),
                              const Text(
                                'Stops: 20  |  Completed at 08:35 AM',
                                style: TextStyle(
                                  color: Color(0xFF64748B),
                                  fontSize: 11,
                                ),
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: const Color(0xFF10B981).withOpacity(0.15),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: const Text(
                            'Completed',
                            style: TextStyle(
                              color: Color(0xFF34D399),
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),
                ],
                if (false) ...[
                  Container(
                    padding: const EdgeInsets.all(18),
                    decoration: BoxDecoration(
                      color: cardBg,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: borderC),
                    ),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: const Color(0xFFEC4899).withOpacity(0.15),
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(
                            Icons.directions_bus_rounded,
                            color: Color(0xFFF472B6),
                            size: 24,
                          ),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Evening Shift',
                                style: TextStyle(
                                  color: subTextColor,
                                  fontSize: 12,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                'Drop Route - 1',
                                style: TextStyle(
                                  color: textColor,
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 4),
                              const Text(
                                'Stops: 2  |  Completed at 04:32 PM',
                                style: TextStyle(
                                  color: Color(0xFF64748B),
                                  fontSize: 11,
                                ),
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: const Color(0xFF10B981).withOpacity(0.15),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: const Text(
                            'Completed',
                            style: TextStyle(
                              color: Color(0xFF34D399),
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),
                ],
                const SizedBox(height: 16),
              ],
            ),
          ),
        );
      },
    );
  }
}
