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
      
      // Default to front-facing camera for kiosk
      final frontCamera = cameras.firstWhere(
        (c) => c.lensDirection == CameraLensDirection.front,
        orElse: () => cameras.first,
      );

      _cameraController = CameraController(
        frontCamera,
        ResolutionPreset.medium,
        enableAudio: false,
        imageFormatGroup: Platform.isAndroid
            ? ImageFormatGroup.nv21
            : ImageFormatGroup.bgra8888,
      );

      await _cameraController!.initialize();
      if (!mounted) return;

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
              _faceGoneTimer = Timer(const Duration(seconds: 1), () {
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

  Future<void> _onFaceDetected(Face face) async {
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
      backgroundColor: Colors.white,
      appBar: AppBar(
        title: const Text("PSNF Attendance", style: TextStyle(fontWeight: FontWeight.w700, letterSpacing: 0.5, fontSize: 16)),
        backgroundColor: Colors.white,
        elevation: 0,
        foregroundColor: const Color(0xFF111111),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(1),
          child: Container(color: const Color(0xFFE5E5E5), height: 1),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.settings, size: 22),
            onPressed: _showSettingsDialog,
          )
        ],
      ),
      body: Stack(
        children: [
          OrientationBuilder(
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
          _buildSuccessOverlay(),
          _buildBlankScreenOverlay(),
        ],
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
            child: Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    _cameraError != null && _cameraError!.contains("cameraPermissionDenied")
                        ? Icons.security
                        : Icons.videocam_off,
                    size: 80,
                    color: _cameraError != null && _cameraError!.contains("cameraPermissionDenied")
                        ? Colors.orange
                        : Colors.red,
                  ),
                  const SizedBox(height: 16),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 24),
                    child: Text(
                      _cameraError != null
                          ? (_cameraError!.contains("cameraPermissionDenied")
                              ? "Camera Permission Denied.\n\nPlease go to Settings > Apps > PSNF Attendance > Permissions and turn ON Camera permission."
                              : "Camera Error:\n$_cameraError")
                          : "Initializing camera stream...",
                      style: const TextStyle(color: Colors.white70),
                      textAlign: TextAlign.center,
                    ),
                  ),
                ],
              ),
            ),
          ),
        
        // Oval Target Cutout Painter with scan line animation
        Positioned.fill(
          child: IgnorePointer(
            child: AnimatedBuilder(
              animation: _scanLineController!,
              builder: (context, child) {
                return OvalHUDOverlay(scanLinePercent: _scanLineController!.value);
              },
            ),
          ),
        ),
        
        // Connection indicator status
        Positioned(
          top: 16,
          left: 16,
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              color: _isLoggedIn ? Colors.white : Colors.white,
              borderRadius: BorderRadius.circular(2),
              border: Border.all(color: _isLoggedIn ? const Color(0xFF86EFAC) : const Color(0xFFFCA5A5)),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  _isLoggedIn ? Icons.check_circle : Icons.error_outline,
                  size: 14,
                  color: _isLoggedIn ? const Color(0xFF16A34A) : const Color(0xFFDC2626),
                ),
                const SizedBox(width: 6),
                Text(
                  _isLoggedIn ? "Authorized" : "Not Authorized",
                  style: TextStyle(color: _isLoggedIn ? const Color(0xFF16A34A) : const Color(0xFFDC2626), fontSize: 11, fontWeight: FontWeight.w600),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildStatusPanel({required bool isLandscape}) {
    return Container(
      padding: EdgeInsets.all(isLandscape ? 32.0 : 16.0),
      key: isLandscape ? const Key("status_panel") : null,
      color: Colors.white,
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            "Status",
            style: TextStyle(
              fontSize: isLandscape ? 16 : 13, 
              fontWeight: FontWeight.w600, 
              color: const Color(0xFF999999),
              letterSpacing: 1,
            ),
            textAlign: TextAlign.center,
          ),
          SizedBox(height: isLandscape ? 20 : 10),
          AnimatedContainer(
            duration: const Duration(milliseconds: 300),
            padding: EdgeInsets.all(isLandscape ? 24 : 14),
            decoration: BoxDecoration(
              color: _statusColor == Colors.grey 
                  ? const Color(0xFFF7F7F7) 
                  : (_statusColor == Colors.green 
                      ? const Color(0xFFF0FDF4) 
                      : const Color(0xFFFEF2F2)),
              borderRadius: BorderRadius.circular(4),
              border: Border.all(
                color: _statusColor == Colors.grey 
                    ? const Color(0xFFE5E5E5) 
                    : (_statusColor == Colors.green 
                        ? const Color(0xFF86EFAC) 
                        : const Color(0xFFFCA5A5)),
                width: 1,
              ),
            ),
            child: Text(
              _statusMessage,
              style: TextStyle(
                fontSize: isLandscape ? 16 : 13,
                fontWeight: FontWeight.w600,
                color: _statusColor == Colors.grey 
                    ? const Color(0xFF666666) 
                    : (_statusColor == Colors.green 
                        ? const Color(0xFF16A34A) 
                        : const Color(0xFFDC2626)),
                height: 1.4,
              ),
              textAlign: TextAlign.center,
            ),
          ),
        ],
      ),
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

    // Y plane
    int idY = 0;
    int rowStrideY = yPlane.bytesPerRow;
    for (int y = 0; y < height; y++) {
      nv21.setRange(idY, idY + width, yBuffer.sublist(y * rowStrideY, y * rowStrideY + width));
      idY += width;
    }

    // UV planes (interleaved)
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

