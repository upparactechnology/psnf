import 'package:flutter/material.dart';
import '../theme/colors.dart';

class SafetyCenterScreen extends StatefulWidget {
  const SafetyCenterScreen({super.key});

  @override
  State<SafetyCenterScreen> createState() => _SafetyCenterScreenState();
}

class _SafetyCenterScreenState extends State<SafetyCenterScreen> {
  bool _speedAlerts = true;
  bool _shareLocation = true;

  @override
  Widget build(BuildContext context) {
    final bool isDark = Theme.of(context).brightness == Brightness.dark;
    final Color textColor = isDark ? Colors.white : const Color(0xFF0F172A);
    final Color subtitleColor = isDark ? const Color(0xFF94A3B8) : const Color(0xFF475569);
    final Color cardBg = isDark ? const Color(0xFF1E293B).withOpacity(0.4) : Colors.white;
    final Color borderColor = isDark ? AppColors.slate.shade850 : const Color(0xFFE2E8F0);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Safety Center'),
        centerTitle: false,
        elevation: 0,
        leading: IconButton(
          icon: Icon(Icons.arrow_back_ios_new_rounded, color: textColor, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // SOS Banner
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFFEF4444), Color(0xFFB91C1C)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFFEF4444).withOpacity(0.3),
                      blurRadius: 12,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.2),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.warning_amber_rounded,
                        color: Colors.white,
                        size: 28,
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Emergency SOS',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Tap to broadcast location and alert school administrators.',
                            style: TextStyle(
                              color: Colors.white.withOpacity(0.9),
                              fontSize: 12,
                              height: 1.3,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 28),

              // Emergency Contacts Header
              Text(
                'Emergency Contacts',
                style: TextStyle(
                  color: textColor,
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 12),

              // Contacts list
              _buildContactTile('School Dispatcher (Main)', '+91 98765 43210', Icons.support_agent_rounded, cardBg, borderColor, textColor, subtitleColor),
              _buildContactTile('Police Helpline', '100', Icons.local_police_rounded, cardBg, borderColor, textColor, subtitleColor),
              _buildContactTile('Ambulance / Medical', '102', Icons.medical_services_rounded, cardBg, borderColor, textColor, subtitleColor),

              const SizedBox(height: 28),

              // Safety Controls Header
              Text(
                'Safety Controls',
                style: TextStyle(
                  color: textColor,
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 12),

              // Safety switches
              Container(
                decoration: BoxDecoration(
                  color: cardBg,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: borderColor),
                ),
                child: Column(
                  children: [
                    SwitchListTile(
                      value: _speedAlerts,
                      onChanged: (val) {
                        setState(() {
                          _speedAlerts = val;
                        });
                      },
                      activeColor: const Color(0xFF6366F1),
                      title: Text(
                        'Over-Speed Warnings',
                        style: TextStyle(color: textColor, fontSize: 14, fontWeight: FontWeight.bold),
                      ),
                      subtitle: Text(
                        'Audible alert when speed exceeds 50 km/h',
                        style: TextStyle(color: subtitleColor, fontSize: 11),
                      ),
                    ),
                    Divider(color: borderColor, height: 1),
                    SwitchListTile(
                      value: _shareLocation,
                      onChanged: (val) {
                        setState(() {
                          _shareLocation = val;
                        });
                      },
                      activeColor: const Color(0xFF6366F1),
                      title: Text(
                        'Live Location Sharing',
                        style: TextStyle(color: textColor, fontSize: 14, fontWeight: FontWeight.bold),
                      ),
                      subtitle: Text(
                        'Shares live coordinates with school admin and parents',
                        style: TextStyle(color: subtitleColor, fontSize: 11),
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 28),

              // Safe Driving Tips
              Text(
                'Safe Driving Checklist',
                style: TextStyle(
                  color: textColor,
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 12),

              _buildTipCard('Always wear seatbelt and ensure students are seated.', Icons.security_rounded, cardBg, borderColor, textColor, subtitleColor),
              _buildTipCard('Maintain speeds below 40 km/h in school residential areas.', Icons.slow_motion_video_rounded, cardBg, borderColor, textColor, subtitleColor),
              _buildTipCard('Conduct a visual vehicle review before departing.', Icons.fact_check_rounded, cardBg, borderColor, textColor, subtitleColor),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildContactTile(String name, String number, IconData icon, Color bg, Color border, Color text, Color subText) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: border),
      ),
      child: ListTile(
        leading: Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: const Color(0xFF6366F1).withOpacity(0.1),
            shape: BoxShape.circle,
          ),
          child: Icon(icon, color: const Color(0xFF818CF8), size: 20),
        ),
        title: Text(
          name,
          style: TextStyle(color: text, fontSize: 14, fontWeight: FontWeight.bold),
        ),
        subtitle: Text(
          number,
          style: TextStyle(color: subText, fontSize: 12),
        ),
        trailing: IconButton(
          icon: const Icon(Icons.phone_rounded, color: Color(0xFF10B981)),
          onPressed: () {},
        ),
      ),
    );
  }

  Widget _buildTipCard(String text, IconData icon, Color bg, Color border, Color textColor, Color subTextColor) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: border),
      ),
      child: Row(
        children: [
          Icon(icon, color: const Color(0xFF818CF8), size: 20),
          const SizedBox(width: 14),
          Expanded(
            child: Text(
              text,
              style: TextStyle(
                color: textColor,
                fontSize: 13,
                height: 1.4,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
