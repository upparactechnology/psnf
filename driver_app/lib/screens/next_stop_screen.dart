import 'package:flutter/material.dart';
import '../theme/colors.dart';
import '../models/student.dart';

class NextStopScreen extends StatelessWidget {
  final Student? student;

  const NextStopScreen({super.key, required this.student});

  @override
  Widget build(BuildContext context) {
    if (student == null) {
      return const Scaffold(
        backgroundColor: Color(0xFF080D1A),
        body: Center(child: Text('No student context')),
      );
    }

    return Scaffold(
      backgroundColor: const Color(0xFF080D1A),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Header navigation row
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  IconButton(
                    onPressed: () => Navigator.pop(context),
                    icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white, size: 20),
                  ),
                  const Text(
                    'Next Stop',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  IconButton(
                    onPressed: () => Navigator.pop(context),
                    icon: const Icon(Icons.close_rounded, color: Colors.white, size: 24),
                  ),
                ],
              ),
              const Spacer(),

              // Student card container
              Container(
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: const Color(0xFF0F172A),
                  borderRadius: BorderRadius.circular(32),
                  border: Border.all(color: AppColors.slate.shade850),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.3),
                      blurRadius: 20,
                      offset: const Offset(0, 10),
                    )
                  ],
                ),
                child: Column(
                  children: [
                    // Student photo / Avatar
                    Container(
                      width: 110,
                      height: 110,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        border: Border.all(color: const Color(0xFF6366F1), width: 3),
                        color: const Color(0xFF4F46E5).withOpacity(0.15),
                      ),
                      child: Center(
                        child: Text(
                          student!.initials,
                          style: const TextStyle(
                            color: Color(0xFF818CF8),
                            fontSize: 36,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Name & Grade
                    Text(
                      student!.fullName,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '${student!.className} - ${student!.section}',
                      style: const TextStyle(
                        color: Color(0xFF818CF8),
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 32),

                    // Detail items
                    _detailRow(Icons.location_on_rounded, student!.address),
                    const SizedBox(height: 18),
                    _detailRow(Icons.phone_rounded, student!.phone),
                    const SizedBox(height: 18),
                    _detailRow(Icons.person_rounded, 'Parent: ${student!.parentName}'),
                  ],
                ),
              ),

              const Spacer(),

              // Confirmation Action Buttons
              Row(
                children: [
                  // Picked Up Button (Green)
                  Expanded(
                    child: ElevatedButton(
                      onPressed: () {
                        Navigator.pop(context, StudentStatus.onboard);
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF10B981),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 18),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(20),
                        ),
                        elevation: 4,
                      ),
                      child: const Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.check_circle_rounded, size: 20),
                          SizedBox(width: 8),
                          Text(
                            'Picked Up',
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 16),
                  
                  // Not Picked Up Button (Red)
                  Expanded(
                    child: ElevatedButton(
                      onPressed: () {
                        Navigator.pop(context, StudentStatus.missed);
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFFEF4444),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 18),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(20),
                        ),
                        elevation: 4,
                      ),
                      child: const Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.cancel_rounded, size: 20),
                          SizedBox(width: 8),
                          Text(
                            'Not Picked Up',
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
            ],
          ),
        ),
      ),
    );
  }

  Widget _detailRow(IconData icon, String text) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(
          icon,
          color: const Color(0xFF94A3B8),
          size: 20,
        ),
        const SizedBox(width: 14),
        Expanded(
          child: Text(
            text,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 14,
              height: 1.4,
            ),
          ),
        ),
      ],
    );
  }
}
