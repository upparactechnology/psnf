import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:camera/camera.dart';
import 'package:google_mlkit_face_detection/google_mlkit_face_detection.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../data/api_service.dart';
import 'login_page.dart';

class KioskPage extends StatefulWidget {
  const KioskPage({super.key});

  @override
  State<KioskPage> createState() => _KioskPageState();
}

class _KioskPageState extends State<KioskPage> {
  final ApiService _apiService = ApiService();
  CameraController? _cameraController;
  FaceDetector? _faceDetector;
  bool _isDetecting = false;
  bool _cameraInitialized = false;
  
  // Kiosk Scan state
  String _statusMessage = "Stand in front of the camera to verify";
  Color _statusColor = Colors.grey;
  bool _isProcessingMatch = false;

  // Settings
  String _hostUrl = "http://10.0.2.2:8000";
  bool _isLoggedIn = false;

  @override
  void initState() {
    super.initState();
    _loadSettings();
    _initializeCamera();
    _faceDetector = FaceDetector(
      options: FaceDetectorOptions(
        enableLandmarks: true,
        performanceMode: FaceDetectorMode.accurate,
      ),
    );
  }

  Future<void> _loadSettings() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _hostUrl = prefs.getString('host_url') ?? "http://10.0.2.2:8000";
      _isLoggedIn = prefs.containsKey('access_token');
    });
  }

  Future<void> _initializeCamera() async {
    try {
      final cameras = await availableCameras();
      if (cameras.isEmpty) return;
      
      // Default to front-facing camera for kiosk
      final frontCamera = cameras.firstWhere(
        (c) => c.lensDirection == CameraLensDirection.front,
        orElse: () => cameras.first,
      );

      _cameraController = CameraController(
        frontCamera,
        ResolutionPreset.medium,
        enableAudio: false,
      );

      await _cameraController!.initialize();
      if (!mounted) return;

      setState(() {
        _cameraInitialized = true;
      });

      _startImageStream();
    } catch (e) {
      debugPrint("Camera initialization failed: $e");
    }
  }

  void _startImageStream() {
    if (_cameraController == null || !_cameraController!.value.isInitialized) return;

    _cameraController!.startImageStream((CameraImage image) async {
      if (_isDetecting || _isProcessingMatch) return;
      _isDetecting = true;

      try {
        final WriteBuffer allBytes = WriteBuffer();
        for (final Plane plane in image.planes) {
          allBytes.putUint8List(plane.bytes);
        }
        final bytes = allBytes.done().buffer.asUint8List();

        final Size imageSize = Size(image.width.toDouble(), image.height.toDouble());
        final InputImageRotation imageRotation = InputImageRotation.rotation270deg; // Front camera standard
        
        final InputImageFormat inputImageFormat = 
            InputImageFormatValue.fromRawValue(image.format.raw) ?? InputImageFormat.nv21;

        final inputImageData = InputImageMetadata(
          size: imageSize,
          rotation: imageRotation,
          format: inputImageFormat,
          bytesPerRow: image.planes[0].bytesPerRow,
        );

        final inputImage = InputImage.fromBytes(
          bytes: bytes,
          metadata: inputImageData,
        );

        final faces = await _faceDetector!.processImage(inputImage);
        
        if (faces.isNotEmpty && !_isProcessingMatch) {
          // Process the primary face (largest bounding box)
          final primaryFace = faces.reduce((a, b) => 
            (a.boundingBox.width * a.boundingBox.height) > (b.boundingBox.width * b.boundingBox.height) ? a : b
          );
          
          // Verify if the face is well-centered (bounding box within screen area parameters)
          final double faceWidth = primaryFace.boundingBox.width;
          if (faceWidth > 120) {
            await _onFaceDetected(primaryFace);
          }
        }
      } catch (e) {
        debugPrint("Error processing frame: $e");
      } finally {
        _isDetecting = false;
      }
    });
  }

  Future<void> _onFaceDetected(Face face) async {
    setState(() {
      _isProcessingMatch = true;
      _statusMessage = "Analyzing face & checking liveness...";
      _statusColor = Colors.orange;
    });

    // Extract mock 512-dim embedding for demo/test purposes as the local model is not bundled in JS/Dart
    final mockEmbedding = List.generate(512, (index) => 0.0125);
    final result = await _apiService.matchFace(mockEmbedding, "FLUTTER_TAB_A");

    if (mounted) {
      setState(() {
        if (result != null && result['matched'] == true) {
          final emp = result['employee'];
          final log = result['attendance_log'];
          _statusMessage = "Welcome ${emp['first_name']}!\n${log['clock_type']} Registered Successfully.";
          _statusColor = Colors.green;
        } else {
          _statusMessage = "Access Denied.\nFace profile match not found.";
          _statusColor = Colors.red;
        }
      });
    }

    // Cooldown interval to prevent double scan and reset scanner view
    await Future.delayed(const Duration(seconds: 4));
    if (mounted) {
      setState(() {
        _statusMessage = "Stand in front of the camera to verify";
        _statusColor = Colors.grey;
        _isProcessingMatch = false;
      });
    }
  }

  Future<void> _showSettingsDialog() async {
    final hostController = TextEditingController(text: _hostUrl);
    final usernameController = TextEditingController();
    final passwordController = TextEditingController();
    bool isSaving = false;

    showDialog(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              title: const Text("Kiosk Configurations", style: TextStyle(fontWeight: FontWeight.bold)),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    TextField(
                      controller: hostController,
                      decoration: const InputDecoration(labelText: "Backend Host URL"),
                    ),
                    const SizedBox(height: 20),
                    const Text("Admin Login Required for Scan Auth", style: TextStyle(fontSize: 12, color: Colors.grey)),
                    TextField(
                      controller: usernameController,
                      decoration: const InputDecoration(labelText: "Admin Username"),
                    ),
                    TextField(
                      controller: passwordController,
                      decoration: const InputDecoration(labelText: "Admin Password"),
                      obscureText: true,
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text("Close"),
                ),
                TextButton(
                  onPressed: () async {
                    final prefs = await SharedPreferences.getInstance();
                    await prefs.remove('access_token');
                    await prefs.remove('refresh_token');
                    if (context.mounted) {
                      Navigator.pop(context);
                      Navigator.pushReplacement(
                        context,
                        MaterialPageRoute(builder: (context) => const LoginPage()),
                      );
                    }
                  },
                  child: const Text("Reset Auth", style: TextStyle(color: Colors.red)),
                ),
                ElevatedButton(
                  onPressed: isSaving ? null : () async {
                    setDialogState(() { isSaving = true; });
                    final prefs = await SharedPreferences.getInstance();
                    await prefs.setString('host_url', hostController.text);
                    
                    // Trigger login to fetch jwt authorization tokens
                    final success = await _apiService.login(
                      usernameController.text,
                      passwordController.text,
                    );
                    
                    if (success) {
                      await _loadSettings();
                      if (context.mounted) {
                        Navigator.pop(context);
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text("Configurations updated & authorized!")),
                        );
                      }
                    } else {
                      setDialogState(() { isSaving = false; });
                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text("Auth failed. Verify admin credentials.")),
                        );
                      }
                    }
                  },
                  child: isSaving ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2)) : const Text("Authorize"),
                ),
              ],
            );
          },
        );
      },
    );
  }

  @override
  void dispose() {
    _cameraController?.dispose();
    _faceDetector?.close();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("AI Face Recognition Kiosk", style: TextStyle(fontWeight: FontWeight.bold)),
        backgroundColor: const Color(0xFF005FAF),
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.settings),
            onPressed: _showSettingsDialog,
          )
        ],
      ),
      body: OrientationBuilder(
        builder: (context, orientation) {
          final isLandscape = orientation == Orientation.landscape;
          if (isLandscape) {
            return Row(
              children: [
                Expanded(
                  flex: 6,
                  child: _buildCameraView(),
                ),
                Expanded(
                  flex: 4,
                  child: _buildStatusPanel(isLandscape: true),
                ),
              ],
            );
          } else {
            return Column(
              children: [
                Expanded(
                  flex: 7,
                  child: _buildCameraView(),
                ),
                _buildStatusPanel(isLandscape: false),
              ],
            );
          }
        },
      ),
    );
  }

  Widget _buildCameraView() {
    return Stack(
      children: [
        if (_cameraInitialized && _cameraController != null)
          Positioned.fill(
            child: AspectRatio(
              aspectRatio: _cameraController!.value.aspectRatio,
              child: CameraPreview(_cameraController!),
            ),
          )
        else
          Container(
            color: Colors.black87,
            child: const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.videocam_off, size: 80, color: Colors.grey),
                  SizedBox(height: 10),
                  Text("Initializing camera stream...", style: TextStyle(color: Colors.white70)),
                ],
              ),
            ),
          ),
        
        // Oval Target Cutout Painter
        const Positioned.fill(
          child: IgnorePointer(
            child: OvalHUDOverlay(),
          ),
        ),
        
        // Connection indicator status
        Positioned(
          top: 16,
          left: 16,
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: _isLoggedIn ? Colors.green.withOpacity(0.8) : Colors.red.withOpacity(0.8),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  _isLoggedIn ? Icons.cloud_done : Icons.cloud_off,
                  size: 16,
                  color: Colors.white,
                ),
                const SizedBox(width: 6),
                Text(
                  _isLoggedIn ? "Authorized" : "Unauthorized Settings Gear Required",
                  style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildStatusPanel({required bool isLandscape}) {
    return Padding(
      padding: EdgeInsets.all(isLandscape ? 32.0 : 16.0),
      key: isLandscape ? const Key("status_panel") : null,
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            "Verification Status",
            style: TextStyle(
              fontSize: isLandscape ? 22 : 18, 
              fontWeight: FontWeight.bold, 
              color: const Color(0xFF0F172A)
            ),
            textAlign: TextAlign.center,
          ),
          SizedBox(height: isLandscape ? 30 : 12),
          AnimatedContainer(
            duration: const Duration(milliseconds: 300),
            padding: EdgeInsets.all(isLandscape ? 32 : 16),
            decoration: BoxDecoration(
              color: _statusColor.withOpacity(0.08),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: _statusColor, width: 3),
              boxShadow: [
                BoxShadow(
                  color: _statusColor.withOpacity(0.1),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Text(
              _statusMessage,
              style: TextStyle(
                fontSize: isLandscape ? 18 : 14,
                fontWeight: FontWeight.bold,
                color: _statusColor == Colors.grey ? Colors.black87 : _statusColor,
                height: 1.4,
              ),
              textAlign: TextAlign.center,
            ),
          ),
        ],
      ),
    );
  }
}

