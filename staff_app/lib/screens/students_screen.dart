import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'class_attendance_screen.dart';

class StudentsScreen extends StatefulWidget {
  const StudentsScreen({super.key});

  @override
  State<StudentsScreen> createState() => _StudentsScreenState();
}

class _StudentsScreenState extends State<StudentsScreen> {
  bool _isLoading = true;
  List<dynamic> _allStudents = [];
  List<dynamic> _filteredStudents = [];
  List<String> _classFilters = ['All'];
  final _searchController = TextEditingController();
  String _selectedClassFilter = 'All';

  @override
  void initState() {
    super.initState();
    _loadStudents();
    _searchController.addListener(_onSearchChanged);
  }

  @override
  void dispose() {
    _searchController.removeListener(_onSearchChanged);
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadStudents() async {
    setState(() => _isLoading = true);
    final result = await ApiService.getStudents();
    if (mounted) {
      setState(() {
        if (result['success'] == true && (result['data'] != null || result['students'] != null)) {
          _allStudents = result['data'] ?? result['students'];
        } else {
          // Mock fallback
          _allStudents = [
            {
              'id': 1,
              'first_name': 'Rohan',
              'last_name': 'Patel',
              'admission_number': 'ADM-2024-001',
              'class': '3-C',
              'disability_type': 'ASD',
            },
            {
              'id': 2,
              'first_name': 'Aarav',
              'last_name': 'Shah',
              'admission_number': 'ADM-2024-002',
              'class': '2-A',
              'disability_type': 'Down Syndrome',
            },
            {
              'id': 3,
              'first_name': 'Neha',
              'last_name': 'Joshi',
              'admission_number': 'ADM-2024-003',
              'class': '4-B',
              'disability_type': 'ADHD',
            },
            {
              'id': 4,
              'first_name': 'Ketan',
              'last_name': 'Mehta',
              'admission_number': 'ADM-2024-004',
              'class': '3-C',
              'disability_type': 'Cerebral Palsy',
            },
          ];
        }

        // Build dynamic class filters
        final classes = _allStudents
            .map((s) => (s['class'] ?? 'N/A').toString().trim())
            .where((c) => c.isNotEmpty)
            .toSet()
            .toList();
        classes.sort();

        _classFilters = ['All'] + classes.map((c) => 'Class: $c').toList();
        _filteredStudents = List.from(_allStudents);
        _isLoading = false;
      });
    }
  }

  void _onSearchChanged() {
    _applyFilters();
  }

  void _applyFilters() {
    final query = _searchController.text.toLowerCase().trim();
    setState(() {
      _filteredStudents = _allStudents.where((student) {
        final firstName = (student['first_name'] ?? '').toString().toLowerCase();
        final lastName = (student['last_name'] ?? '').toString().toLowerCase();
        final nameMatches = firstName.contains(query) || lastName.contains(query);

        final studentClass = 'Class: ${(student['class'] ?? '')}';
        final classMatches = _selectedClassFilter == 'All' || studentClass.trim() == _selectedClassFilter.trim();

        return nameMatches && classMatches;
      }).toList();
    });
  }

  Future<void> _showGuardiansDialog(BuildContext context, dynamic student) async {
    final int studentId = int.tryParse(student['id'].toString()) ?? 0;
    
    showDialog(
      context: context,
      builder: (context) {
        bool isLoading = true;
        List<dynamic> studentGuardians = [];
        
        return StatefulBuilder(
          builder: (context, setDialogState) {
            if (isLoading) {
              ApiService.getGuardians().then((res) {
                if (res['success'] == true && res['guardians'] != null) {
                  final List<dynamic> all = res['guardians'];
                  studentGuardians = all.where((g) => g['student_id'].toString() == studentId.toString()).toList();
                }
                setDialogState(() {
                  isLoading = false;
                });
              });
              
              return const AlertDialog(
                title: Text('Loading Guardians...'),
                content: SizedBox(
                  height: 100,
                  child: Center(child: CircularProgressIndicator()),
                ),
              );
            }
            
            return AlertDialog(
              title: Text('Guardians for ${student['first_name']}'),
              content: studentGuardians.isEmpty
                  ? const Text('No guardians registered for this student.')
                  : SizedBox(
                      width: double.maxFinite,
                      child: ListView.builder(
                        shrinkWrap: true,
                        itemCount: studentGuardians.length,
                        itemBuilder: (context, index) {
                          final g = studentGuardians[index];
                          return ListTile(
                            contentPadding: EdgeInsets.zero,
                            title: Text(g['name'] ?? 'Guardian', style: const TextStyle(fontWeight: FontWeight.bold)),
                            subtitle: Text('Relation: ${g['relation'] ?? 'N/A'}\nPhone: ${g['phone'] ?? 'N/A'}'),
                          );
                        },
                      ),
                    ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('Close'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  void _showMarkAttendanceDialog(BuildContext context, dynamic student) {
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: Text('Mark Attendance: ${student['first_name']}'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              ListTile(
                leading: const Icon(Icons.check_circle_rounded, color: Colors.green),
                title: const Text('Present'),
                onTap: () async {
                  Navigator.pop(context);
                  final res = await ApiService.markStudentAttendance(int.parse(student['id'].toString()), 'present');
                  if (res['success'] == true) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Marked Present successfully!'), backgroundColor: Colors.green),
                    );
                  } else {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('Failed: ${res['message']}'), backgroundColor: Colors.red),
                    );
                  }
                },
              ),
              ListTile(
                leading: const Icon(Icons.cancel_rounded, color: Colors.red),
                title: const Text('Absent'),
                onTap: () async {
                  Navigator.pop(context);
                  final res = await ApiService.markStudentAttendance(int.parse(student['id'].toString()), 'absent');
                  if (res['success'] == true) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Marked Absent successfully!'), backgroundColor: Colors.green),
                    );
                  } else {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('Failed: ${res['message']}'), backgroundColor: Colors.red),
                    );
                  }
                },
              ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _showEarlyPickupDialog(BuildContext context, dynamic student) async {
    final theme = Theme.of(context);
    final String fullName = '${student['first_name']} ${student['last_name']}';
    final int studentId = int.tryParse(student['id'].toString()) ?? 0;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        bool isLoadingGuardians = true;
        List<dynamic> eligibleGuardians = [];
        dynamic selectedGuardian;
        final notesController = TextEditingController(text: 'Verified guardian ID card physically.');

        return StatefulBuilder(
          builder: (context, setDialogState) {
            if (isLoadingGuardians) {
              ApiService.getGuardians().then((guardiansRes) {
                if (!context.mounted) return;
                List<dynamic> fetched = [];
                if (guardiansRes['success'] == true && guardiansRes['guardians'] != null) {
                  final List<dynamic> allGuardians = guardiansRes['guardians'];
                  fetched = allGuardians.where((g) {
                    return g['student_id'].toString() == studentId.toString();
                  }).toList();
                }

                setDialogState(() {
                  eligibleGuardians = fetched;
                  selectedGuardian = eligibleGuardians.isNotEmpty ? eligibleGuardians.first : null;
                  isLoadingGuardians = false;
                });
              });
              return AlertDialog(
                title: Text('Early Pickup: $fullName'),
                content: const SizedBox(height: 100, child: Center(child: CircularProgressIndicator())),
              );
            }

            return AlertDialog(
              title: Text('Early Pickup: $fullName'),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const Text(
                      'Select and verify pickup guardian:',
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Colors.grey),
                    ),
                    const SizedBox(height: 8),
                    if (eligibleGuardians.isEmpty)
                      const Padding(
                        padding: EdgeInsets.symmetric(vertical: 12),
                        child: Text(
                          'No verified guardians found for this student in the database.',
                          style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold),
                        ),
                      )
                    else
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        decoration: BoxDecoration(
                          border: Border.all(color: Colors.grey.shade300),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton<dynamic>(
                            value: selectedGuardian,
                            isExpanded: true,
                            items: eligibleGuardians.map((g) {
                              return DropdownMenuItem<dynamic>(
                                value: g,
                                child: Text('${g['name']} (${g['relationship']})'),
                              );
                            }).toList(),
                            onChanged: (val) {
                              setDialogState(() {
                                selectedGuardian = val;
                              });
                            },
                          ),
                        ),
                      ),
                    const SizedBox(height: 12),
                    if (selectedGuardian != null) ...[
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if (selectedGuardian['photo'] != null)
                            ClipRRect(
                              borderRadius: BorderRadius.circular(8),
                              child: Image.network(selectedGuardian['photo'], width: 64, height: 64, fit: BoxFit.cover, errorBuilder: (_,__,___) => const Icon(Icons.account_circle, size: 64, color: Colors.grey)),
                            )
                          else
                            const Icon(Icons.account_circle, size: 64, color: Colors.grey),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text('Relation: ${selectedGuardian['relationship']}', style: const TextStyle(fontSize: 13)),
                                Text('Phone: ${selectedGuardian['phone']}', style: const TextStyle(fontSize: 13)),
                                const SizedBox(height: 4),
                                Text('Aadhar: ${selectedGuardian['aadhar'] ?? 'Not Provided'}', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.black87)),
                                Text('Docs: ${selectedGuardian['documents'] ?? 'None'}', style: const TextStyle(fontSize: 12, color: Colors.blue)),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                    ],
                    TextField(
                      controller: notesController,
                      decoration: InputDecoration(
                        labelText: 'Verification Notes',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      maxLines: 2,
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('Cancel'),
                ),
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: theme.primaryColor,
                    foregroundColor: Colors.white,
                  ),
                  onPressed: selectedGuardian == null ? null : () async {
                    Navigator.pop(context);
                    setState(() => _isLoading = true);
                    final res = await ApiService.approveEarlyPickup(
                      studentId: studentId,
                      guardianName: selectedGuardian['name'],
                      relationship: selectedGuardian['relationship'],
                      notes: notesController.text.trim(),
                    );
                    setState(() => _isLoading = false);

                    if (mounted) {
                      if (res['success'] == true) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(res['message'] ?? 'Approved successfully! Removed from Bus Route.'),
                            backgroundColor: Colors.green,
                          ),
                        );
                      } else {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(res['message'] ?? 'Failed to approve early pickup.'),
                            backgroundColor: Colors.red,
                          ),
                        );
                      }
                    }
                  },
                  child: const Text('Verify & Release'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    if (_isLoading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Students Directory'),
        centerTitle: true,
        actions: [
          TextButton.icon(
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (context) => const ClassAttendanceScreen()),
              );
            },
            icon: const Icon(Icons.fact_check_rounded, color: Colors.white),
            label: const Text('Attendance', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
      body: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Search Field
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Search students by name...',
                prefixIcon: const Icon(Icons.search_rounded),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear_rounded),
                        onPressed: () => _searchController.clear(),
                      )
                    : null,
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.symmetric(vertical: 0),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                ),
              ),
            ),
          ),

          // Filters row
          SizedBox(
            height: 38,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16),
              itemCount: _classFilters.length,
              itemBuilder: (context, index) {
                final filter = _classFilters[index];
                final isSelected = filter == _selectedClassFilter;

                return Padding(
                  padding: const EdgeInsets.only(right: 8.0),
                  child: ChoiceChip(
                    label: Text(
                      filter,
                      style: TextStyle(
                        color: isSelected ? Colors.white : Colors.grey.shade700,
                        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                        fontSize: 12,
                      ),
                    ),
                    selected: isSelected,
                    selectedColor: theme.primaryColor,
                    backgroundColor: Colors.white,
                    side: BorderSide(color: isSelected ? Colors.transparent : const Color(0xFFE2E8F0)),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    onSelected: (selected) {
                      if (selected) {
                        setState(() {
                          _selectedClassFilter = filter;
                          _applyFilters();
                        });
                      }
                    },
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: 16),

          // Students List
          Expanded(
            child: _filteredStudents.isEmpty
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.search_off_rounded, size: 64, color: Colors.grey.shade300),
                        const SizedBox(height: 12),
                        Text('No students match your query', style: TextStyle(color: Colors.grey.shade400)),
                      ],
                    ),
                  )
                : ListView.separated(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                    itemCount: _filteredStudents.length,
                    separatorBuilder: (context, index) => const SizedBox(height: 12),
                    itemBuilder: (context, index) {
                      final student = _filteredStudents[index];
                      final String firstName = student['first_name'] ?? 'Student';
                      final String lastName = student['last_name'] ?? '';
                      final String studentClass = student['class'] ?? 'N/A';
                      final String admissionNo = student['admission_number'] ?? 'N/A';
                      final String disability = student['disability_type'] ?? 'Other';

                      return Card(
                        margin: EdgeInsets.zero,
                        child: Padding(
                          padding: const EdgeInsets.all(14.0),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              Row(
                                children: [
                                  CircleAvatar(
                                    radius: 24,
                                    backgroundColor: theme.primaryColor.withOpacity(0.08),
                                    child: Text(
                                      firstName.isNotEmpty ? firstName.substring(0, 1) : 'S',
                                      style: TextStyle(
                                        fontWeight: FontWeight.bold,
                                        fontSize: 18,
                                        color: theme.primaryColor,
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 14),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          '$firstName $lastName',
                                          style: const TextStyle(
                                            fontWeight: FontWeight.bold,
                                            fontSize: 15,
                                            color: Color(0xFF1E293B),
                                          ),
                                        ),
                                        const SizedBox(height: 2),
                                        Text(
                                          'Class: $studentClass  •  Roll: $admissionNo',
                                          style: TextStyle(
                                            fontSize: 12,
                                            color: Colors.grey.shade500,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFE8F0FE),
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    child: Text(
                                      disability,
                                      style: const TextStyle(
                                        color: Color(0xFF1A73E8),
                                        fontSize: 10,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 12),
                              const Divider(height: 1),
                              const SizedBox(height: 8),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  // Guardians button
                                  Expanded(
                                    child: OutlinedButton.icon(
                                      onPressed: () => _showGuardiansDialog(context, student),
                                      icon: const Icon(Icons.people_alt_rounded, size: 14),
                                      label: const Text('Guardians', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold)),
                                      style: OutlinedButton.styleFrom(
                                        foregroundColor: const Color(0xFF0A5C36),
                                        side: const BorderSide(color: Color(0xFF0A5C36)),
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                        padding: const EdgeInsets.symmetric(vertical: 6),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 6),
                                  // Attendance button
                                  Expanded(
                                    child: OutlinedButton.icon(
                                      onPressed: () => _showMarkAttendanceDialog(context, student),
                                      icon: const Icon(Icons.fact_check_rounded, size: 14),
                                      label: const Text('Attendance', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold)),
                                      style: OutlinedButton.styleFrom(
                                        foregroundColor: Colors.blue.shade800,
                                        side: BorderSide(color: Colors.blue.shade800),
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                        padding: const EdgeInsets.symmetric(vertical: 6),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 6),
                                  // Release button
                                  Expanded(
                                    child: OutlinedButton.icon(
                                      onPressed: () => _showEarlyPickupDialog(context, student),
                                      icon: const Icon(Icons.outbox_rounded, size: 14),
                                      label: const Text('Release', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold)),
                                      style: OutlinedButton.styleFrom(
                                        foregroundColor: Colors.orange.shade800,
                                        side: BorderSide(color: Colors.orange.shade800),
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                        padding: const EdgeInsets.symmetric(vertical: 6),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}
