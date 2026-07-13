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
  bool _isFacePresent = false;
  Timer? _faceGoneTimer;
  AnimationController? _scanLineController;

  // Settings
  String _hostUrl = "http://192.168.1.5:8000";
  bool _isLoggedIn = false;

  // Flash and Flip Camera state
  bool _isFrontCamera = true;
  bool _flashOn = false;

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
      _hostUrl = prefs.getString('host_url') ?? "http://192.168.1.5:8000";
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
        imageFormatGroup: Platform.isAndroid
            ? ImageFormatGroup.nv21
            : ImageFormatGroup.bgra8888,
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

      _startImageStream();
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
      await _cameraController!.stopImageStream();

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
        _isFacePresent = false; // Reset face presence state to turn screen off if no one is in front
      });
      // Restart image stream for next scan
      _startImageStream();
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
              color: const Color(0xFF111111),
              child: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Opacity(
                      opacity: pulseOpacity,
                      child: Container(
                        width: 90,
                        height: 90,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(color: Colors.white.withOpacity(0.4), width: 1.5),
                        ),
                        child: Icon(Icons.face, color: Colors.white.withOpacity(0.7), size: 48),
                      ),
                    ),
                    const SizedBox(height: 24),
                    Opacity(
                      opacity: pulseOpacity,
                      child: const Text(
                        "APPROACH TO SCAN",
                        style: TextStyle(
                          color: Colors.white70,
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          letterSpacing: 4.0,
                        ),
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
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (context) => const RegisterPage()),
                      );
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
    return Scaffold(
      backgroundColor: const Color(0xFF021513),
      appBar: PreferredSize(
        preferredSize: const Size.fromHeight(68),
        child: _buildCustomAppBar(),
      ),
      body: Stack(
        children: [
          Container(
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [
                  Color(0xFF032220),
                  Color(0xFF011413),
                ],
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
              ),
            ),
            child: OrientationBuilder(
              builder: (context, orientation) {
                final isLandscape = orientation == Orientation.landscape;
                if (isLandscape) {
                  return _buildLandscapeLayout();
                } else {
                  return _buildPortraitLayout();
                }
              },
            ),
          ),
          _buildSuccessOverlay(),
          _buildBlankScreenOverlay(),
        ],
      ),
    );
  }

  Widget _buildCustomAppBar() {
    return AppBar(
      automaticallyImplyLeading: false,
      backgroundColor: const Color(0xFFF8FAFC),
      elevation: 0,
      titleSpacing: 16,
      toolbarHeight: 68,
      title: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(6),
            decoration: BoxDecoration(
              color: const Color(0xFFDCFCE7),
              shape: BoxShape.circle,
              border: Border.all(color: const Color(0xFF86EFAC), width: 1),
            ),
            child: const Icon(
              Icons.spa,
              color: Color(0xFF15803D),
              size: 24,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: const [
                Text(
                  "PSNF",
                  style: TextStyle(
                    color: Color(0xFF15803D),
                    fontSize: 16,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 0.5,
                  ),
                ),
                Text(
                  "Pearl Special Needs Foundation",
                  style: TextStyle(
                    color: Color(0xFF64748B),
                    fontSize: 9,
                    fontWeight: FontWeight.w500,
                  ),
                  overflow: TextOverflow.ellipsis,
                  maxLines: 1,
                ),
                Text(
                  "Staff Attendance",
                  style: TextStyle(
                    color: Color(0xFF16A34A),
                    fontSize: 10,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
      actions: [
        Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(
              Icons.wifi,
              color: Color(0xFF16A34A),
              size: 16,
            ),
            const SizedBox(width: 6),
            const Text(
              "Online",
              style: TextStyle(
                color: Color(0xFF16A34A),
                fontSize: 12,
                fontWeight: FontWeight.w600,
              ),
            ),
            const SizedBox(width: 6),
            Container(
              width: 8,
              height: 8,
              decoration: const BoxDecoration(
                color: Color(0xFF22C55E),
                shape: BoxShape.circle,
              ),
            ),
          ],
        ),
        const SizedBox(width: 12),
        IconButton(
          icon: const Icon(Icons.menu, color: Color(0xFF1E293B), size: 24),
          onPressed: _showSettingsDialog,
        ),
        const SizedBox(width: 8),
      ],
      bottom: PreferredSize(
        preferredSize: const Size.fromHeight(1),
        child: Container(color: const Color(0xFFE2E8F0), height: 1),
      ),
    );
  }

  Widget _buildPortraitLayout() {
    return SingleChildScrollView(
      physics: const BouncingScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          const Text(
            "Scan to Mark Attendance",
            style: TextStyle(
              color: Colors.white,
              fontSize: 24,
              fontWeight: FontWeight.w700,
              letterSpacing: 0.2,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 6),
          const Text(
            "Align your face within the frame",
            style: TextStyle(
              color: Color(0xFF94A3B8),
              fontSize: 13,
              fontWeight: FontWeight.w400,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 24),
          _buildCameraCard(isLandscape: false),
          const SizedBox(height: 20),
          _buildStatusBox(),
          const SizedBox(height: 24),
          _buildTapToScanButton(),
          const SizedBox(height: 28),
          _buildFooterInfoPill(),
          const SizedBox(height: 20),
          _buildBottomStatusBar(),
        ],
      ),
    );
  }

  Widget _buildLandscapeLayout() {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(
          flex: 6,
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  "Scan to Mark Attendance",
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 26,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 6),
                const Text(
                  "Align your face within the frame",
                  style: TextStyle(
                    color: Color(0xFF94A3B8),
                    fontSize: 13,
                  ),
                ),
                const SizedBox(height: 20),
                _buildCameraCard(isLandscape: true),
              ],
            ),
          ),
        ),
        Expanded(
          flex: 5,
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                const SizedBox(height: 10),
                _buildStatusBox(),
                const SizedBox(height: 24),
                _buildTapToScanButton(),
                const SizedBox(height: 24),
                _buildFooterInfoPill(),
                const SizedBox(height: 32),
                _buildBottomStatusBar(),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildCameraCard({required bool isLandscape}) {
    final double cardHeight = isLandscape ? 360 : 280;
    
    return Container(
      height: cardHeight,
      decoration: BoxDecoration(
        color: const Color(0xFF0F2624),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFF1E3D3A), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.3),
            blurRadius: 16,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: Stack(
          children: [
            Positioned.fill(
              child: _cameraInitialized && _cameraController != null
                  ? AspectRatio(
                      aspectRatio: _cameraController!.value.aspectRatio,
                      child: CameraPreview(_cameraController!),
                    )
                  : Container(
                      color: const Color(0xFF031614),
                      child: Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              _cameraError != null && _cameraError!.contains("cameraPermissionDenied")
                                  ? Icons.security
                                  : Icons.videocam_off,
                              size: 48,
                              color: const Color(0xFFEF4444),
                            ),
                            const SizedBox(height: 12),
                            Text(
                              _cameraError != null ? "Camera Error" : "Loading camera feed...",
                              style: const TextStyle(color: Colors.white70, fontSize: 13),
                            ),
                          ],
                        ),
                      ),
                    ),
            ),
            Positioned.fill(
              child: IgnorePointer(
                child: CustomPaint(
                  painter: FaceSilhouettePainter(),
                ),
              ),
            ),
            Positioned.fill(
              child: IgnorePointer(
                child: CustomPaint(
                  painter: RoundedCornerPainter(),
                ),
              ),
            ),
            Positioned(
              top: 16,
              left: 16,
              child: Container(
                decoration: BoxDecoration(
                  color: Colors.black.withOpacity(0.5),
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.white.withOpacity(0.2), width: 1),
                ),
                child: IconButton(
                  icon: Icon(
                    _flashOn ? Icons.flash_on : Icons.flash_off,
                    color: _flashOn ? const Color(0xFF22C55E) : Colors.white,
                    size: 20,
                  ),
                  onPressed: _toggleFlash,
                  tooltip: "Toggle Flashlight",
                ),
              ),
            ),
            Positioned(
              top: 16,
              right: 16,
              child: Container(
                decoration: BoxDecoration(
                  color: Colors.black.withOpacity(0.5),
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.white.withOpacity(0.2), width: 1),
                ),
                child: IconButton(
                  icon: const Icon(
                    Icons.flip_camera_ios,
                    color: Colors.white,
                    size: 20,
                  ),
                  onPressed: _flipCamera,
                  tooltip: "Flip Camera",
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatusBox() {
    IconData statusIcon = Icons.verified_user_outlined;
    String statusTitle = "Ready to Scan";
    String statusSub = "Look at the camera to mark your attendance";
    Color accentColor = const Color(0xFF10B981);
    Color boxBgColor = const Color(0xFF0A2B27);
    Color boxBorderColor = const Color(0xFF1E3D3A);

    if (_statusColor == Colors.red) {
      statusIcon = Icons.error_outline_sharp;
      statusTitle = "Scan Failed";
      statusSub = _statusMessage;
      accentColor = const Color(0xFFEF4444);
      boxBgColor = const Color(0xFF2B0A0D);
      boxBorderColor = const Color(0xFF3D1E21);
    } else if (_statusColor == Colors.green) {
      statusIcon = Icons.check_circle_outline;
      statusTitle = "Scan Success";
      statusSub = _statusMessage;
      accentColor = const Color(0xFF10B981);
      boxBgColor = const Color(0xFF0A2B27);
      boxBorderColor = const Color(0xFF1E3D3A);
    } else if (_isProcessingMatch) {
      statusIcon = Icons.hourglass_empty;
      statusTitle = "Processing";
      statusSub = _statusMessage;
      accentColor = const Color(0xFFF59E0B);
      boxBgColor = const Color(0xFF2B210A);
      boxBorderColor = const Color(0xFF3D331E);
    }

    return Container(
      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
      decoration: BoxDecoration(
        color: boxBgColor,
        borderRadius: BorderRadius.circular(32),
        border: Border.all(color: boxBorderColor, width: 1.5),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(statusIcon, color: accentColor, size: 24),
          const SizedBox(width: 12),
          Flexible(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  statusTitle,
                  style: TextStyle(
                    color: accentColor,
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  statusSub,
                  style: const TextStyle(
                    color: Color(0xFF94A3B8),
                    fontSize: 12,
                    fontWeight: FontWeight.w400,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
        ],
      ),
    );
  }

  Widget _buildTapToScanButton() {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        GestureDetector(
          onTap: () {
            if (!_isProcessingMatch && _cameraInitialized && _cameraController != null) {
              _cameraController!.value.isStreamingImages 
                ? _onFaceDetected()
                : _initializeCamera();
            }
          },
          child: Stack(
            alignment: Alignment.center,
            children: [
              Container(
                width: 96,
                height: 96,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: RadialGradient(
                    colors: [
                      const Color(0xFF10B981).withOpacity(0.4),
                      const Color(0xFF10B981).withOpacity(0.0),
                    ],
                  ),
                ),
              ),
              Container(
                width: 72,
                height: 72,
                decoration: const BoxDecoration(
                  color: Colors.white,
                  shape: BoxShape.circle,
                  boxShadow: [
                    BoxShadow(
                      color: Color(0x3F10B981),
                      blurRadius: 12,
                      spreadRadius: 2,
                    ),
                  ],
                ),
                child: const Icon(
                  Icons.face_retouching_natural_rounded,
                  color: Color(0xFF10B981),
                  size: 32,
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 8),
        const Text(
          "Tap to Scan",
          style: TextStyle(
            color: Colors.white,
            fontSize: 15,
            fontWeight: FontWeight.w700,
          ),
        ),
      ],
    );
  }

  Widget _buildFooterInfoPill() {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
      decoration: BoxDecoration(
        color: const Color(0xFF061A18),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFF122C2A), width: 1),
      ),
      child: IntrinsicHeight(
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
          children: [
            Expanded(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: const [
                  Icon(Icons.verified_outlined, color: Colors.white, size: 20),
                  SizedBox(height: 4),
                  Text("Secure", style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600)),
                  Text("Your data is safe", style: TextStyle(color: Color(0xFF64748B), fontSize: 10)),
                ],
              ),
            ),
            Container(width: 1, color: const Color(0xFF1E3D3A)),
            Expanded(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: const [
                  Icon(Icons.track_changes_rounded, color: Colors.white, size: 20),
                  SizedBox(height: 4),
                  Text("Accurate", style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600)),
                  Text("99.9% accuracy", style: TextStyle(color: Color(0xFF64748B), fontSize: 10)),
                ],
              ),
            ),
            Container(width: 1, color: const Color(0xFF1E3D3A)),
            Expanded(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: const [
                  Icon(Icons.bolt_outlined, color: Colors.white, size: 20),
                  SizedBox(height: 4),
                  Text("Real-time", style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600)),
                  Text("Instant recording", style: TextStyle(color: Color(0xFF64748B), fontSize: 10)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBottomStatusBar() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Row(
          mainAxisSize: MainAxisSize.min,
          children: const [
            Icon(Icons.lock_outline_rounded, color: Color(0xFF10B981), size: 14),
            SizedBox(width: 6),
            Text(
              "Connected to PSNF Admin Panel",
              style: TextStyle(
                color: Color(0xFF10B981),
                fontSize: 12,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
        const Text(
          "v1.0.0",
          style: TextStyle(
            color: Color(0xFF64748B),
            fontSize: 12,
            fontWeight: FontWeight.w500,
          ),
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
