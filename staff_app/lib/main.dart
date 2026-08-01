import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'screens/login_screen.dart';
import 'screens/navigation_container.dart';
import 'services/api_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  SystemChrome.setSystemUIOverlayStyle(const SystemUiOverlayStyle(
    statusBarColor: Colors.transparent,
    statusBarIconBrightness: Brightness.light,
    systemNavigationBarColor: Colors.white,
    systemNavigationBarIconBrightness: Brightness.dark,
  ));
  await ApiService.loadPersistedAuth();
  runApp(const StaffApp());
}

class StaffApp extends StatelessWidget {
  const StaffApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'PSNF Staff ERP',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        brightness: Brightness.light,
        scaffoldBackgroundColor: const Color(0xFFF4F6F5),
        primaryColor: const Color(0xFF0A5C36),
        colorScheme: const ColorScheme.light(
          primary: Color(0xFF0A5C36),
          secondary: Color(0xFF063D23),
          background: Color(0xFFF4F6F5),
          surface: Colors.white,
          onSurface: Color(0xFF1E293B),
          error: Color(0xFFC5221F),
        ),
        cardTheme: CardThemeData(
          color: Colors.white,
          elevation: 0,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(20),
            side: const BorderSide(color: Color(0xFFE2E8F0), width: 1),
          ),
        ),
        appBarTheme: const AppBarTheme(
          backgroundColor: Colors.transparent,
          elevation: 0,
          centerTitle: true,
          iconTheme: IconThemeData(color: Color(0xFF1E293B)),
          titleTextStyle: TextStyle(
            color: Color(0xFF1E293B),
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
        bottomNavigationBarTheme: const BottomNavigationBarThemeData(
          backgroundColor: Colors.white,
          selectedItemColor: Color(0xFF0A5C36),
          unselectedItemColor: Color(0xFF94A3B8),
          selectedLabelStyle: TextStyle(fontWeight: FontWeight.w600, fontSize: 12),
          unselectedLabelStyle: TextStyle(fontWeight: FontWeight.normal, fontSize: 12),
          type: BottomNavigationBarType.fixed,
          elevation: 8,
        ),
      ),
      initialRoute: ApiService.token != null ? '/home' : '/login',
      routes: {
        '/login': (context) => const LoginScreen(),
        '/home': (context) => NavigationContainer(key: NavigationContainer.navKey),
      },
    );
  }
}
