import 'package:flutter/material.dart';
import '../services/api_service.dart';

class GuardianDirectoryScreen extends StatefulWidget {
  const GuardianDirectoryScreen({super.key});

  @override
  State<GuardianDirectoryScreen> createState() => _GuardianDirectoryScreenState();
}

class _GuardianDirectoryScreenState extends State<GuardianDirectoryScreen> {
  bool _isLoading = true;
  List<dynamic> _allGuardians = [];
  List<dynamic> _filteredGuardians = [];
  final _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadGuardians();
    _searchController.addListener(_onSearchChanged);
  }

  @override
  void dispose() {
    _searchController.removeListener(_onSearchChanged);
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadGuardians() async {
    setState(() => _isLoading = true);
    final result = await ApiService.getGuardians();
    if (mounted) {
      setState(() {
        if (result['success'] == true && result['guardians'] != null) {
          _allGuardians = result['guardians'];
        } else {
          // Mock data fallback if database check fails or is empty
          _allGuardians = [
            {
              'name': 'Meera Mehta',
              'phone': '+91-9876543210',
              'relationship': 'Mother',
              'student_name': 'Ketan Mehta',
              'student_class': 'Class: 3-C'
            },
            {
              'name': 'Rajesh Joshi',
              'phone': '+91-9123456780',
              'relationship': 'Father',
              'student_name': 'Neha Joshi',
              'student_class': 'Class: 4-B'
            },
            {
              'name': 'Suman Patel',
              'phone': '+91-9567841230',
              'relationship': 'Mother',
              'student_name': 'Rohan Patel',
              'student_class': 'Class: 3-C'
            },
            {
              'name': 'Vikram Shah',
              'phone': '+91-9890123456',
              'relationship': 'Father',
              'student_name': 'Aarav Shah',
              'student_class': 'Class: 2-A'
            }
          ];
        }
        _filteredGuardians = List.from(_allGuardians);
        _isLoading = false;
      });
    }
  }

  void _onSearchChanged() {
    final query = _searchController.text.toLowerCase().trim();
    setState(() {
      _filteredGuardians = _allGuardians.where((guardian) {
        final gName = (guardian['name'] ?? '').toString().toLowerCase();
        final sName = (guardian['student_name'] ?? '').toString().toLowerCase();
        return gName.contains(query) || sName.contains(query);
      }).toList();
    });
  }

  void _simulateCall(String phone, String name) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Simulate Call'),
        content: Text('Do you want to simulate a call to $name at $phone?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Cancel')),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('Simulating call to $phone...'),
                  backgroundColor: Colors.green.shade700,
                ),
              );
            },
            child: const Text('Call'),
          ),
        ],
      ),
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
        title: const Text('Guardian Directory'),
        centerTitle: true,
      ),
      body: Column(
        children: [
          // Search Field
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Search by guardian or student name...',
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

          // Guardian listing
          Expanded(
            child: _filteredGuardians.isEmpty
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.contact_phone_rounded, size: 64, color: Colors.grey.shade300),
                        const SizedBox(height: 12),
                        Text('No contacts found', style: TextStyle(color: Colors.grey.shade400)),
                      ],
                    ),
                  )
                : ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: _filteredGuardians.length,
                    separatorBuilder: (context, index) => const SizedBox(height: 12),
                    itemBuilder: (context, index) {
                      final contact = _filteredGuardians[index];
                      final String parentName = contact['name'] ?? 'Guardian';
                      final String phone = contact['phone'] ?? 'N/A';
                      final String relation = contact['relationship'] ?? 'Guardian';
                      final String studentName = contact['student_name'] ?? 'Student';
                      final String studentClass = contact['student_class'] ?? 'N/A';

                      return Card(
                        margin: EdgeInsets.zero,
                        child: Padding(
                          padding: const EdgeInsets.all(14.0),
                          child: Row(
                            children: [
                              CircleAvatar(
                                radius: 24,
                                backgroundColor: theme.primaryColor.withOpacity(0.08),
                                child: Icon(Icons.family_restroom_rounded, color: theme.primaryColor, size: 22),
                              ),
                              const SizedBox(width: 14),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      parentName,
                                      style: const TextStyle(
                                        fontWeight: FontWeight.bold,
                                        fontSize: 15,
                                        color: Color(0xFF1E293B),
                                      ),
                                    ),
                                    const SizedBox(height: 2),
                                    Text(
                                      '$relation of $studentName ($studentClass)',
                                      style: TextStyle(
                                        fontSize: 11,
                                        color: Colors.grey.shade500,
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      phone,
                                      style: TextStyle(
                                        fontSize: 13,
                                        fontWeight: FontWeight.bold,
                                        color: theme.primaryColor,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              if (phone != 'N/A')
                                IconButton(
                                  icon: const Icon(Icons.phone_in_talk_rounded, color: Color(0xFF137333)),
                                  onPressed: () => _simulateCall(phone, parentName),
                                  style: IconButton.styleFrom(
                                    backgroundColor: const Color(0xFFE6F4EA),
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                  ),
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
