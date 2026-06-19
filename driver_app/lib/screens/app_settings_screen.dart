import 'package:flutter/material.dart';
import '../theme/colors.dart';

class AppSettingsScreen extends StatefulWidget {
  const AppSettingsScreen({super.key});

  @override
  State<AppSettingsScreen> createState() => _AppSettingsScreenState();
}

class _AppSettingsScreenState extends State<AppSettingsScreen> {
  bool _sound = true;
  bool _vibration = true;
  bool _criticalAlerts = true;
  String _selectedLanguage = 'English';

  @override
  Widget build(BuildContext context) {
    final bool isDark = Theme.of(context).brightness == Brightness.dark;
    final Color textColor = isDark ? Colors.white : const Color(0xFF0F172A);
    final Color subtitleColor = isDark ? const Color(0xFF94A3B8) : const Color(0xFF475569);
    final Color cardBg = isDark ? const Color(0xFF1E293B).withOpacity(0.4) : Colors.white;
    final Color borderColor = isDark ? AppColors.slate.shade850 : const Color(0xFFE2E8F0);

    return Scaffold(
      appBar: AppBar(
        title: const Text('App Settings'),
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
              // Notification preferences card
              Text(
                'Notifications',
                style: TextStyle(
                  color: textColor,
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 12),
              Container(
                decoration: BoxDecoration(
                  color: cardBg,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: borderColor),
                ),
                child: Column(
                  children: [
                    SwitchListTile(
                      value: _sound,
                      onChanged: (val) {
                        setState(() {
                          _sound = val;
                        });
                      },
                      activeColor: const Color(0xFF6366F1),
                      title: Text(
                        'Notification Sounds',
                        style: TextStyle(color: textColor, fontSize: 14, fontWeight: FontWeight.bold),
                      ),
                      subtitle: Text(
                        'Play sound on new alerts or stops',
                        style: TextStyle(color: subtitleColor, fontSize: 11),
                      ),
                    ),
                    Divider(color: borderColor, height: 1),
                    SwitchListTile(
                      value: _vibration,
                      onChanged: (val) {
                        setState(() {
                          _vibration = val;
                        });
                      },
                      activeColor: const Color(0xFF6366F1),
                      title: Text(
                        'Vibration Feedback',
                        style: TextStyle(color: textColor, fontSize: 14, fontWeight: FontWeight.bold),
                      ),
                      subtitle: Text(
                        'Vibrate on stop entry and announcements',
                        style: TextStyle(color: subtitleColor, fontSize: 11),
                      ),
                    ),
                    Divider(color: borderColor, height: 1),
                    SwitchListTile(
                      value: _criticalAlerts,
                      onChanged: (val) {
                        setState(() {
                          _criticalAlerts = val;
                        });
                      },
                      activeColor: const Color(0xFF6366F1),
                      title: Text(
                        'Critical Alerts Override',
                        style: TextStyle(color: textColor, fontSize: 14, fontWeight: FontWeight.bold),
                      ),
                      subtitle: Text(
                        'Always play sound for critical safety announcements',
                        style: TextStyle(color: subtitleColor, fontSize: 11),
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 28),

              // Regional and Language Header
              Text(
                'Regional & Language',
                style: TextStyle(
                  color: textColor,
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 12),

              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                decoration: BoxDecoration(
                  color: cardBg,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: borderColor),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'App Language',
                          style: TextStyle(color: textColor, fontSize: 14, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Choose system display language',
                          style: TextStyle(color: subtitleColor, fontSize: 11),
                        ),
                      ],
                    ),
                    DropdownButton<String>(
                      value: _selectedLanguage,
                      dropdownColor: isDark ? const Color(0xFF0F172A) : Colors.white,
                      style: TextStyle(color: textColor, fontWeight: FontWeight.bold, fontSize: 14),
                      underline: const SizedBox(),
                      icon: Icon(Icons.arrow_drop_down_rounded, color: textColor),
                      items: <String>['English', 'Hindi', 'Gujarati'].map((String value) {
                        return DropdownMenuItem<String>(
                          value: value,
                          child: Text(value),
                        );
                      }).toList(),
                      onChanged: (val) {
                        if (val != null) {
                          setState(() {
                            _selectedLanguage = val;
                          });
                        }
                      },
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 28),

              // App Version
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: cardBg,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: borderColor),
                ),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Software Version',
                          style: TextStyle(color: textColor, fontSize: 13, fontWeight: FontWeight.w500),
                        ),
                        Text(
                          'v1.0.4 (Production)',
                          style: TextStyle(color: subtitleColor, fontSize: 13, fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Divider(color: borderColor, height: 1),
                    const SizedBox(height: 12),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Check for Updates',
                          style: TextStyle(color: textColor, fontSize: 13, fontWeight: FontWeight.w500),
                        ),
                        TextButton(
                          onPressed: () {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('App is up to date.')),
                            );
                          },
                          child: const Text(
                            'Check Now',
                            style: TextStyle(color: Color(0xFF818CF8), fontWeight: FontWeight.bold),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
