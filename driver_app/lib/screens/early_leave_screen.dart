import 'package:flutter/material.dart';
import '../theme/colors.dart';

class EarlyLeaveScreen extends StatelessWidget {
  const EarlyLeaveScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF080D1A),
      appBar: AppBar(
        backgroundColor: const Color(0xFF0F172A),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Early Leave Alert',
          style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
        ),
      ),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Spacer(),

              // Alert container
              Container(
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: const Color(0xFF0F172A),
                  borderRadius: BorderRadius.circular(32),
                  border: Border.all(color: const Color(0xFFF59E0B).withOpacity(0.3)),
                ),
                child: Column(
                  children: [
                    // Warning bell
                    Container(
                      width: 64,
                      height: 64,
                      decoration: BoxDecoration(
                        color: const Color(0xFFF59E0B).withOpacity(0.15),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.notifications_active_rounded,
                        color: Color(0xFFF59E0B),
                        size: 32,
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Student avatar
                    CircleAvatar(
                      radius: 36,
                      backgroundColor: const Color(0xFF6366F1).withOpacity(0.15),
                      child: const Text(
                        'AP',
                        style: TextStyle(
                          color: Color(0xFF818CF8),
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                    const SizedBox(height: 14),

                    const Text(
                      'Anjali Patel',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    Text(
                      'Class 5 - A',
                      style: TextStyle(
                        color: AppColors.slate.shade400,
                        fontSize: 13,
                      ),
                    ),
                    const SizedBox(height: 20),

                    Text(
                      'Student is leaving early',
                      style: TextStyle(
                        color: const Color(0xFFF59E0B).withOpacity(0.9),
                        fontSize: 15,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 18),

                    Divider(color: AppColors.slate.shade850, thickness: 1),
                    const SizedBox(height: 18),

                    // Address Info
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Icon(Icons.location_on_rounded, color: AppColors.slate, size: 18),
                        const SizedBox(width: 10),
                        const Expanded(
                          child: Text(
                            '12, Green Park Society, Surat, Gujarat',
                            style: TextStyle(color: Colors.white, fontSize: 13),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    // Time Info
                    Row(
                      children: [
                        const Icon(Icons.access_time_filled_rounded, color: AppColors.slate, size: 18),
                        const SizedBox(width: 10),
                        Text(
                          '12:30 PM',
                          style: TextStyle(color: AppColors.slate.shade350, fontSize: 13),
                        ),
                      ],
                    ),
                  ],
                ),
              ),

              const Spacer(),

              // Action buttons
              ElevatedButton(
                onPressed: () {
                  Navigator.pop(context);
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFF59E0B),
                  foregroundColor: Colors.black,
                  padding: const EdgeInsets.symmetric(vertical: 18),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                  ),
                ),
                child: const Text(
                  'Start Navigation',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              const SizedBox(height: 12),

              OutlinedButton(
                onPressed: () {
                  Navigator.pop(context);
                },
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppColors.slate.shade400,
                  side: BorderSide(color: AppColors.slate.shade850),
                  padding: const EdgeInsets.symmetric(vertical: 18),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                  ),
                ),
                child: const Text(
                  'Ignore',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              const SizedBox(height: 10),
            ],
          ),
        ),
      ),
    );
  }
}
