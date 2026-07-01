import 'dart:convert';
import 'package:workmanager/workmanager.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../data/local_db.dart';

void callbackDispatcher() {
  Workmanager().executeTask((task, inputData) async {
    final localDb = LocalDatabase.instance;
    final unsyncedLogs = await localDb.getUnsyncedLogs();

    if (unsyncedLogs.isEmpty) {
      return Future.value(true);
    }

    final prefs = await SharedPreferences.getInstance();
    final host = prefs.getString('host_url');
    final token = prefs.getString('access_token');

    if (host == null || token == null) {
      return Future.value(false);
    }

    final payload = unsyncedLogs.map((log) => {
      'employee_id': log['employeeId'],
      'clock_time': log['clockTime'],
      'clock_type': log['clockType'],
      'status': log['status'],
      'device_id': log['deviceId']
    }).toList();

    try {
      final response = await http.post(
        Uri.parse('$host/api/v1/attendance/sync'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token'
        },
        body: jsonEncode({'logs': payload}),
      );

      if (response.statusCode == 200) {
        for (var log in unsyncedLogs) {
          await localDb.markSynced(log['id'] as int);
        }
        return Future.value(true);
      }
      return Future.value(false);
    } catch (e) {
      return Future.value(false);
    }
  });
}
