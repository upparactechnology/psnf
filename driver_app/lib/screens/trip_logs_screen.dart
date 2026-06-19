import 'package:flutter/material.dart';
import '../theme/colors.dart';
import 'dashboard_screen.dart';

class TripLogsScreen extends StatelessWidget {
  const TripLogsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final bool isDark = Theme.of(context).brightness == Brightness.dark;
    final Color textColor = isDark ? Colors.white : const Color(0xFF0F172A);
    final Color subtitleColor = isDark ? const Color(0xFF94A3B8) : const Color(0xFF475569);
    final Color cardBg = isDark ? const Color(0xFF1E293B).withOpacity(0.4) : Colors.white;
    final Color borderColor = isDark ? AppColors.slate.shade850 : const Color(0xFFE2E8F0);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Trip Logs'),
        centerTitle: false,
        elevation: 0,
        leading: IconButton(
          icon: Icon(Icons.arrow_back_ios_new_rounded, color: textColor, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.all(24.0),
          children: [
            // Stats summary card
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: isDark 
                      ? [const Color(0xFF1E293B), const Color(0xFF0F172A)]
                      : [const Color(0xFFF1F5F9), const Color(0xFFE2E8F0)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: borderColor),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: [
                  _statItem('Distance', '42 km', textColor, subtitleColor),
                  Container(width: 1, height: 40, color: borderColor),
                  _statItem('Time', '2h 15m', textColor, subtitleColor),
                  Container(width: 1, height: 40, color: borderColor),
                  _statItem('Trips Today', 
                      '${(DashboardScreen.isPickupCompleted ? 1 : 0) + (DashboardScreen.isDropCompleted ? 1 : 0)}/2', 
                      textColor, 
                      subtitleColor),
                ],
              ),
            ),
            const SizedBox(height: 28),

            // Today's trips
            Text(
              "Today's Trips",
              style: TextStyle(
                color: textColor,
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 12),

            if (DashboardScreen.isPickupCompleted)
              _buildTripCard(
                'Morning Shift (Pickup)',
                'Pickup Route - 1',
                'Completed at 08:35 AM',
                '20 Stops  |  18 Students',
                '14.2 km  |  50 mins',
                true,
                cardBg,
                borderColor,
                textColor,
                subtitleColor,
              )
            else
              _buildTripCard(
                'Morning Shift (Pickup)',
                'Pickup Route - 1',
                'Not started',
                '20 Stops  |  18 Students',
                '--',
                false,
                cardBg,
                borderColor,
                textColor,
                subtitleColor,
              ),

            const SizedBox(height: 12),

            if (DashboardScreen.isDropCompleted)
              _buildTripCard(
                'Evening Shift (Drop)',
                'Drop Route - 1',
                'Completed at 04:32 PM',
                '2 Stops  |  2 Students',
                '8.5 km  |  22 mins',
                true,
                cardBg,
                borderColor,
                textColor,
                subtitleColor,
              )
            else
              _buildTripCard(
                'Evening Shift (Drop)',
                'Drop Route - 1',
                'Not started',
                '2 Stops  |  2 Students',
                '--',
                false,
                cardBg,
                borderColor,
                textColor,
                subtitleColor,
              ),

            const SizedBox(height: 28),

            // Past trips
            Text(
              "Past Logs (June 18)",
              style: TextStyle(
                color: textColor,
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 12),

            _buildTripCard(
              'Evening Shift (Drop)',
              'Drop Route - 1',
              'Completed at 04:30 PM',
              '2 Stops  |  2 Students',
              '8.5 km  |  24 mins',
              true,
              cardBg,
              borderColor,
              textColor,
              subtitleColor,
            ),
            const SizedBox(height: 12),
            _buildTripCard(
              'Morning Shift (Pickup)',
              'Pickup Route - 1',
              'Completed at 08:36 AM',
              '20 Stops  |  18 Students',
              '14.3 km  |  52 mins',
              true,
              cardBg,
              borderColor,
              textColor,
              subtitleColor,
            ),
          ],
        ),
      ),
    );
  }

  Widget _statItem(String title, String value, Color textColor, Color subtitleColor) {
    return Column(
      children: [
        Text(
          value,
          style: TextStyle(
            color: textColor,
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          title,
          style: TextStyle(
            color: subtitleColor,
            fontSize: 11,
          ),
        ),
      ],
    );
  }

  Widget _buildTripCard(
    String shift,
    String route,
    String time,
    String stops,
    String distance,
    bool isCompleted,
    Color bg,
    Color border,
    Color text,
    Color subText,
  ) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    shift,
                    style: TextStyle(
                      color: subText,
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    route,
                    style: TextStyle(
                      color: text,
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: isCompleted 
                      ? const Color(0xFF10B981).withOpacity(0.15) 
                      : const Color(0xFF64748B).withOpacity(0.15),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  isCompleted ? 'Completed' : 'Pending',
                  style: TextStyle(
                    color: isCompleted 
                        ? (text == Colors.white ? const Color(0xFF34D399) : const Color(0xFF059669)) 
                        : (text == Colors.white ? const Color(0xFF94A3B8) : const Color(0xFF475569)),
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                stops,
                style: TextStyle(color: subText, fontSize: 12),
              ),
              Text(
                distance,
                style: TextStyle(color: text, fontSize: 12, fontWeight: FontWeight.bold),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            time,
            style: TextStyle(color: subText, fontSize: 11, fontStyle: FontStyle.italic),
          ),
        ],
      ),
    );
  }
}
