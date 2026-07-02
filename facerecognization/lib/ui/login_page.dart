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
    
    final discoveredHost = await _apiService.discoverBackend();
    
    if (mounted) {
      if (discoveredHost != null) {
        await prefs.setString('host_url', discoveredHost);
        setState(() {
          _hostController.text = discoveredHost;
          _isDiscovering = false;
          _showHostField = false;
          _discoveryStatus = "Connected to $discoveredHost";
        });
      } else if (savedHost != null) {
        setState(() {
          _hostController.text = savedHost;
          _isDiscovering = false;
          _showHostField = false;
          _discoveryStatus = "Using saved: $savedHost";
        });
      } else {
        setState(() {
          _hostController.text = "http://192.168.1.5:8000";
          _isDiscovering = false;
          _showHostField = true;
          _discoveryStatus = "Server not found. Enter manually.";
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
          _errorMessage = "Authentication failed. Check credentials and server connection.";
          _showHostField = true;
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
      backgroundColor: Colors.white,
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24.0),
          child: Container(
            constraints: const BoxConstraints(maxWidth: 380),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Logo
                Center(
                  child: Container(
                    width: 56,
                    height: 56,
                    decoration: BoxDecoration(
                      color: const Color(0xFF111111),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: const Icon(Icons.face_retouching_natural, size: 30, color: Colors.white),
                  ),
                ),
                const SizedBox(height: 24),
                const Text(
                  "Sign in",
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF111111),
                    letterSpacing: -0.5,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 6),
                const Text(
                  "Authorize this device as a kiosk terminal",
                  style: TextStyle(color: Color(0xFF888888), fontSize: 13),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 12),
                
                // Discovery Status
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF7F7F7),
                    borderRadius: BorderRadius.circular(4),
                    border: Border.all(color: const Color(0xFFE5E5E5)),
                  ),
                  child: Row(
                    children: [
                      SizedBox(
                        width: 12,
                        height: 12,
                        child: _isDiscovering 
                          ? const CircularProgressIndicator(strokeWidth: 1.5, color: Color(0xFF111111))
                          : Icon(
                              _hostController.text.isNotEmpty ? Icons.check_circle : Icons.error_outline, 
                              size: 14, 
                              color: _hostController.text.isNotEmpty ? const Color(0xFF22C55E) : const Color(0xFFEF4444),
                            ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          _discoveryStatus,
                          style: const TextStyle(fontSize: 11, color: Color(0xFF666666)),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ),
                
                const SizedBox(height: 24),

                if (_errorMessage != null) ...[
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEF2F2),
                      borderRadius: BorderRadius.circular(4),
                      border: Border.all(color: const Color(0xFFFCA5A5)),
                    ),
                    child: Text(
                      _errorMessage!,
                      style: const TextStyle(color: Color(0xFFDC2626), fontSize: 13),
                      textAlign: TextAlign.center,
                    ),
                  ),
                  const SizedBox(height: 16),
                ],
                
                if (_showHostField) ...[
                  TextField(
                    controller: _hostController,
                    decoration: const InputDecoration(labelText: "Server URL"),
                  ),
                  const SizedBox(height: 14),
                ],
                
                TextField(
                  controller: _usernameController,
                  decoration: const InputDecoration(labelText: "Username"),
                ),
                const SizedBox(height: 14),
                TextField(
                  controller: _passwordController,
                  obscureText: true,
                  decoration: const InputDecoration(labelText: "Password"),
                ),
                const SizedBox(height: 28),
                ElevatedButton(
                  onPressed: (_isLoading || _isDiscovering) ? null : _handleLogin,
                  child: _isLoading
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                        )
                      : const Text("Authorize Tablet"),
                ),
                
                if (!_showHostField && !_isDiscovering) ...[
                  const SizedBox(height: 16),
                  Center(
                    child: TextButton(
                      onPressed: () {
                        setState(() {
                          _showHostField = true;
                        });
                      },
                      child: const Text(
                        "Configure server manually",
                        style: TextStyle(
                          color: Color(0xFF888888),
                          fontSize: 12,
                          decoration: TextDecoration.underline,
                        ),
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