// Oval Target custom overlay painter
class OvalHUDOverlay extends StatelessWidget {
  final double scanLinePercent;
  const OvalHUDOverlay({super.key, required this.scanLinePercent});

  @override
  Widget build(BuildContext context) {
    return CustomPaint(
      painter: _OvalHUDPainter(scanLinePercent: scanLinePercent),
    );
  }
}

class _OvalHUDPainter extends CustomPainter {
  final double scanLinePercent;
  _OvalHUDPainter({required this.scanLinePercent});

  @override
  void paint(Canvas canvas, Size size) {
    final backgroundPaint = Paint()
      ..color = Colors.black.withOpacity(0.5)
      ..style = PaintingStyle.fill;

    final borderPaint = Paint()
      ..color = Colors.white
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2.0;

    // Outer full bounding rectangle
    final Path backgroundPath = Path()..addRect(Rect.fromLTWH(0, 0, size.width, size.height));

    // Inner Oval cutout
    final bool isPortrait = size.width < size.height;
    final double ovalWidth = isPortrait ? size.width * 0.65 : size.width * 0.45;
    final double ovalHeight = isPortrait ? size.height * 0.55 : size.height * 0.7;

    final Rect ovalRect = Rect.fromCenter(
      center: Offset(size.width / 2, size.height / 2),
      width: ovalWidth,
      height: ovalHeight,
    );
    final Path ovalPath = Path()..addOval(ovalRect);

    final Path resultPath = Path.combine(
      PathOperation.difference,
      backgroundPath,
      ovalPath,
    );

    canvas.drawPath(resultPath, backgroundPaint);
    canvas.drawOval(ovalRect, borderPaint);

    // Scanning line
    final double laserY = ovalRect.top + (ovalRect.height * scanLinePercent);
    
    final laserGlowPaint = Paint()
      ..shader = LinearGradient(
        colors: [
          Colors.white.withOpacity(0.0),
          Colors.white.withOpacity(0.2),
          Colors.white.withOpacity(0.2),
          Colors.white.withOpacity(0.0),
        ],
      ).createShader(Rect.fromLTRB(ovalRect.left, laserY - 6, ovalRect.right, laserY + 6))
      ..style = PaintingStyle.fill;

    canvas.drawRect(Rect.fromLTRB(ovalRect.left + 16, laserY - 4, ovalRect.right - 16, laserY + 4), laserGlowPaint);

    final laserLinePaint = Paint()
      ..color = Colors.white.withOpacity(0.8)
      ..strokeWidth = 1.5
      ..style = PaintingStyle.stroke;

    canvas.drawLine(Offset(ovalRect.left + 16, laserY), Offset(ovalRect.right - 16, laserY), laserLinePaint);
  }

  @override
  bool shouldRepaint(covariant _OvalHUDPainter oldDelegate) => 
      oldDelegate.scanLinePercent != scanLinePercent;
}
