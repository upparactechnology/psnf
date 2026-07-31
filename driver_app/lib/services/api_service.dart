import 'dart:convert';
import 'dart:io' show Platform;
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static String get baseUrl {
    if (kIsWeb) {
      return 'http://localhost/psnf/public';
    }
    try {
      if (Platform.isAndroid) {
        return 'http://192.168.29.240/psnf/public';
      }
    } catch (e) {
      // Platform check not supported
    }
    return 'http://localhost/psnf/public';
  }

  static String? token;
  static Map<String, dynamic>? assignedRoute;
  static String? driverName;
  static String? driverPhone;

  static Future<void> loadToken() async {
    final prefs = await SharedPreferences.getInstance();
    token = prefs.getString('auth_token');
    driverName = prefs.getString('driver_name');
    driverPhone = prefs.getString('driver_phone');
  }

  static Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('driver_name');
    await prefs.remove('driver_phone');
    token = null;
    driverName = null;
    driverPhone = null;
    assignedRoute = null;
  }

  static Future<Map<String, dynamic>> login(String name, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/v1/auth/driver-login'),
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'Accept': 'application/json'
        },
        body: {'name': name, 'password': password},
      );

      final data = json.decode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        token = data['token'];
        if (data['user'] != null) {
          driverName = data['user']['name'];
          driverPhone = data['user']['phone'];
        }
        
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_token', token!);
        if (driverName != null) await prefs.setString('driver_name', driverName!);
        if (driverPhone != null) await prefs.setString('driver_phone', driverPhone!);

        if (data['requires_password_change'] != true) {
          await fetchAssignedRoute();
        }
        return {'success': true, 'requires_password_change': data['requires_password_change']};
      } else {
        return {'success': false, 'message': data['message'] ?? 'Invalid name or password.'};
      }
    } catch (e) {
      return {'success': false, 'message': 'Network error: $e'};
    }
  }

  static Future<Map<String, dynamic>> changePassword(String newPassword) async {
    if (token == null) return {'success': false, 'message': 'Not authenticated'};
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/v1/auth/driver-change-password'),
        headers: {
          'Authorization': 'Bearer $token',
          'Content-Type': 'application/x-www-form-urlencoded',
          'Accept': 'application/json'
        },
        body: {
          'password': newPassword,
          'password_confirmation': newPassword,
        },
      );

      final data = json.decode(response.body);
      if (data['success'] == true) {
        await fetchAssignedRoute();
        return {'success': true};
      }
      return {'success': false, 'message': data['message'] ?? 'Failed to change password'};
    } catch (e) {
      return {'success': false, 'message': 'Network error: $e'};
    }
  }

  static Future<void> fetchAssignedRoute() async {
    if (token == null) return;
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/v1/driver/my-route'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );
      final data = json.decode(response.body);
      if (data['success'] == true && data['route'] != null) {
        assignedRoute = data['route'];
      } else {
        assignedRoute = null;
      }
    } catch (e) {
      print('Error fetching assigned route: $e');
      assignedRoute = null;
    }
  }

  static Future<bool> updateLocation(double lat, double lng, double speed) async {
    if (token == null || assignedRoute == null) return false;
    final routeId = assignedRoute!['id'];
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/v1/driver/$routeId/location'),
        headers: {
          'Authorization': 'Bearer $token',
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: json.encode({
          'lat': lat,
          'lng': lng,
          'speed': speed,
        }),
      );
      final data = json.decode(response.body);
      return data['success'] == true;
    } catch (e) {
      print('Error updating location: $e');
      return false;
    }
  }

  static Future<bool> startTrip() async {
    if (token == null) return false;
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/v1/driver/start-trip'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );
      final data = json.decode(response.body);
      if (data['success'] == true) {
        await fetchAssignedRoute();
        return true;
      }
      return false;
    } catch (e) {
      print('Error starting trip: $e');
      return false;
    }
  }

  static Future<bool> completeTrip() async {
    if (token == null) return false;
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/v1/driver/complete-trip'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );
      return json.decode(response.body)['success'] == true;
    } catch (e) {
      print('Error completing trip: $e');
      return false;
    }
  }

  static Future<bool> updateStudentStatus(int studentId, String status) async {
    if (token == null) return false;
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/v1/driver/update-status'),
        headers: {
          'Authorization': 'Bearer $token',
          'Content-Type': 'application/x-www-form-urlencoded',
          'Accept': 'application/json',
        },
        body: {
          'student_id': studentId.toString(),
          'status': status,
        },
      );
      final data = json.decode(response.body);
      if (data['success'] == true) {
        await fetchAssignedRoute();
        return true;
      }
      return false;
    } catch (e) {
      print('Error updating student status: $e');
      return false;
    }
  }

  static Future<Map<String, dynamic>?> fetchTripHistory(String date) async {
    if (token == null) return null;
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/v1/driver/trip-history?date=$date'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );
      if (response.statusCode == 401 || response.statusCode == 403) {
        print('Trip history auth error: ${response.statusCode}');
        return null;
      }
      if (response.statusCode != 200) {
        print('Trip history HTTP error: ${response.statusCode}');
        return null;
      }
      final data = json.decode(response.body);
      if (data['success'] == true) {
        return data as Map<String, dynamic>;
      }
      return null;
    } catch (e) {
      print('Error fetching trip history: $e');
      return null;
    }
  }
}
