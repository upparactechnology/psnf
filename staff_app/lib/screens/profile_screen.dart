import 'package:flutter/material.dart';
import '../services/api_service.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  void _handleLogout(BuildContext context) {
    ApiService.clearPersistedAuth();
    Navigator.pushReplacementNamed(context, '/login');
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final profile = ApiService.userProfile ?? {};

    final String staffName = (profile['name'] ?? 'Staff Member').toString();
    final String staffEmail = (profile['email'] ?? '').toString();
    final String staffPhone = (profile['phone'] ?? '').toString();
    final String staffRole = (profile['role'] ?? profile['designation'] ?? 'Educator').toString();
    final String staffId = (profile['employee_id'] ?? (profile['id'] != null ? 'ST${1000 + int.tryParse(profile['id'].toString())!}' : '')).toString();
    final String school = (profile['school'] ?? 'Pearl Special Needs School').toString();
    final String branch = (profile['branch'] ?? 'Main Branch').toString();

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Profile'),
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const SizedBox(height: 10),
            // Circular Avatar card
            Card(
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 24.0, horizontal: 16.0),
                child: Column(
                  children: [
                    CircleAvatar(
                      radius: 46,
                      backgroundColor: theme.primaryColor.withOpacity(0.08),
                      child: Text(
                        staffName.isNotEmpty ? staffName.substring(0, 1) : 'S',
                        style: TextStyle(
                          fontSize: 36,
                          fontWeight: FontWeight.bold,
                          color: theme.primaryColor,
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      staffName,
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF1E293B),
                      ),
                    ),
                    const SizedBox(height: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 3),
                      decoration: BoxDecoration(
                        color: const Color(0xFFE6F4EA),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        staffRole,
                        style: const TextStyle(
                          color: Color(0xFF137333),
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 20),

            // Profile info cards
            const Text(
              "Personal Details",
              style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
            ),
            const SizedBox(height: 10),
            Card(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
                child: Column(
                  children: [
                    _buildProfileItem(Icons.badge_outlined, 'Employee ID', staffId.isNotEmpty ? staffId : 'N/A'),
                    const Divider(height: 1),
                    _buildProfileItem(Icons.email_outlined, 'Email Address', staffEmail.isNotEmpty ? staffEmail : 'N/A'),
                    const Divider(height: 1),
                    _buildProfileItem(Icons.phone_iphone_rounded, 'Mobile Phone', staffPhone.isNotEmpty ? staffPhone : 'N/A'),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),

            // Branch info cards
            const Text(
              "School Affiliation",
              style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
            ),
            const SizedBox(height: 10),
            Card(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
                child: Column(
                  children: [
                    _buildProfileItem(Icons.school_outlined, 'School Name', school),
                    const Divider(height: 1),
                    _buildProfileItem(Icons.location_on_outlined, 'Assigned Branch', branch),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 32),

            // Log Out Button
            ElevatedButton.icon(
              onPressed: () => _handleLogout(context),
              icon: const Icon(Icons.logout_rounded, color: Colors.white),
              label: const Text('Log Out', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFC5221F),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
                elevation: 0,
              ),
            ),
            const SizedBox(height: 30),
          ],
        ),
      ),
    );
  }

  Widget _buildProfileItem(IconData icon, String label, String val) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 14.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: const Color(0xFF64748B), size: 22),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade400, fontWeight: FontWeight.w500),
                ),
                const SizedBox(height: 2),
                Text(
                  val,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF334155),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
