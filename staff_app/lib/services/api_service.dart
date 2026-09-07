import 'dart:convert';
import 'dart:io' show Platform;
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static String overrideBaseUrl = '';
  static String get baseUrl {
    if (overrideBaseUrl.isNotEmpty) return overrideBaseUrl;
    if (kIsWeb) {
      return 'http://localhost/psnf/public';
    }
    try {
      if (Platform.isAndroid) {
        return 'http://192.168.10.237/psnf/public/';
      }
    } catch (e) {
      // Platform check not supported
    }
    return 'http://localhost/psnf/public';
  }

  static String? token;
  static Map<String, dynamic>? userProfile;

  static Future<void> loadPersistedAuth() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      token = prefs.getString('auth_token');
      final profileStr = prefs.getString('user_profile');
      if (profileStr != null) {
        userProfile = json.decode(profileStr);
      }
    } catch (e) {
      // Ignore load error
    }
  }

  static Future<void> clearPersistedAuth() async {
    token = null;
    userProfile = null;
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('auth_token');
      await prefs.remove('user_profile');
    } catch (e) {
      // Ignore save error
    }
  }

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
      ).timeout(const Duration(seconds: 10), onTimeout: () {
        throw 'Connection timed out. Ensure your phone is on the same WiFi network as your PC and Windows Firewall allows Port 80.';
      });


      final data = json.decode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        token = data['token'];
        userProfile = data['user'];
        
        // Persist
        final prefs = await SharedPreferences.getInstance();
        if (token != null) await prefs.setString('auth_token', token!);
        if (userProfile != null) await prefs.setString('user_profile', json.encode(userProfile));
        
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

  static Future<Map<String, dynamic>> getClassAttendance() async {
    if (token == null) return {'success': false, 'message': 'Unauthorized'};
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/v1/staff/class-attendance'),
        headers: headers,
      );
      return json.decode(response.body);
    } catch (e) {
      return {'success': false, 'message': '$e'};
    }
  }

  static Future<Map<String, dynamic>> markStudentAttendance(int studentId, String status) async {
    if (token == null) return {'success': false, 'message': 'Unauthorized'};
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/v1/staff/attendance/mark-student'),
        headers: headers,
        body: json.encode({'student_id': studentId, 'status': status}),
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

  static Future<Map<String, dynamic>> approveEarlyPickup({
    required int studentId,
    required String guardianName,
    required String relationship,
    String notes = '',
  }) async {
    if (token == null) return {'success': false, 'message': 'Unauthorized'};
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/v1/staff/early-pickup/approve'),
        headers: headers,
        body: json.encode({
          'student_id': studentId,
          'guardian_name': guardianName,
          'relationship': relationship,
          'notes': notes,
        }),
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
        Uri.parse('$baseUrl/api/v1/staff/early-pickup'),
        headers: headers,
      );
      return json.decode(response.body);
    } catch (e) {
      return {'success': false, 'message': '$e'};
    }
  }
}
