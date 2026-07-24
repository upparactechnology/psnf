import 'package:flutter/material.dart';
import '../services/api_service.dart';

class StudentsScreen extends StatefulWidget {
  const StudentsScreen({super.key});

  @override
  State<StudentsScreen> createState() => _StudentsScreenState();
}

class _StudentsScreenState extends State<StudentsScreen> {
  bool _isLoading = true;
  List<dynamic> _allStudents = [];
  List<dynamic> _filteredStudents = [];
  final _searchController = TextEditingController();
  String _selectedClassFilter = 'All';

  final List<String> _classFilters = ['All', 'Class: 3-C', 'Class: 2-A', 'Class: 4-B', 'Class: 1-A'];

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
        if (result['success'] == true && result['students'] != null) {
          _allStudents = result['students'];
        } else {
          // Mock data fallback for Special Needs Center
          _allStudents = [
            {
              'first_name': 'Rohan',
              'last_name': 'Patel',
              'admission_number': 'ADM-2024-001',
              'class': '3-C',
              'disability_type': 'ASD',
              'photo': null
            },
            {
              'first_name': 'Aarav',
              'last_name': 'Shah',
              'admission_number': 'ADM-2024-002',
              'class': '2-A',
              'disability_type': 'Down Syndrome',
              'photo': null
            },
            {
              'first_name': 'Neha',
              'last_name': 'Joshi',
              'admission_number': 'ADM-2024-003',
              'class': '4-B',
              'disability_type': 'ADHD',
              'photo': null
            },
            {
              'first_name': 'Ketan',
              'last_name': 'Mehta',
              'admission_number': 'ADM-2024-004',
              'class': '3-C',
              'disability_type': 'Cerebral Palsy',
              'photo': null
            },
            {
              'first_name': 'Shivam',
              'last_name': 'Das',
              'admission_number': 'ADM-2024-005',
              'class': '1-A',
              'disability_type': 'Intellectual Disability',
              'photo': null
            }
          ];
        }
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
                          child: Row(
                            children: [
                              CircleAvatar(
                                radius: 24,
                                backgroundColor: theme.primaryColor.withOpacity(0.08),
                                child: Text(
                                  firstName.substring(0, 1),
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
