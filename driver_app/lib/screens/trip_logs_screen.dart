import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../theme/colors.dart';
import '../services/api_service.dart';

class TripLogsScreen extends StatefulWidget {
  const TripLogsScreen({super.key});

  @override
  State<TripLogsScreen> createState() => _TripLogsScreenState();
}

class _TripLogsScreenState extends State<TripLogsScreen> {
  DateTime _selectedDate = DateTime.now();
  Map<String, dynamic>? _tripData;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate);
    final data = await ApiService.fetchTripHistory(dateStr);
    setState(() {
      _tripData = data;
      _isLoading = false;
    });
  }

  void _selectDate(DateTime date) {
    setState(() => _selectedDate = date);
    _loadData();
  }

  bool get _isToday {
    final now = DateTime.now();
    return _selectedDate.year == now.year &&
        _selectedDate.month == now.month &&
        _selectedDate.day == now.day;
  }

  bool get _isTomorrow {
    final tomorrow = DateTime.now().add(const Duration(days: 1));
    return _selectedDate.year == tomorrow.year &&
        _selectedDate.month == tomorrow.month &&
        _selectedDate.day == tomorrow.day;
  }

  String get _dateLabel {
    if (_isToday) return 'Today';
    if (_isTomorrow) return 'Tomorrow';
    return DateFormat('dd MMM yyyy').format(_selectedDate);
  }

  @override
  Widget build(BuildContext context) {
    final bool isDark = Theme.of(context).brightness == Brightness.dark;
    final Color textColor = isDark ? Colors.white : const Color(0xFF0F172A);
    final Color subText = isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B);
    final Color cardBg = isDark ? const Color(0xFF1E293B).withOpacity(0.4) : Colors.white;
    final Color borderColor = isDark ? AppColors.slate.shade850 : const Color(0xFFE2E8F0);
    final Color bgColor = isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC);

    final trip = _tripData?['trip'];
    final List students = (_tripData?['students'] ?? []) as List;

    int pickedUp = students.where((s) => s['status'] == 'Picked Up' || s['status'] == 'Dropped').length;
    int absent = students.where((s) => s['status'] == 'Absent').length;
    int waiting = students.where((s) => s['status'] == 'Waiting' || s['status'] == 'Scheduled').length;

    return Scaffold(
      backgroundColor: bgColor,
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Header
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 0),
              child: Row(
                children: [
                  Text(
                    'Trip Logs',
                    style: TextStyle(
                      color: textColor,
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const Spacer(),
                  // Calendar picker button
                  GestureDetector(
                    onTap: () async {
                      final picked = await showDatePicker(
                        context: context,
                        initialDate: _selectedDate,
                        firstDate: DateTime.now().subtract(const Duration(days: 90)),
                        lastDate: DateTime.now().add(const Duration(days: 7)),
                      );
                      if (picked != null) _selectDate(picked);
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      decoration: BoxDecoration(
                        color: cardBg,
                        border: Border.all(color: borderColor),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Row(
                        children: [
                          Icon(Icons.calendar_month_rounded, size: 16, color: const Color(0xFF6366F1)),
                          const SizedBox(width: 6),
                          Text(
                            _dateLabel,
                            style: TextStyle(
                              color: textColor,
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 16),

            // Date chip row: Yesterday, Today, Tomorrow
            SizedBox(
              height: 44,
              child: ListView(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 20),
                children: [
                  _dateChip(
                    label: 'Yesterday',
                    date: DateTime.now().subtract(const Duration(days: 1)),
                    textColor: textColor,
                    subText: subText,
                    cardBg: cardBg,
                    borderColor: borderColor,
                  ),
                  const SizedBox(width: 10),
                  _dateChip(
                    label: 'Today',
                    date: DateTime.now(),
                    textColor: textColor,
                    subText: subText,
                    cardBg: cardBg,
                    borderColor: borderColor,
                  ),
                  const SizedBox(width: 10),
                  _dateChip(
                    label: 'Tomorrow',
                    date: DateTime.now().add(const Duration(days: 1)),
                    textColor: textColor,
                    subText: subText,
                    cardBg: cardBg,
                    borderColor: borderColor,
                  ),
                ],
              ),
            ),

            const SizedBox(height: 16),

            // Content
            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator(color: Color(0xFF6366F1)))
                  : RefreshIndicator(
                      onRefresh: _loadData,
                      color: const Color(0xFF6366F1),
                      child: ListView(
                        padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
                        children: [
                          // Stats row
                          Container(
                            padding: const EdgeInsets.all(18),
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                colors: isDark
                                    ? [const Color(0xFF1E293B), const Color(0xFF0F172A)]
                                    : [Colors.white, const Color(0xFFF1F5F9)],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                              borderRadius: BorderRadius.circular(18),
                              border: Border.all(color: borderColor),
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceAround,
                              children: [
                                _statItem(students.length.toString(), 'Total', textColor, subText),
                                Container(width: 1, height: 36, color: borderColor),
                                _statItem(pickedUp.toString(), 'Done', const Color(0xFF10B981), subText),
                                Container(width: 1, height: 36, color: borderColor),
                                _statItem(waiting.toString(), 'Waiting', const Color(0xFFF59E0B), subText),
                                Container(width: 1, height: 36, color: borderColor),
                                _statItem(absent.toString(), 'Absent', const Color(0xFFEF4444), subText),
                              ],
                            ),
                          ),

                          const SizedBox(height: 16),

                          // Trip status card
                          if (trip != null)
                            Container(
                              padding: const EdgeInsets.all(16),
                              margin: const EdgeInsets.only(bottom: 16),
                              decoration: BoxDecoration(
                                color: cardBg,
                                borderRadius: BorderRadius.circular(14),
                                border: Border.all(color: borderColor),
                              ),
                              child: Row(
                                children: [
                                  Container(
                                    width: 10,
                                    height: 10,
                                    decoration: BoxDecoration(
                                      color: trip['status'] == 'completed'
                                          ? const Color(0xFF10B981)
                                          : const Color(0xFF6366F1),
                                      shape: BoxShape.circle,
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          'Trip #${trip['id']}',
                                          style: TextStyle(
                                            color: textColor,
                                            fontSize: 14,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                        Text(
                                          'Started: ${trip['started_at'] ?? '--'}',
                                          style: TextStyle(color: subText, fontSize: 11),
                                        ),
                                      ],
                                    ),
                                  ),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                    decoration: BoxDecoration(
                                      color: (trip['status'] == 'completed'
                                              ? const Color(0xFF10B981)
                                              : const Color(0xFF6366F1))
                                          .withOpacity(0.1),
                                      borderRadius: BorderRadius.circular(6),
                                    ),
                                    child: Text(
                                      trip['status'] == 'completed' ? 'Completed' : 'Active',
                                      style: TextStyle(
                                        color: trip['status'] == 'completed'
                                            ? const Color(0xFF10B981)
                                            : const Color(0xFF6366F1),
                                        fontSize: 11,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),

                          // Students section header
                          if (students.isNotEmpty) ...[
                            Text(
                              _isTomorrow ? 'Scheduled Students' : 'Students',
                              style: TextStyle(
                                color: textColor,
                                fontSize: 15,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 10),
                          ],

                          // Student cards
                          if (students.isEmpty)
                            Container(
                              padding: const EdgeInsets.symmetric(vertical: 48),
                              child: Column(
                                children: [
                                  Icon(Icons.route_outlined, color: subText, size: 48),
                                  const SizedBox(height: 12),
                                  Text(
                                    'No data for this date',
                                    style: TextStyle(color: subText, fontSize: 14),
                                  ),
                                ],
                              ),
                            )
                          else
                            ...students.map((student) {
                              final st = student['status'] ?? 'Waiting';
                              final isDone = st == 'Picked Up' || st == 'Dropped';
                              final isAbsent = st == 'Absent';
                              final isScheduled = st == 'Scheduled' || st == 'No Trip';

                              Color chipColor = isDone
                                  ? const Color(0xFF10B981)
                                  : isAbsent
                                      ? const Color(0xFFEF4444)
                                      : isScheduled
                                          ? const Color(0xFF6366F1)
                                          : const Color(0xFFF59E0B);

                              return Container(
                                margin: const EdgeInsets.only(bottom: 10),
                                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                decoration: BoxDecoration(
                                  color: cardBg,
                                  borderRadius: BorderRadius.circular(14),
                                  border: Border.all(color: borderColor),
                                ),
                                child: Row(
                                  children: [
                                    CircleAvatar(
                                      radius: 18,
                                      backgroundColor: chipColor.withOpacity(0.15),
                                      child: Text(
                                        (student['name'] ?? 'S').substring(0, 1).toUpperCase(),
                                        style: TextStyle(
                                          color: chipColor,
                                          fontWeight: FontWeight.bold,
                                          fontSize: 14,
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 12),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            student['name'] ?? 'Unknown',
                                            style: TextStyle(
                                              color: textColor,
                                              fontSize: 14,
                                              fontWeight: FontWeight.w600,
                                            ),
                                          ),
                                          if (student['address'] != null && student['address'].toString().isNotEmpty)
                                            Text(
                                              student['address'],
                                              style: TextStyle(color: subText, fontSize: 11),
                                              maxLines: 1,
                                              overflow: TextOverflow.ellipsis,
                                            ),
                                        ],
                                      ),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: chipColor.withOpacity(0.1),
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: Text(
                                        st,
                                        style: TextStyle(
                                          color: chipColor,
                                          fontSize: 10,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              );
                            }).toList(),
                        ],
                      ),
                    ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _dateChip({
    required String label,
    required DateTime date,
    required Color textColor,
    required Color subText,
    required Color cardBg,
    required Color borderColor,
  }) {
    final isSelected = _selectedDate.year == date.year &&
        _selectedDate.month == date.month &&
        _selectedDate.day == date.day;

    return GestureDetector(
      onTap: () => _selectDate(date),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF6366F1) : cardBg,
          borderRadius: BorderRadius.circular(22),
          border: Border.all(
            color: isSelected ? const Color(0xFF6366F1) : borderColor,
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: isSelected ? Colors.white : subText,
            fontSize: 13,
            fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
          ),
        ),
      ),
    );
  }

  Widget _statItem(String value, String label, Color valueColor, Color subText) {
    return Column(
      children: [
        Text(
          value,
          style: TextStyle(
            color: valueColor,
            fontSize: 20,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          label,
          style: TextStyle(color: subText, fontSize: 11),
        ),
      ],
    );
  }
}
