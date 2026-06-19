enum StudentStatus { pending, onboard, missed, dropped, onLeave }

class Student {
  final int id;
  final String firstName;
  final String lastName;
  final String className;
  final String section;
  final String address;
  final String phone;
  final String parentName;
  final double distanceKm;
  final String scheduledTime;
  StudentStatus status;
  String? actionTime;

  Student({
    required this.id,
    required this.firstName,
    required this.lastName,
    required this.className,
    required this.section,
    required this.address,
    required this.phone,
    required this.parentName,
    required this.distanceKm,
    required this.scheduledTime,
    this.status = StudentStatus.pending,
    this.actionTime,
  });

  String get fullName => '$firstName $lastName';
  String get initials => firstName.substring(0, 1).toUpperCase();
}
