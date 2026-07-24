import 'dart:async';
import 'dart:io';
import 'dart:typed_data';
import 'dart:ui';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:camera/camera.dart';
import 'package:google_mlkit_face_detection/google_mlkit_face_detection.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../data/api_service.dart';
import 'login_page.dart';
import 'register_page.dart';

class KioskPage extends StatefulWidget {
  const KioskPage({super.key});

  @override
  State<KioskPage> createState() => _KioskPageState();
}

class _KioskPageState extends State<KioskPage> with SingleTickerProviderStateMixin {
  final ApiService _apiService = ApiService();
  CameraController? _cameraController;
  FaceDetector? _faceDetector;
  bool _isDetecting = false;
  bool _cameraInitialized = false;
  String? _cameraError;
  
  // Kiosk Scan state
  String _statusMessage = "Stand in front of the camera to verify";
  Color _statusColor = Colors.grey;
  bool _isProcessingMatch = false;
  Map<String, dynamic>? _matchResult;
  bool _isFacePresent = kIsWeb;
  Timer? _faceGoneTimer;
  AnimationController? _scanLineController;

  // Settings
  String _hostUrl = "http://192.168.1.10:8000";
  bool _isLoggedIn = false;

  // Flash and Flip Camera state
  bool _isFrontCamera = true;
  bool _flashOn = false;

