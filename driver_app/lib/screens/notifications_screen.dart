import 'package:flutter/material.dart';
import '../theme/colors.dart';
import '../models/notification.dart';
import 'early_leave_screen.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  String _selectedCategory = 'All';

  final List<NotificationItem> _notifications = [
    NotificationItem(
      title: 'Route Assigned',
      subtitle: 'Morning Route - 1',
      time: '07:15 AM',
      type: NotificationType.route,
    ),
    NotificationItem(
      title: 'Student Left School',
      subtitle: 'Anjali Patel is leaving early',
      time: '11:30 AM',
      type: NotificationType.alert,
    ),
    NotificationItem(
      title: 'Speed Warning',
      subtitle: 'Speed exceeded 60 km/h limit',
      time: '09:20 AM',
      type: NotificationType.speed,
    ),
    NotificationItem(
      title: 'Speed Breach',
      subtitle: '72 km/h velocity recorded',
      time: '09:22 AM',
      type: NotificationType.speed,
    ),
    NotificationItem(
      title: 'Route Updated',
      subtitle: 'Evening Route - 1 assigned',
      time: '12:10 PM',
      type: NotificationType.route,
    ),
    NotificationItem(
      title: 'System Update',
      subtitle: 'Offline maps sync completed',
      time: '01:00 PM',
      type: NotificationType.system,
    ),
  ];

  @override
  Widget build(BuildContext context) {
    final bool isDark = Theme.of(context).brightness == Brightness.dark;
    final Color textColor = isDark ? Colors.white : const Color(0xFF0F172A);
    final Color subTextColor = isDark ? AppColors.slate.shade400 : const Color(0xFF64748B);
    final Color chipBgColor = isDark ? const Color(0xFF1E293B).withOpacity(0.3) : const Color(0xFFF1F5F9);
    final Color borderColor = isDark ? AppColors.slate.shade850 : const Color(0xFFE2E8F0);

    // Filter list based on category tab
    final filteredNotifications = _notifications.where((n) {
      if (_selectedCategory == 'All') return true;
      if (_selectedCategory == 'Alerts' && n.type == NotificationType.alert) return true;
      if (_selectedCategory == 'Route' && n.type == NotificationType.route) return true;
      if (_selectedCategory == 'System' && n.type == NotificationType.system) return true;
      return false;
    }).toList();

    return SafeArea(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 10.0),
            child: Text(
              'Notifications',
              style: TextStyle(
                color: textColor,
                fontSize: 24,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),

          // Categories horizontal chips row
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
            child: Row(
              children: ['All', 'Alerts', 'Route', 'System'].map((cat) {
                bool isSelected = _selectedCategory == cat;
                return Padding(
                  padding: const EdgeInsets.only(right: 10.0),
                  child: ChoiceChip(
                    label: Text(cat),
                    selected: isSelected,
                    onSelected: (selected) {
                      setState(() {
                        _selectedCategory = cat;
                      });
                    },
                    backgroundColor: chipBgColor,
                    selectedColor: const Color(0xFF6366F1),
                    labelStyle: TextStyle(
                      color: isSelected ? Colors.white : subTextColor,
                      fontWeight: FontWeight.bold,
                    ),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                      side: BorderSide(color: isSelected ? Colors.transparent : borderColor),
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
          const SizedBox(height: 10),

          // Notifications List
          Expanded(
            child: filteredNotifications.isEmpty
                ? Center(
                    child: Text(
                      'No notifications in this category',
                      style: TextStyle(color: isDark ? AppColors.slate.shade500 : const Color(0xFF94A3B8), fontSize: 14),
                    ),
                  )
                : ListView.builder(
                    padding: const EdgeInsets.symmetric(horizontal: 16.0),
                    itemCount: filteredNotifications.length,
                    itemBuilder: (context, index) {
                      final item = filteredNotifications[index];
                      return _buildNotificationTile(context, item);
                    },
                  ),
          ),

          // Bottom View All button
          Padding(
            padding: const EdgeInsets.all(20.0),
            child: ElevatedButton(
              onPressed: () {
                // View all
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF6366F1),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
              ),
              child: const Text(
                'View All',
                style: TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildNotificationTile(BuildContext context, NotificationItem item) {
    final bool isDark = Theme.of(context).brightness == Brightness.dark;
    final Color textColor = isDark ? Colors.white : const Color(0xFF0F172A);
    final Color subTextColor = isDark ? AppColors.slate.shade400 : const Color(0xFF64748B);
    final Color cardBg = isDark ? const Color(0xFF1E293B).withOpacity(0.3) : Colors.white;
    final Color borderColor = isDark ? AppColors.slate.shade850 : const Color(0xFFE2E8F0);

    Color iconColor;
    IconData icon;
    
    switch (item.type) {
      case NotificationType.route:
        iconColor = const Color(0xFF10B981); // Green
        icon = Icons.directions_bus_rounded;
        break;
      case NotificationType.alert:
        iconColor = const Color(0xFFF59E0B); // Yellow
        icon = Icons.warning_amber_rounded;
        break;
      case NotificationType.speed:
        iconColor = const Color(0xFFEF4444); // Red
        icon = Icons.speed_rounded;
        break;
      case NotificationType.system:
      default:
        iconColor = const Color(0xFF3B82F6); // Blue
        icon = Icons.settings_suggest_rounded;
        break;
    }

    return Card(
      color: cardBg,
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(color: borderColor),
      ),
      child: ListTile(
        onTap: () {
          // If they tap the Anjali Patel alert, push the Early Leave warning screen!
          if (item.title.contains('Student Left School') || item.subtitle.contains('Anjali Patel')) {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (context) => const EarlyLeaveScreen()),
            );
          }
        },
        leading: Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: iconColor.withOpacity(0.1),
            shape: BoxShape.circle,
          ),
          child: Icon(icon, color: iconColor, size: 22),
        ),
        title: Text(
          item.title,
          style: TextStyle(
            color: textColor,
            fontSize: 14,
            fontWeight: FontWeight.bold,
          ),
        ),
        subtitle: Padding(
          padding: const EdgeInsets.only(top: 4.0),
          child: Text(
            item.subtitle,
            style: TextStyle(
              color: subTextColor,
              fontSize: 12,
            ),
          ),
        ),
        trailing: Text(
          item.time,
          style: TextStyle(
            color: isDark ? AppColors.slate.shade500 : const Color(0xFF94A3B8),
            fontSize: 11,
          ),
        ),
      ),
    );
  }
}
