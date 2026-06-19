import 'package:flutter/material.dart';
import '../theme/colors.dart';
import '../models/student.dart';

class StudentsOnboardScreen extends StatefulWidget {
  final List<Student> students;

  const StudentsOnboardScreen({super.key, required this.students});

  @override
  State<StudentsOnboardScreen> createState() => _StudentsOnboardScreenState();
}

class _StudentsOnboardScreenState extends State<StudentsOnboardScreen> {
  String _searchQuery = '';

  @override
  Widget build(BuildContext context) {
    // Filter list based on search query
    final filteredList = widget.students.where((student) {
      final nameMatches = student.fullName.toLowerCase().contains(_searchQuery.toLowerCase());
      return nameMatches;
    }).toList();

    int totalOnboard = widget.students.where((s) => s.status == StudentStatus.onboard).length;

    return Scaffold(
      backgroundColor: const Color(0xFF080D1A),
      appBar: AppBar(
        backgroundColor: const Color(0xFF0F172A),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        title: Row(
          children: [
            const Text(
              'Students Onboard',
              style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
              decoration: BoxDecoration(
                color: const Color(0xFF6366F1).withOpacity(0.2),
                borderRadius: BorderRadius.circular(100),
              ),
              child: Text(
                '$totalOnboard',
                style: const TextStyle(
                  color: Color(0xFF818CF8),
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ],
        ),
      ),
      body: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Search box
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: TextField(
              onChanged: (val) {
                setState(() {
                  _searchQuery = val;
                });
              },
              style: const TextStyle(color: Colors.white, fontSize: 14),
              decoration: InputDecoration(
                filled: true,
                fillColor: const Color(0xFF1E293B).withOpacity(0.5),
                hintText: 'Search student',
                hintStyle: TextStyle(color: AppColors.slate.shade500, fontSize: 14),
                prefixIcon: Icon(Icons.search_rounded, color: AppColors.slate.shade400, size: 20),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: BorderSide(color: AppColors.slate.shade850),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: const BorderSide(color: Color(0xFF6366F1), width: 1),
                ),
                contentPadding: const EdgeInsets.symmetric(vertical: 14),
              ),
            ),
          ),

          // Students List
          Expanded(
            child: filteredList.isEmpty
                ? Center(
                    child: Text(
                      'No students found',
                      style: TextStyle(color: AppColors.slate.shade500, fontSize: 14),
                    ),
                  )
                : ListView.builder(
                    padding: const EdgeInsets.symmetric(horizontal: 16.0),
                    itemCount: filteredList.length,
                    itemBuilder: (context, index) {
                      final student = filteredList[index];
                      return _buildStudentTile(student);
                    },
                  ),
          ),
          
          // Bottom View All bar
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: const Color(0xFF0F172A),
              border: Border(
                top: BorderSide(color: AppColors.slate.shade850, width: 1),
              ),
            ),
            child: ElevatedButton(
              onPressed: () {
                Navigator.pop(context);
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF6366F1),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
              ),
              child: Text(
                'View All ($totalOnboard)',
                style: const TextStyle(
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

  Widget _buildStudentTile(Student student) {
    Color statusColor;
    String statusLabel;
    
    switch (student.status) {
      case StudentStatus.onboard:
        statusColor = const Color(0xFF10B981);
        statusLabel = 'Onboard';
        break;
      case StudentStatus.missed:
        statusColor = const Color(0xFFEF4444);
        statusLabel = 'Missed';
        break;
      case StudentStatus.dropped:
        statusColor = const Color(0xFF3B82F6);
        statusLabel = 'Dropped';
        break;
      case StudentStatus.onLeave:
        statusColor = const Color(0xFFF59E0B);
        statusLabel = 'On Leave';
        break;
      case StudentStatus.pending:
      default:
        statusColor = const Color(0xFF64748B);
        statusLabel = 'Pending';
        break;
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B).withOpacity(0.3),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.slate.shade850),
      ),
      child: Row(
        children: [
          CircleAvatar(
            radius: 20,
            backgroundColor: const Color(0xFF6366F1).withOpacity(0.15),
            child: Text(
              student.initials,
              style: const TextStyle(
                color: Color(0xFF818CF8),
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
                  student.fullName,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  '${student.className} - ${student.section}',
                  style: TextStyle(
                    color: AppColors.slate.shade400,
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                student.actionTime ?? student.scheduledTime,
                style: TextStyle(
                  color: AppColors.slate.shade400,
                  fontSize: 11,
                ),
              ),
              const SizedBox(height: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: statusColor.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(color: statusColor.withOpacity(0.3)),
                ),
                child: Text(
                  statusLabel,
                  style: TextStyle(
                    color: statusColor,
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
