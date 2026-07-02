import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../data/api_service.dart';
import 'kiosk_page.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final ApiService _apiService = ApiService();
  final _hostController = TextEditingController();
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();
  
  bool _isLoading = false;
  bool _isDiscovering = false;
  bool _showHostField = false;
  String _discoveryStatus = "Searching for server...";
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _startAutoDiscovery();
  }

  Future<void> _startAutoDiscovery() async {
    setState(() {
      _isDiscovering = true;
      _errorMessage = null;
      _discoveryStatus = "Auto-discovering server on Wi-Fi...";
    });
    
    final prefs = await SharedPreferences.getInstance();
    final savedHost = prefs.getString('host_url');
    
    // Probe local Wi-Fi network subnet
    final discoveredHost = await _apiService.discoverBackend();
    
    if (mounted) {
      if (discoveredHost != null) {
        await prefs.setString('host_url', discoveredHost);
        setState(() {
          _hostController.text = discoveredHost;
          _isDiscovering = false;
          _showHostField = false;
          _discoveryStatus = "Auto-connected to $discoveredHost";
        });
      } else if (savedHost != null) {
        setState(() {
          _hostController.text = savedHost;
          _isDiscovering = false;
          _showHostField = false;
          _discoveryStatus = "Linked: $savedHost (Last Active)";
        });
      } else {
        setState(() {
          _hostController.text = "http://192.168.1.5:8000";
          _isDiscovering = false;
          _showHostField = true;
          _discoveryStatus = "Auto-discovery offline. Use manual entry.";
        });
      }
    }
  }

  Future<void> _handleLogin() async {
    if (_hostController.text.trim().isEmpty) {
      await _startAutoDiscovery();
    }

    final host = _hostController.text.trim();
    final username = _usernameController.text.trim();
    final password = _passwordController.text;

    if (host.isEmpty || username.isEmpty || password.isEmpty) {
      setState(() {
        _errorMessage = "Please fill in all fields.";
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('host_url', host);

    final success = await _apiService.login(username, password);

    if (mounted) {
      setState(() {
        _isLoading = false;
      });

      if (success) {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (context) => const KioskPage()),
        );
      } else {
        setState(() {
          _errorMessage = "Authentication failed. Check credentials and router Wi-Fi.";
          _showHostField = true; // Always display IP field on failure for easy manual verification
        });
      }
    }
  }

  @override
  void dispose() {
    _hostController.dispose();
    _usernameController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F172A),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24.0),
          child: Container(
            constraints: const BoxConstraints(maxWidth: 400),
            padding: const EdgeInsets.all(32.0),
            decoration: BoxDecoration(
              color: const Color(0xFF1E293B),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: Colors.white12),
              boxShadow: const [
                BoxShadow(
                  color: Colors.black26,
                  blurRadius: 10,
                  offset: Offset(0, 4),
                )
              ],
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Icon(
                  Icons.lock_person,
                  size: 64,
                  color: Color(0xFF64FFDA),
                ),
                const SizedBox(height: 16),
                const Text(
                  "Kiosk Auth Console",
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 8),
                const Text(
                  "Enter admin credentials to authorize this tablet",
                  style: TextStyle(color: Colors.white54, fontSize: 13),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 16),
                
                // Discovery Status Badge
                Center(
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: _isDiscovering 
                          ? Colors.blue.withOpacity(0.08) 
                          : (_hostController.text.isNotEmpty ? Colors.green.withOpacity(0.08) : Colors.orange.withOpacity(0.08)),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: _isDiscovering 
                            ? Colors.blue.withOpacity(0.2) 
                            : (_hostController.text.isNotEmpty ? Colors.green.withOpacity(0.2) : Colors.orange.withOpacity(0.2)),
                      ),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        SizedBox(
                          width: 10,
                          height: 10,
                          child: _isDiscovering 
                            ? const CircularProgressIndicator(strokeWidth: 1.5, valueColor: AlwaysStoppedAnimation<Color>(Colors.blue))
                            : Icon(
                                _hostController.text.isNotEmpty ? Icons.wifi : Icons.wifi_off, 
                                size: 12, 
                                color: _hostController.text.isNotEmpty ? Colors.greenAccent : Colors.orangeAccent
                              ),
                        ),
                        const SizedBox(width: 8),
                        Flexible(
                          child: Text(
                            _discoveryStatus,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              color: _isDiscovering 
                                  ? Colors.blueAccent 
                                  : (_hostController.text.isNotEmpty ? Colors.greenAccent : Colors.orangeAccent),
                              fontSize: 10,
                              fontWeight: FontWeight.bold
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                
                const SizedBox(height: 24),
                if (_errorMessage != null) ...[
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.red.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: Colors.red.withOpacity(0.3)),
                    ),
                    child: Text(
                      _errorMessage!,
                      style: const TextStyle(color: Colors.redAccent, fontSize: 13),
                      textAlign: TextAlign.center,
                    ),
                  ),
                  const SizedBox(height: 16),
                ],
                
                if (_showHostField) ...[
                  TextField(
                    controller: _hostController,
                    style: const TextStyle(color: Colors.white),
                    decoration: const InputDecoration(
                      labelText: "Backend Host URL",
                      labelStyle: TextStyle(color: Colors.white70),
                      enabledBorder: UnderlineInputBorder(
                        borderSide: BorderSide(color: Colors.white24),
                      ),
                      focusedBorder: UnderlineInputBorder(
                        borderSide: BorderSide(color: Color(0xFF64FFDA)),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                ],
                
                TextField(
                  controller: _usernameController,
                  style: const TextStyle(color: Colors.white),
                  decoration: const InputDecoration(
                    labelText: "Admin Username",
                    labelStyle: TextStyle(color: Colors.white70),
                    enabledBorder: UnderlineInputBorder(
                      borderSide: BorderSide(color: Colors.white24),
                    ),
                    focusedBorder: UnderlineInputBorder(
                      borderSide: BorderSide(color: Color(0xFF64FFDA)),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: _passwordController,
                  style: const TextStyle(color: Colors.white),
                  obscureText: true,
                  decoration: const InputDecoration(
                    labelText: "Admin Password",
                    labelStyle: TextStyle(color: Colors.white70),
                    enabledBorder: UnderlineInputBorder(
                      borderSide: BorderSide(color: Colors.white24),
                    ),
                    focusedBorder: UnderlineInputBorder(
                      borderSide: BorderSide(color: Color(0xFF64FFDA)),
                    ),
                  ),
                ),
                const SizedBox(height: 32),
                ElevatedButton(
                  onPressed: (_isLoading || _isDiscovering) ? null : _handleLogin,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF64FFDA),
                    foregroundColor: const Color(0xFF0F172A),
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                  child: _isLoading
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            valueColor: AlwaysStoppedAnimation<Color>(Color(0xFF0F172A)),
                          ),
                        )
                      : const Text(
                          "Authorize Tablet",
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                        ),
                ),
                
                // Manual Config Trigger
                if (!_showHostField && !_isDiscovering) ...[
                  const SizedBox(height: 16),
                  TextButton(
                    onPressed: () {
                      setState(() {
                        _showHostField = true;
                      });
                    },
                    child: const Text(
                      "Configure IP Address Manually",
                      style: TextStyle(
                        color: Color(0xFF64FFDA),
                        fontSize: 12,
                        decoration: TextDecoration.underline
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}
