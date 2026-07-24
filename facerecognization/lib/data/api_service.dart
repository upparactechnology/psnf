import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  // CONFIGURATION: Set your backend API server IP and port here
  static const String serverIp = "127.0.0.1";
  static const String serverPort = "8000";

  Future<String?> getBaseUrl() async {
    final prefs = await SharedPreferences.getInstance();
    final host = prefs.getString('host_url');
    if (host != null && host.isNotEmpty) {
      return host;
    }
    return "http://$serverIp:$serverPort";
  }

  Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('access_token');
  }

  Future<String?> _checkHealth(String ip) async {
    final url = "http://$ip:8000/api/v1/health";
    try {
      final res = await http.get(Uri.parse(url)).timeout(const Duration(milliseconds: 1000));
      if (res.statusCode == 200) {
        return "http://$ip:8000";
      }
    } catch (_) {}
    return null;
  }

  Future<String?> discoverBackend() async {
    try {
      final interfaces = await NetworkInterface.list(
        includeLinkLocal: false,
        type: InternetAddressType.IPv4,
      );
      
      if (interfaces.isEmpty) return null;
      
      for (var interface in interfaces) {
        for (var addr in interface.addresses) {
          final ip = addr.address;
          if (ip.startsWith('127.') || ip.startsWith('169.254')) continue;
          
          final parts = ip.split('.');
          if (parts.length != 4) continue;
          
          final subnetPrefix = "${parts[0]}.${parts[1]}.${parts[2]}";
          
          // Probe all 254 subnet hosts in parallel
          final List<Future<String?>> tasks = [];
          for (int i = 1; i <= 254; i++) {
            tasks.add(_checkHealth("$subnetPrefix.$i"));
          }
          
          final results = await Future.wait(tasks);
          for (var match in results) {
            if (match != null) {
              return match;
            }
          }
        }
      }
    } catch (_) {}
    return null;
  }

  Future<bool> login(String username, String password) async {
    final host = await getBaseUrl();
    if (host == null) return false;

    try {
      final response = await http.post(
        Uri.parse('$host/api/v1/auth/login'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'username': username, 'password': password}),
      ).timeout(const Duration(seconds: 5));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('access_token', data['access_token']);
        await prefs.setString('refresh_token', data['refresh_token']);
        return true;
      }
      return false;
    } catch (e) {
      return false;
    }
  }

  Future<Map<String, dynamic>?> matchFace(List<double> embedding, String deviceId) async {
    final host = await getBaseUrl();
    if (host == null) return null;

    final token = await getToken();

    try {
      final headers = {
        'Content-Type': 'application/json',
      };
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }

      final response = await http.post(
        Uri.parse('$host/api/v1/recognition/match'),
        headers: headers,
        body: jsonEncode({
          'embedding': embedding,
          'timestamp': DateTime.now().toUtc().toIso8601String(),
          'device_id': deviceId,
          'liveness_score': 0.95
        }),
      ).timeout(const Duration(seconds: 5));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  Future<Map<String, dynamic>?> verifyImage(String imagePath) async {
    final host = await getBaseUrl();
    if (host == null) {
      return {'error': true, 'detail': 'No host URL configured. Open Settings (gear icon) to set Backend Host URL.'};
    }

    try {
      final uri = Uri.parse('$host/api/v1/recognition/verify-image');
      final request = http.MultipartRequest('POST', uri);
      request.files.add(await http.MultipartFile.fromPath('file', imagePath));

      final streamedResponse = await request.send().timeout(const Duration(seconds: 15));
      final response = await http.Response.fromStream(streamedResponse).timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
      // Server returned an error — extract the detail message
      try {
        final body = jsonDecode(response.body);
        return {'error': true, 'detail': body['detail'] ?? 'Server error (${response.statusCode})'};
      } catch (_) {
        return {'error': true, 'detail': 'Server error (${response.statusCode})'};
      }
    } catch (e) {
      return {'error': true, 'detail': 'Connection failed: $e'};
    }
  }

  Future<Map<String, dynamic>?> registerWithFace({
    required String employeeId,
    required String firstName,
    required String lastName,
    required String email,
    required String phone,
    required String imagePath,
  }) async {
    final host = await getBaseUrl();
    final token = await getToken();
    if (host == null) {
      return {'error': 'No host URL configured. Open Settings (gear icon) to set Backend Host URL.'};
    }
    if (token == null) {
      return {'error': 'Not authorized. Open Settings (gear icon) and login with Admin credentials first.'};
    }

    try {
      final uri = Uri.parse('$host/api/v1/employees/register-with-face');
      final request = http.MultipartRequest('POST', uri);

      // Add headers
      request.headers['Authorization'] = 'Bearer $token';

      // Add text fields
      request.fields['employee_id'] = employeeId;
      request.fields['first_name'] = firstName;
      request.fields['last_name'] = lastName;
      request.fields['email'] = email;
      request.fields['phone'] = phone;

      // Add image file
      request.files.add(await http.MultipartFile.fromPath('file', imagePath));

      final streamedResponse = await request.send().timeout(const Duration(seconds: 15));
      final response = await http.Response.fromStream(streamedResponse).timeout(const Duration(seconds: 15));

      if (response.statusCode == 201) {
        return jsonDecode(response.body);
      } else if (response.statusCode == 401 || response.statusCode == 403) {
        return {'error': 'Authorization expired. Open Settings and re-login with Admin credentials.'};
      } else {
        try {
          final err = jsonDecode(response.body);
          return {'error': err['detail'] ?? 'Registration failed (${response.statusCode})'};
        } catch (_) {
          return {'error': 'Registration failed (${response.statusCode})'};
        }
      }
    } catch (e) {
      return {'error': 'Network connection failed: $e'};
    }
  }
}