  @override
  void initState() {
    super.initState();
    _loadSettings();
    _initializeCamera();
    if (!kIsWeb) {
      _faceDetector = FaceDetector(
        options: FaceDetectorOptions(
          enableLandmarks: true,
          performanceMode: FaceDetectorMode.accurate,
        ),
      );
    }
    _scanLineController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 2),
    )..repeat(reverse: true);
  }

  @override
  void dispose() {
    _scanLineController?.dispose();
    _faceGoneTimer?.cancel();
    _cameraController?.dispose();
    _faceDetector?.close();
    super.dispose();
  }

  Future<void> _loadSettings() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _hostUrl = prefs.getString('host_url') ?? "http://192.168.1.10:8000";
      _isLoggedIn = prefs.containsKey('access_token');
    });
  }

  Future<void> _initializeCamera() async {
    try {
      setState(() {
        _cameraError = null;
      });
      final cameras = await availableCameras();
      if (cameras.isEmpty) {
        setState(() {
          _cameraError = "No cameras detected on this device.";
        });
        return;
      }
      
      final targetLens = _isFrontCamera ? CameraLensDirection.front : CameraLensDirection.back;
      final selectedCamera = cameras.firstWhere(
        (c) => c.lensDirection == targetLens,
        orElse: () => cameras.first,
      );

      _cameraController = CameraController(
        selectedCamera,
        ResolutionPreset.medium,
        enableAudio: false,
        imageFormatGroup: kIsWeb
            ? null
            : (Platform.isAndroid ? ImageFormatGroup.nv21 : ImageFormatGroup.bgra8888),
      );

      await _cameraController!.initialize();
      if (!mounted) return;

      try {
        await _cameraController!.setFlashMode(FlashMode.off);
        _flashOn = false;
      } catch (_) {}

      setState(() {
        _cameraInitialized = true;
      });

      if (!kIsWeb) {
        _startImageStream();
      }
    } catch (e) {
      debugPrint("Camera initialization failed: $e");
      if (mounted) {
        setState(() {
          _cameraError = e.toString();
        });
      }
    }
  }

  Future<void> _toggleFlash() async {
    if (_cameraController == null || !_cameraController!.value.isInitialized) return;
    try {
      final newMode = _flashOn ? FlashMode.off : FlashMode.torch;
      await _cameraController!.setFlashMode(newMode);
      setState(() {
        _flashOn = !_flashOn;
      });
    } catch (e) {
      debugPrint("Flash toggle failed: $e");
    }
  }

  Future<void> _flipCamera() async {
    if (_cameraController != null) {
      await _cameraController!.dispose();
      _cameraController = null;
    }
    setState(() {
      _isFrontCamera = !_isFrontCamera;
      _cameraInitialized = false;
    });
    await _initializeCamera();
  }

  Future<void> _startImageStream() async {
    if (kIsWeb) return;
    if (_cameraController == null || !_cameraController!.value.isInitialized) return;
    
    // Stop the image stream first if the controller reports it is streaming,
    // to prevent any inconsistent/stuck state in the camera plugin.
    if (_cameraController!.value.isStreamingImages) {
      try {
        await _cameraController!.stopImageStream();
      } catch (e) {
        debugPrint("Error stopping image stream before restart: $e");
      }
    }

    try {
      await _cameraController!.startImageStream((CameraImage image) async {
        if (_isDetecting || _isProcessingMatch) return;
        _isDetecting = true;

        try {
          final bytes = yuv420ToNv21(image);

          final Size imageSize = Size(image.width.toDouble(), image.height.toDouble());
          final InputImageRotation imageRotation = InputImageRotation.rotation270deg; // Front camera standard
          
          final InputImageFormat inputImageFormat = image.planes.length < 3
              ? (InputImageFormatValue.fromRawValue(image.format.raw) ?? InputImageFormat.nv21)
              : (Platform.isAndroid ? InputImageFormat.nv21 : InputImageFormat.bgra8888);

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
          
          // Find the primary face (largest bounding box) if any faces are detected
          Face? primaryFace;
          if (faces.isNotEmpty) {
            primaryFace = faces.reduce((a, b) => 
              (a.boundingBox.width * a.boundingBox.height) > (b.boundingBox.width * b.boundingBox.height) ? a : b
            );
          }

          // We consider a face "present" only if a face is detected and it is close enough to verify
          final bool hasValidFace = primaryFace != null && primaryFace.boundingBox.width > 120;

          if (hasValidFace) {
            _faceGoneTimer?.cancel();
            if (!_isFacePresent) {
              setState(() {
                _isFacePresent = true;
              });
            }
            
            if (!_isProcessingMatch) {
              await _onFaceDetected(primaryFace);
            }
          } else {
            // No valid close face detected
            if (!_isProcessingMatch && _isFacePresent && (_faceGoneTimer == null || !_faceGoneTimer!.isActive)) {
              _faceGoneTimer = Timer(const Duration(seconds: 10), () {
                if (mounted) {
                  setState(() {
                    _isFacePresent = false;
                  });
                }
              });
            }
          }
        } catch (e) {
          debugPrint("Error processing frame: $e");
        } finally {
          _isDetecting = false;
        }
      });
    } catch (e) {
      debugPrint("Error starting image stream: $e");
    }
  }

  Future<void> _onFaceDetected([Face? face]) async {
    _faceGoneTimer?.cancel(); // Cancel any pending face-gone timers
    setState(() {
      _isProcessingMatch = true;
      _statusMessage = "Face detected! Snapping photo...";
      _statusColor = Colors.orange;
    });

    try {
      // 1. Stop image stream so we can take picture without camera resource conflicts
      if (!kIsWeb && _cameraController!.value.isStreamingImages) {
        await _cameraController!.stopImageStream();
      }

      // 2. Take the picture
      final XFile file = await _cameraController!.takePicture();

      setState(() {
        _statusMessage = "Analyzing face pattern on server...";
      });

      // 3. Send image to backend for actual face embedding extraction and matching
      final result = await _apiService.verifyImage(file.path);

      if (mounted) {
        setState(() {
          if (result != null && result['matched'] == true) {
            _matchResult = result;
            final emp = result['employee'];
            final log = result['attendance_log'];
            
            // Format time nicely (e.g. 05:43 PM)
            String formattedTime = "just now";
            try {
              final timeStr = log['clock_time'];
              if (timeStr != null) {
                DateTime dt = DateTime.parse(timeStr).toLocal();
                int hour = dt.hour;
                String period = "AM";
                if (hour >= 12) {
                  period = "PM";
                  if (hour > 12) hour -= 12;
                }
                if (hour == 0) hour = 12;
                formattedTime = "${hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')} $period";
              }
            } catch (_) {}

            _statusMessage = "Welcome ${emp['first_name']} ${emp['last_name']}!\n${log['clock_type']} at $formattedTime";
            _statusColor = Colors.green;
          } else if (result != null && result['error'] == true) {
            // Show specific error from server/network
            _statusMessage = "${result['detail'] ?? 'Unknown error'}";
            _statusColor = Colors.red;
          } else {
            _statusMessage = "Access Denied.\nFace profile match not found.";
            _statusColor = Colors.red;
          }
        });
      }
      
      // Delete temporary file to save storage space
      await File(file.path).delete().catchError((_) {});

    } catch (e) {
      debugPrint("Error during face verification: $e");
      if (mounted) {
        setState(() {
          _statusMessage = "Verification Error. Try again.";
          _statusColor = Colors.red;
        });
      }
    }

    // Cooldown interval to prevent double scan and reset scanner view
    await Future.delayed(const Duration(seconds: 2));
    if (mounted) {
      setState(() {
        _matchResult = null;
        _statusMessage = "Stand in front of the camera to verify";
        _statusColor = Colors.grey;
        _isProcessingMatch = false;
        _isFacePresent = kIsWeb; // Reset face presence state (keep true on web)
      });
      // Restart image stream for next scan
      if (!kIsWeb) {
        _startImageStream();
      }
    }
  }

  Widget _buildSuccessOverlay() {
    if (_matchResult == null) return const SizedBox.shrink();

    final emp = _matchResult!['employee'];
    final log = _matchResult!['attendance_log'];
    final clockType = log['clock_type'] ?? 'CLOCK_IN';
    final status = log['status'] ?? 'PRESENT';

    // Format time
    String timeStr = "just now";
    String dateStr = "";
    try {
      final t = log['clock_time'];
      if (t != null) {
        DateTime dt = DateTime.parse(t).toLocal();
        int hour = dt.hour;
        String period = "AM";
        if (hour >= 12) {
          period = "PM";
          if (hour > 12) hour -= 12;
        }
        if (hour == 0) hour = 12;
        timeStr = "${hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')} $period";
        
        final months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        dateStr = "${dt.day} ${months[dt.month - 1]}, ${dt.year}";
      }
    } catch (_) {}

    final bool isLate = status == "LATE";

    return Positioned.fill(
      child: Container(
        color: Colors.black.withOpacity(0.7),
        child: BackdropFilter(
          filter: ImageFilter.blur(sigmaX: 8, sigmaY: 8),
          child: Center(
            child: Container(
              width: 320,
              padding: const EdgeInsets.symmetric(vertical: 32, horizontal: 24),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(4),
                border: Border.all(color: const Color(0xFFE5E5E5)),
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 64,
                    height: 64,
                    decoration: BoxDecoration(
                      color: const Color(0xFF111111),
                      borderRadius: BorderRadius.circular(32),
                    ),
                    child: const Icon(
                      Icons.check,
                      color: Colors.white,
                      size: 36,
                    ),
                  ),
                  const SizedBox(height: 20),
                  Text(
                    clockType == "CHECK_IN" ? "CHECK-IN" : "CHECK-OUT",
                    style: const TextStyle(
                      color: Color(0xFF111111),
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      letterSpacing: 2,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    "${emp['first_name']} ${emp['last_name']}",
                    style: const TextStyle(
                      color: Color(0xFF111111),
                      fontSize: 22,
                      fontWeight: FontWeight.w700,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 4),
                  Text(
                    emp['employee_id'],
                    style: const TextStyle(
                      color: Color(0xFF999999),
                      fontSize: 13,
                    ),
                  ),
                  const SizedBox(height: 16),
                  Container(
                    height: 1,
                    color: const Color(0xFFE5E5E5),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text("Time", style: TextStyle(color: Color(0xFF999999), fontSize: 13)),
                      Text(timeStr, style: const TextStyle(color: Color(0xFF111111), fontSize: 14, fontWeight: FontWeight.w600)),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text("Date", style: TextStyle(color: Color(0xFF999999), fontSize: 13)),
                      Text(dateStr, style: const TextStyle(color: Color(0xFF111111), fontSize: 14, fontWeight: FontWeight.w600)),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text("Status", style: TextStyle(color: Color(0xFF999999), fontSize: 13)),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                        decoration: BoxDecoration(
                          color: isLate ? const Color(0xFFFEF2F2) : const Color(0xFFF0FDF4),
                          borderRadius: BorderRadius.circular(2),
                          border: Border.all(color: isLate ? const Color(0xFFFCA5A5) : const Color(0xFF86EFAC)),
                        ),
                        child: Text(
                          status,
                          style: TextStyle(
                            color: isLate ? const Color(0xFFDC2626) : const Color(0xFF16A34A),
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            letterSpacing: 0.5,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildBlankScreenOverlay() {
    if (_isFacePresent) return const SizedBox.shrink();

    return Positioned.fill(
      child: GestureDetector(
        onDoubleTap: _showSettingsDialog,
        child: AnimatedBuilder(
          animation: _scanLineController!,
          builder: (context, child) {
            final pulseOpacity = 0.3 + (_scanLineController!.value * 0.7);

            return Container(
              color: const Color(0xFF021513),
              child: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Opacity(
                      opacity: pulseOpacity,
                      child: Container(
                        width: 100,
                        height: 100,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(color: const Color(0xFF22C55E).withOpacity(0.4), width: 2),
                          boxShadow: [
                            BoxShadow(
                              color: const Color(0xFF22C55E).withOpacity(0.1),
                              blurRadius: 16,
                              spreadRadius: 2,
                            ),
                          ],
                        ),
                        child: const Icon(
                          Icons.face_retouching_natural_rounded,
                          color: Color(0xFF22C55E),
                          size: 48,
                        ),
                      ),
                    ),
                    const SizedBox(height: 28),
                    Opacity(
                      opacity: pulseOpacity,
                      child: const Text(
                        "APPROACH TO VERIFY",
                        style: TextStyle(
                          color: Color(0xFF22C55E),
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                          letterSpacing: 6.0,
                        ),
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      "Double tap screen for settings",
                      style: TextStyle(
                        color: Colors.white.withOpacity(0.2),
                        fontSize: 11,
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        ),
      ),
    );
  }

  Future<void> _navigateToRegister() async {
    // Release the camera before navigating to avoid resource locks
    if (_cameraController != null) {
      try {
        await _cameraController!.dispose();
      } catch (e) {
        debugPrint("Error disposing camera: $e");
      }
      _cameraController = null;
      setState(() {
        _cameraInitialized = false;
      });
    }

    final prefs = await SharedPreferences.getInstance();
    final isLoggedIn = prefs.containsKey('access_token');

    if (!mounted) return;

    if (isLoggedIn) {
      await Navigator.push(
        context,
        MaterialPageRoute(builder: (context) => const RegisterPage()),
      );
    } else {
      await Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => const LoginPage(redirectPage: RegisterPage()),
        ),
      );
    }

    // Re-initialize when returning to this page
    await _loadSettings();
    await _initializeCamera();
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
                if (_isLoggedIn)
                  TextButton.icon(
                    onPressed: () {
                      Navigator.pop(context);
                      _navigateToRegister();
                    },
                    icon: const Icon(Icons.person_add, color: Color(0xFF111111)),
                    label: const Text("Register Staff & Face", style: TextStyle(color: Color(0xFF111111))),
                  ),
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
  Widget build(BuildContext context) {
    final bool hasCamera = _cameraInitialized && _cameraController != null;
    
    return Scaffold(
      backgroundColor: const Color(0xFF021513),
      body: Stack(
        children: [
          // 1. Fullscreen Camera Preview / Fallback Loading
          Positioned.fill(
            child: hasCamera
                ? FittedBox(
                    fit: BoxFit.cover,
                    child: SizedBox(
                      width: _cameraController!.value.previewSize?.height ?? 1080,
                      height: _cameraController!.value.previewSize?.width ?? 1920,
                      child: CameraPreview(_cameraController!),
                    ),
                  )
                : Container(
                    decoration: const BoxDecoration(
                      gradient: LinearGradient(
                        colors: [Color(0xFF032220), Color(0xFF011413)],
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                      ),
                    ),
                    child: Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            _cameraError != null && _cameraError!.contains("cameraPermissionDenied")
                                ? Icons.security
                                : Icons.videocam_off,
                            size: 64,
                            color: const Color(0xFFEF4444),
                          ),
                          const SizedBox(height: 16),
                          Text(
                            _cameraError != null ? "Camera Error: $_cameraError" : "Loading camera feed...",
                            style: const TextStyle(color: Colors.white70, fontSize: 14),
                          ),
                        ],
                      ),
                    ),
                  ),
          ),

          // 2. Custom Silhouette Scanning Overlay Mask
          Positioned.fill(
            child: IgnorePointer(
              child: CustomPaint(
                painter: FaceSilhouettePainter(),
              ),
            ),
          ),
          
          // 3. Scan Line Sweep Animation
          if (hasCamera && !_isProcessingMatch && _isFacePresent)
            Positioned.fill(
              child: IgnorePointer(
                child: AnimatedBuilder(
                  animation: _scanLineController!,
                  builder: (context, child) {
                    return CustomPaint(
                      painter: ScanLinePainter(progress: _scanLineController!.value),
                    );
                  },
                ),
              ),
            ),

          // 4. Floating Premium Top Header
          Positioned(
            top: MediaQuery.of(context).padding.top + 12,
            left: 16,
            right: 16,
            child: _buildFloatingKioskHeader(),
          ),

          // 5. Floating Camera Controls (Flash, Flip)
          Positioned(
            right: 16,
            top: MediaQuery.of(context).padding.top + 100,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                _buildFloatingIconButton(
                  icon: _flashOn ? Icons.flash_on : Icons.flash_off,
                  color: _flashOn ? const Color(0xFF22C55E) : Colors.white,
                  onPressed: _toggleFlash,
                  tooltip: "Toggle Flashlight",
                ),
                const SizedBox(height: 12),
                _buildFloatingIconButton(
                  icon: Icons.flip_camera_ios_rounded,
                  color: Colors.white,
                  onPressed: _flipCamera,
                  tooltip: "Flip Camera",
                ),
              ],
            ),
          ),

          // 6. Modern Bottom Drawer Overlay (Status & Matched Result)
          Positioned(
            bottom: MediaQuery.of(context).padding.bottom + 24,
            left: 20,
            right: 20,
            child: Align(
              alignment: Alignment.bottomCenter,
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 480),
                child: _buildBottomStatusDrawer(),
              ),
            ),
          ),

          // 7. Screen Saver Blank Screen Overlay (Energy Saver when no face is present)
          _buildBlankScreenOverlay(),
        ],
      ),
    );
  }

  Widget _buildFloatingKioskHeader() {
    return ClipRRect(
      borderRadius: BorderRadius.circular(16),
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 10, sigmaY: 10),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
          decoration: BoxDecoration(
            color: Colors.black.withOpacity(0.4),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: Colors.white.withOpacity(0.08), width: 1),
          ),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: const BoxDecoration(
                  color: Color(0xFF0F5B3C),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.spa_rounded,
                  color: Color(0xFF4ADE80),
                  size: 22,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: const [
                    Text(
                      "PSNF KIOSK",
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 15,
                        fontWeight: FontWeight.w800,
                        letterSpacing: 0.5,
                      ),
                    ),
                    Text(
                      "Pearl Special Needs Foundation",
                      style: TextStyle(
                        color: Color(0xFF94A3B8),
                        fontSize: 10,
                      ),
                    ),
                  ],
                ),
              ),
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(
                    Icons.wifi_rounded,
                    color: Color(0xFF22C55E),
                    size: 16,
                  ),
                  const SizedBox(width: 6),
                  const Text(
                    "ONLINE",
                    style: TextStyle(
                      color: Color(0xFF22C55E),
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(width: 12),
                  TextButton.icon(
                    icon: const Icon(Icons.person_add_rounded, color: Colors.white, size: 14),
                    label: const Text(
                      "Register",
                      style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                    ),
                    onPressed: _navigateToRegister,
                    style: TextButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      minimumSize: Size.zero,
                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                    ),
                  ),
                  const SizedBox(width: 8),
                  IconButton(
                    icon: const Icon(Icons.settings_rounded, color: Colors.white, size: 20),
                    onPressed: _showSettingsDialog,
                    padding: EdgeInsets.zero,
                    constraints: const BoxConstraints(),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildFloatingIconButton({
    required IconData icon,
    required Color color,
    required VoidCallback onPressed,
    required String tooltip,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.black.withOpacity(0.5),
        shape: BoxShape.circle,
        border: Border.all(color: Colors.white.withOpacity(0.1), width: 1.2),
      ),
      child: IconButton(
        icon: Icon(icon, color: color, size: 22),
        onPressed: onPressed,
        tooltip: tooltip,
      ),
    );
  }

  Widget _buildBottomStatusDrawer() {
    IconData statusIcon = Icons.face_retouching_natural_rounded;
    String statusTitle = "Ready to Scan";
    String statusSub = "Position your face inside the green frame";
    Color accentColor = const Color(0xFF22C55E);
    Color cardBgColor = const Color(0xCC05201E); // Semi-transparent dark green
    Color borderColor = const Color(0xFF1E3D3A);
    bool showProfile = _matchResult != null;

    if (_statusColor == Colors.red) {
      statusIcon = Icons.error_outline_rounded;
      statusTitle = "Access Denied";
      statusSub = _statusMessage;
      accentColor = const Color(0xFFEF4444);
      cardBgColor = const Color(0xCC2D0B0E); // Soft red background
      borderColor = const Color(0xFF5F1E24);
    } else if (_statusColor == Colors.green) {
      statusIcon = Icons.verified_rounded;
      statusTitle = "Scan Success";
      statusSub = "Attendance recorded successfully";
      accentColor = const Color(0xFF22C55E);
      cardBgColor = const Color(0xCC05201E);
      borderColor = const Color(0xFF1E3D3A);
    } else if (_isProcessingMatch) {
      statusIcon = Icons.hourglass_top_rounded;
      statusTitle = "Processing";
      statusSub = _statusMessage;
      accentColor = const Color(0xFFF59E0B);
      cardBgColor = const Color(0xCC2B1B04); // Soft orange background
      borderColor = const Color(0xFF523B18);
    }

    return ClipRRect(
      borderRadius: BorderRadius.circular(24),
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 10, sigmaY: 10),
        child: Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: cardBgColor,
            borderRadius: BorderRadius.circular(24),
            border: Border.all(color: borderColor, width: 1.5),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.4),
                blurRadius: 24,
                offset: const Offset(0, 10),
              ),
            ],
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              if (showProfile) ...[
                _buildDrawerProfileContent(),
              ] else ...[
                Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: accentColor.withOpacity(0.12),
                            shape: BoxShape.circle,
                          ),
                          child: Icon(statusIcon, color: accentColor, size: 28),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                statusTitle,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 16,
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: 0.2,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                statusSub,
                                style: const TextStyle(
                                  color: Color(0xFF94A3B8),
                                  fontSize: 13,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    if (kIsWeb && !_isProcessingMatch) ...[
                      const SizedBox(height: 16),
                      ElevatedButton.icon(
                        onPressed: () {
                          if (_cameraInitialized && _cameraController != null) {
                            _onFaceDetected();
                          }
                        },
                        icon: const Icon(Icons.face_retouching_natural_rounded),
                        label: const Text("TAP TO VERIFY", style: TextStyle(fontWeight: FontWeight.bold)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: accentColor,
                          foregroundColor: Colors.white,
                          minimumSize: const Size(double.infinity, 44),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ],
                  ],
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDrawerProfileContent() {
    final emp = _matchResult!['employee'];
    final log = _matchResult!['attendance_log'];
    final clockType = log['clock_type'] ?? 'CHECK_IN';
    final status = log['status'] ?? 'PRESENT';

    // Format clock time
    String timeStr = "just now";
    String dateStr = "";
    try {
      final t = log['clock_time'];
      if (t != null) {
        DateTime dt = DateTime.parse(t).toLocal();
        int hour = dt.hour;
        String period = "AM";
        if (hour >= 12) {
          period = "PM";
          if (hour > 12) hour -= 12;
        }
        if (hour == 0) hour = 12;
        timeStr = "${hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')} $period";
        
        final months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        dateStr = "${dt.day} ${months[dt.month - 1]}, ${dt.year}";
      }
    } catch (_) {}

    final bool isLate = status == "LATE";
    final Color badgeBg = isLate ? const Color(0xFFFCE8E6) : const Color(0xFFE6F4EA);
    final Color badgeText = isLate ? const Color(0xFFC5221F) : const Color(0xFF137333);

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Row(
          children: [
            Container(
              width: 56,
              height: 56,
              decoration: BoxDecoration(
                color: const Color(0xFF0F5B3C).withOpacity(0.2),
                shape: BoxShape.circle,
                border: Border.all(color: const Color(0xFF22C55E), width: 1.5),
              ),
              child: const Icon(
                Icons.person_rounded,
                color: Color(0xFF22C55E),
                size: 32,
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    "${emp['first_name']} ${emp['last_name']}",
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    "ID: ${emp['employee_id']}",
                    style: const TextStyle(
                      color: Color(0xFF94A3B8),
                      fontSize: 13,
                    ),
                  ),
                ],
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: clockType == "CHECK_IN" ? const Color(0xFFE8F0FE) : const Color(0xFFFEF7E0),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                clockType == "CHECK_IN" ? "CHECK-IN" : "CHECK-OUT",
                style: TextStyle(
                  color: clockType == "CHECK_IN" ? const Color(0xFF1A73E8) : const Color(0xFFB06000),
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                  letterSpacing: 0.5,
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),
        Container(height: 1, color: Colors.white.withOpacity(0.1)),
        const SizedBox(height: 12),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Row(
              children: [
                Icon(Icons.access_time_rounded, color: Colors.white.withOpacity(0.6), size: 16),
                const SizedBox(width: 6),
                Text(
                  timeStr,
                  style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w600),
                ),
                const SizedBox(width: 12),
                Icon(Icons.calendar_month_rounded, color: Colors.white.withOpacity(0.6), size: 16),
                const SizedBox(width: 6),
                Text(
                  dateStr,
                  style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w600),
                ),
              ],
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color: badgeBg,
                borderRadius: BorderRadius.circular(6),
              ),
              child: Text(
                status,
                style: TextStyle(
                  color: badgeText,
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Uint8List yuv420ToNv21(CameraImage image) {
    if (image.planes.length < 3) {
      final WriteBuffer allBytes = WriteBuffer();
      for (final Plane plane in image.planes) {
        allBytes.putUint8List(plane.bytes);
      }
      return allBytes.done().buffer.asUint8List();
    }

    final width = image.width;
    final height = image.height;
    final yPlane = image.planes[0];
    final uPlane = image.planes[1];
    final vPlane = image.planes[2];

    final yBuffer = yPlane.bytes;
    final uBuffer = uPlane.bytes;
    final vBuffer = vPlane.bytes;

    final numPixels = width * height;
    final nv21 = Uint8List(numPixels + (numPixels ~/ 2));

    int idY = 0;
    int rowStrideY = yPlane.bytesPerRow;
    for (int y = 0; y < height; y++) {
      nv21.setRange(idY, idY + width, yBuffer.sublist(y * rowStrideY, y * rowStrideY + width));
      idY += width;
    }

    int idUV = numPixels;
    int rowStrideUV = uPlane.bytesPerRow;
    int pixelStrideUV = uPlane.bytesPerPixel ?? 1;

    for (int y = 0; y < height ~/ 2; y++) {
      for (int x = 0; x < width ~/ 2; x++) {
        nv21[idUV++] = vBuffer[y * rowStrideUV + x * pixelStrideUV];
        nv21[idUV++] = uBuffer[y * rowStrideUV + x * pixelStrideUV];
      }
    }

    return nv21;
  }
}

class RoundedCornerPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0xFF22C55E)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 3.0
      ..strokeCap = StrokeCap.round;

    final double r = 16.0;
    final double l = 28.0;

    Path tlPath = Path()
      ..moveTo(0, l)
      ..lineTo(0, r)
      ..arcToPoint(Offset(r, 0), radius: Radius.circular(r), clockwise: true)
      ..lineTo(l, 0);
    canvas.drawPath(tlPath, paint);

    Path trPath = Path()
      ..moveTo(size.width - l, 0)
      ..lineTo(size.width - r, 0)
      ..arcToPoint(Offset(size.width, r), radius: Radius.circular(r), clockwise: true)
      ..lineTo(size.width, l);
    canvas.drawPath(trPath, paint);

    Path blPath = Path()
      ..moveTo(0, size.height - l)
      ..lineTo(0, size.height - r)
      ..arcToPoint(Offset(r, size.height), radius: Radius.circular(r), clockwise: false)
      ..lineTo(l, size.height);
    canvas.drawPath(blPath, paint);

    Path brPath = Path()
      ..moveTo(size.width - l, size.height)
      ..lineTo(size.width - r, size.height)
      ..arcToPoint(Offset(size.width, size.height - r), radius: Radius.circular(r), clockwise: false)
      ..lineTo(size.width, size.height - l);
    canvas.drawPath(brPath, paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class FaceSilhouettePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.white.withOpacity(0.08)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.0;

    final fillPaint = Paint()
      ..color = Colors.white.withOpacity(0.02)
      ..style = PaintingStyle.fill;

    final centerX = size.width / 2;
    final centerY = size.height * 0.45;
    final headWidth = size.width * 0.42;
    final headHeight = size.height * 0.45;

    final shoulderPath = Path()
      ..moveTo(centerX - headWidth * 0.3, centerY + headHeight * 0.45)
      ..lineTo(centerX - headWidth * 0.3, centerY + headHeight * 0.6)
      ..quadraticBezierTo(
        centerX - headWidth * 0.9, centerY + headHeight * 0.7,
        centerX - headWidth * 1.2, size.height,
      )
      ..lineTo(centerX + headWidth * 1.2, size.height)
      ..quadraticBezierTo(
        centerX + headWidth * 0.9, centerY + headHeight * 0.7,
        centerX + headWidth * 0.3, centerY + headHeight * 0.6,
      )
      ..lineTo(centerX + headWidth * 0.3, centerY + headHeight * 0.45)
      ..close();

    canvas.drawPath(shoulderPath, fillPaint);
    canvas.drawPath(shoulderPath, paint);

    final headRect = Rect.fromCenter(
      center: Offset(centerX, centerY),
      width: headWidth,
      height: headHeight,
    );
    canvas.drawOval(headRect, fillPaint);
    canvas.drawOval(headRect, paint);

    final gridPaint = Paint()
      ..color = Colors.white.withOpacity(0.05)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 0.5;

    final dotPaint = Paint()
      ..color = Colors.white.withOpacity(0.12)
      ..style = PaintingStyle.fill;

    final points = [
      Offset(centerX, centerY - headHeight * 0.3),
      Offset(centerX - headWidth * 0.25, centerY - headHeight * 0.15),
      Offset(centerX + headWidth * 0.25, centerY - headHeight * 0.15),
      Offset(centerX - headWidth * 0.35, centerY),
      Offset(centerX, centerY),
      Offset(centerX + headWidth * 0.35, centerY),
      Offset(centerX - headWidth * 0.2, centerY + headHeight * 0.2),
      Offset(centerX + headWidth * 0.2, centerY + headHeight * 0.2),
      Offset(centerX, centerY + headHeight * 0.35),
    ];

    final connections = [
      [0, 1], [0, 2], [1, 2],
      [1, 3], [1, 4], [2, 4], [2, 5],
      [3, 4], [4, 5],
      [3, 6], [4, 6], [4, 7], [5, 7],
      [6, 7], [6, 8], [7, 8], [4, 8]
    ];

    for (var conn in connections) {
      canvas.drawLine(points[conn[0]], points[conn[1]], gridPaint);
    }

    for (var pt in points) {
      canvas.drawCircle(pt, 2.0, dotPaint);
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class ScanLinePainter extends CustomPainter {
  final double progress;
  ScanLinePainter({required this.progress});

  @override
  void paint(Canvas canvas, Size size) {
    final centerX = size.width / 2;
    final centerY = size.height * 0.45;
    final headWidth = size.width * 0.42;
    final headHeight = size.height * 0.45;

    // Laser glow gradient
    final paint = Paint()
      ..shader = LinearGradient(
        colors: [
          Colors.green.withOpacity(0.0),
          const Color(0xFF22C55E).withOpacity(0.3),
          const Color(0xFF22C55E),
          const Color(0xFF22C55E).withOpacity(0.3),
          Colors.green.withOpacity(0.0),
        ],
      ).createShader(Rect.fromLTWH(centerX - headWidth * 0.5, 0, headWidth, 4))
      ..style = PaintingStyle.fill;

    // Scan sweep line bounds inside the head silhouette
    final double startY = centerY - headHeight * 0.4;
    final double endY = centerY + headHeight * 0.4;
    final double currentY = startY + (endY - startY) * progress;

    final rect = Rect.fromLTWH(
      centerX - headWidth * 0.45,
      currentY - 2,
      headWidth * 0.9,
      4,
    );

    // Draw the glow bar
    canvas.drawRect(rect, paint);

    // Draw active bright core line
    final linePaint = Paint()
      ..color = const Color(0xFF4ADE80)
      ..strokeWidth = 1.2;
    canvas.drawLine(
      Offset(centerX - headWidth * 0.45, currentY),
      Offset(centerX + headWidth * 0.45, currentY),
      linePaint,
    );
  }

  @override
  bool shouldRepaint(covariant ScanLinePainter oldDelegate) =>
      oldDelegate.progress != progress;
}
