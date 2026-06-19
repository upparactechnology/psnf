import 'package:flutter/material.dart';
import '../theme/colors.dart';
import 'login_screen.dart';

class SplashScreen extends StatelessWidget {
  const SplashScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF080D1A),
      body: Stack(
        children: [
          // Full-screen background image
          Positioned.fill(
            child: Image.network(
              'https://images.unsplash.com/photo-1557223562-6c77ef16210f?auto=format&fit=crop&w=800&q=80',
              fit: BoxFit.cover,
            ),
          ),
          // Dark gradient overlay to ensure text contrast and premium feel
          Positioned.fill(
            child: Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.black.withOpacity(0.35),
                    Colors.black.withOpacity(0.55),
                    const Color(0xFF080D1A).withOpacity(0.9),
                    const Color(0xFF080D1A),
                  ],
                  stops: const [0.0, 0.4, 0.8, 1.0],
                ),
              ),
            ),
          ),
          
          SafeArea(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const SizedBox(height: 20),
                  // Shield Badge Logo
                  Align(
                    alignment: Alignment.topLeft,
                    child: ClipPath(
                      clipper: ShieldClipper(),
                      child: Container(
                        width: 76,
                        height: 84,
                        decoration: const BoxDecoration(
                          gradient: LinearGradient(
                            begin: Alignment.topCenter,
                            end: Alignment.bottomCenter,
                            colors: [
                              Color(0xFF8B5CF6), // Violet
                              Color(0xFF6366F1), // Indigo
                            ],
                          ),
                        ),
                        child: const Center(
                          child: Icon(
                            Icons.directions_bus_filled_rounded,
                            color: Colors.white,
                            size: 34,
                          ),
                        ),
                      ),
                    ),
                  ),
                  const Spacer(flex: 3),
                  // Title text with "Smiles" highlighted in yellow
                  RichText(
                    text: const TextSpan(
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 38,
                        fontWeight: FontWeight.w800,
                        letterSpacing: -0.5,
                        height: 1.15,
                        fontFamily: 'Inter',
                      ),
                      children: [
                        TextSpan(text: 'Drive Safe,\nDeliver '),
                        TextSpan(
                          text: 'Smiles',
                          style: TextStyle(
                            color: Color(0xFFFBBF24), // Amber yellow
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),
                  // Subtitle
                  const Text(
                    'Smart Transport. Safe Students.',
                    style: TextStyle(
                      color: Color(0xFF94A3B8), // slate-400
                      fontSize: 16,
                      fontWeight: FontWeight.w400,
                    ),
                  ),
                  const SizedBox(height: 40),
                  // Buttons
                  ElevatedButton(
                    onPressed: () {
                      Navigator.pushReplacement(
                        context,
                        MaterialPageRoute(builder: (context) => const LoginScreen()),
                      );
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF7C3AED), // Vibrant purple
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 18),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                      elevation: 8,
                      shadowColor: const Color(0xFF7C3AED).withOpacity(0.4),
                    ),
                    child: const Text(
                      'Get Started',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  const SizedBox(height: 14),
                  // Login to Continue Button (Translucent dark button with chevron)
                  OutlinedButton(
                    onPressed: () {
                      Navigator.pushReplacement(
                        context,
                        MaterialPageRoute(builder: (context) => const LoginScreen()),
                      );
                    },
                    style: OutlinedButton.styleFrom(
                      backgroundColor: const Color(0xFF1E293B).withOpacity(0.4),
                      foregroundColor: Colors.white,
                      side: BorderSide(color: Colors.white.withOpacity(0.08)),
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 18),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                    ),
                    child: const Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Login to Continue',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        Icon(
                          Icons.chevron_right_rounded,
                          size: 20,
                          color: Color(0xFF94A3B8), // slate-400
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 10),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class ShieldClipper extends CustomClipper<Path> {
  @override
  Path getClip(Size size) {
    final path = Path();
    final w = size.width;
    final h = size.height;
    
    // Top center starting point
    path.moveTo(w * 0.5, 0);
    // Curve to top right
    path.quadraticBezierTo(w * 0.9, h * 0.02, w, h * 0.15);
    // Line down to side curve
    path.lineTo(w, h * 0.6);
    // Curve to bottom point
    path.quadraticBezierTo(w, h * 0.85, w * 0.5, h);
    // Curve back to left side
    path.quadraticBezierTo(0, h * 0.85, 0, h * 0.6);
    // Line up to top left
    path.lineTo(0, h * 0.15);
    // Curve to top center
    path.quadraticBezierTo(w * 0.1, h * 0.02, w * 0.5, 0);
    path.close();
    return path;
  }

  @override
  bool shouldReclip(covariant CustomClipper<Path> oldClipper) => false;
}
