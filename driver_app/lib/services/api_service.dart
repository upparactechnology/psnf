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
        return 'http://192.168.1.5/psnf/public';
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
        if (data['user'] != null) {
          driverName = data['user']['name'];
          driverPhone = data['user']['phone'];
        }
        await fetchAssignedRoute();
        return {'success': true};
      } else {
        return {'success': false, 'message': data['message'] ?? 'Invalid email or password.'};
      }
    } catch (e) {
      return {'success': false, 'message': 'Network error: $e'};
    }
  }

  static Future<void> fetchAssignedRoute() async {
    if (token == null) return;
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/transport/live-data'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );
      final data = json.decode(response.body);
      if (data['success'] == true && data['routes'] != null) {
        final routes = data['routes'] as List;
        if (routes.isNotEmpty) {
          try {
            assignedRoute = routes.firstWhere(
              (r) => (driverName != null && r['driver'] == driverName) || 
                     (driverPhone != null && r['phone'] == driverPhone)
            );
          } catch (e) {
            assignedRoute = null;
          }
        }
      }
    } catch (e) {
      print('Error fetching assigned route: $e');
    }
  }

  static Future<bool> updateLocation(double lat, double lng, double speed) async {
    if (token == null || assignedRoute == null) return false;
    final routeId = assignedRoute!['id'];
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/transport/$routeId/location'),
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

  static Future<bool> updateRouteStatus(String status) async {
    if (token == null || assignedRoute == null) return false;
    final routeId = assignedRoute!['id'];
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/transport/$routeId'),
        headers: {
          'Authorization': 'Bearer $token',
          'Content-Type': 'application/x-www-form-urlencoded',
          'Accept': 'application/json',
        },
        body: {
          'route_name': assignedRoute!['name'],
          'bus_number': assignedRoute!['bus'],
          'driver_name': assignedRoute!['driver'],
          'driver_phone': assignedRoute!['phone'],
          'status': status,
        },
      );
      await fetchAssignedRoute();
      return true;
    } catch (e) {
      print('Error updating route status: $e');
      return false;
    }
  }
}
