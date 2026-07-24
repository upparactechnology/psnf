import 'dart:convert';
import 'dart:io' show Platform;
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:http/http.dart' as http;

class ApiService {
  static String get baseUrl {
    if (kIsWeb) {
      return 'http://localhost/psnf/public';
    }
    try {
      if (Platform.isAndroid) {
        return 'http://10.0.2.2/psnf/public';
      }
    } catch (e) {
      // Platform check not supported
    }
    return 'http://localhost/psnf/public';
  }

  static String? token;
  static Map<String, dynamic>? userProfile;

  static Map<String, String> get headers {
    return {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  static Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/v1/auth/login'),
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'Accept': 'application/json'
        },
        body: {'email': email, 'password': password},
      );

      final data = json.decode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        token = data['token'];
        userProfile = data['user'];
        return {'success': true};
      } else {
        return {'success': false, 'message': data['message'] ?? 'Invalid email or password.'};
      }
    } catch (e) {
      return {'success': false, 'message': 'Network error: $e'};
    }
  }

  static Future<Map<String, dynamic>> getTodayAttendance() async {
    if (token == null) return {'success': false, 'message': 'Unauthorized'};
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/v1/staff/attendance/today'),
        headers: headers,
      );
      return json.decode(response.body);
    } catch (e) {
      return {'success': false, 'message': '$e'};
    }
  }

  static Future<Map<String, dynamic>> checkIn() async {
    if (token == null) return {'success': false, 'message': 'Unauthorized'};
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/v1/staff/attendance/check-in'),
        headers: headers,
      );
      return json.decode(response.body);
    } catch (e) {
      return {'success': false, 'message': '$e'};
    }
  }

  static Future<Map<String, dynamic>> checkOut() async {
    if (token == null) return {'success': false, 'message': 'Unauthorized'};
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/v1/staff/attendance/check-out'),
        headers: headers,
      );
      return json.decode(response.body);
    } catch (e) {
      return {'success': false, 'message': '$e'};
    }
  }

  static Future<Map<String, dynamic>> getAttendanceHistory() async {
    if (token == null) return {'success': false, 'message': 'Unauthorized'};
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/v1/staff/attendance/history'),
        headers: headers,
      );
      return json.decode(response.body);
    } catch (e) {
      return {'success': false, 'message': '$e'};
    }
  }

  static Future<Map<String, dynamic>> getEarlyStudents() async {
    if (token == null) return {'success': false, 'message': 'Unauthorized'};
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/v1/staff/early-students'),
        headers: headers,
      );
      return json.decode(response.body);
    } catch (e) {
      return {'success': false, 'message': '$e'};
    }
  }

  static Future<Map<String, dynamic>> getSummary() async {
    if (token == null) return {'success': false, 'message': 'Unauthorized'};
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/v1/staff/summary'),
        headers: headers,
      );
      return json.decode(response.body);
    } catch (e) {
      return {'success': false, 'message': '$e'};
    }
  }

  static Future<Map<String, dynamic>> getGuardians() async {
    if (token == null) return {'success': false, 'message': 'Unauthorized'};
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/v1/staff/guardians'),
        headers: headers,
      );
      return json.decode(response.body);
    } catch (e) {
      return {'success': false, 'message': '$e'};
    }
  }

  static Future<Map<String, dynamic>> getStudents() async {
    if (token == null) return {'success': false, 'message': 'Unauthorized'};
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/v1/students'),
        headers: headers,
      );
      // Wait, student index responds directly with paginated students
      final data = json.decode(response.body);
      if (data['success'] == true) {
        return data;
      }
      return {'success': false, 'message': 'Failed to load students'};
    } catch (e) {
      return {'success': false, 'message': '$e'};
    }
  }
}