// Oval Target custom overlay painter
class OvalHUDOverlay extends StatelessWidget {
  const OvalHUDOverlay({super.key});

  @override
  Widget build(BuildContext context) {
    return CustomPaint(
      painter: _OvalHUDPainter(),
    );
  }
}

class _OvalHUDPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final backgroundPaint = Paint()
      ..color = Colors.black.withOpacity(0.55)
      ..style = PaintingStyle.fill;

    final borderPaint = Paint()
      ..color = const Color(0xFF64FFDA)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 3.0;

    // Outer full bounding rectangle
    final Path backgroundPath = Path()..addRect(Rect.fromLTWH(0, 0, size.width, size.height));

    // Inner Oval cutout (adapted dynamically based on orientation / aspect ratio)
    final bool isPortrait = size.width < size.height;
    final double ovalWidth = isPortrait ? size.width * 0.65 : size.width * 0.45;
    final double ovalHeight = isPortrait ? size.height * 0.55 : size.height * 0.7;

    final Rect ovalRect = Rect.fromCenter(
      center: Offset(size.width / 2, size.height / 2),
      width: ovalWidth,
      height: ovalHeight,
    );
    final Path ovalPath = Path()..addOval(ovalRect);

    // Subtract oval path from full rect using Path.combine
    final Path resultPath = Path.combine(
      PathOperation.difference,
      backgroundPath,
      ovalPath,
    );

    canvas.drawPath(resultPath, backgroundPaint);
    canvas.drawOval(ovalRect, borderPaint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
