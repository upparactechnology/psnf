import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  Future<String?> getBaseUrl() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('host_url');
  }

  Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('access_token');
  }

  Future<bool> login(String username, String password) async {
    final host = await getBaseUrl();
    if (host == null) return false;

    try {
      final response = await http.post(
        Uri.parse('$host/api/v1/auth/login'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'username': username, 'password': password}),
      );

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
    final token = await getToken();
    if (host == null || token == null) return null;

    try {
      final response = await http.post(
        Uri.parse('$host/api/v1/recognition/match'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token'
        },
        body: jsonEncode({
          'embedding': embedding,
          'timestamp': DateTime.now().toUtc().toIso8601String(),
          'device_id': deviceId,
          'liveness_score': 0.95
        }),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
      return null;
    } catch (e) {
      return null;
    }
  }
}
