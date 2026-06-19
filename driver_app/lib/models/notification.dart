enum NotificationType { route, speed, alert, system }

class NotificationItem {
  final String title;
  final String subtitle;
  final String time;
  final NotificationType type;

  NotificationItem({
    required this.title,
    required this.subtitle,
    required this.time,
    required this.type,
  });
}
