import 'dart:io';
import 'package:flutter/material.dart';
import 'package:camera/camera.dart';
import '../data/api_service.dart';

class RegisterPage extends StatefulWidget {
  const RegisterPage({super.key});

  @override
  State<RegisterPage> createState() => _RegisterPageState();
}

class _RegisterPageState extends State<RegisterPage> {
  final ApiService _apiService = ApiService();
  final _formKey = GlobalKey<FormState>();

  // Controllers
  final _employeeIdController = TextEditingController();
  final _firstNameController = TextEditingController();
  final _lastNameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();

  // Camera State
  CameraController? _cameraController;
  bool _cameraInitialized = false;
  XFile? _capturedImage;
  bool _isRegistering = false;

  @override
  void initState() {
    super.initState();
    _initializeCamera();
  }

  Future<void> _initializeCamera() async {
    try {
      final cameras = await availableCameras();
      if (cameras.isEmpty) return;

      // Select front camera as default for registration on kiosk
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
    } catch (e) {
      debugPrint("Failed to initialize camera in register page: $e");
    }
  }

  Future<void> _takePhoto() async {
    if (_cameraController == null || !_cameraController!.value.isInitialized) return;

    try {
      final image = await _cameraController!.takePicture();
      setState(() {
        _capturedImage = image;
      });
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text("Failed to snap photo: $e")),
      );
    }
  }

  void _retakePhoto() {
    setState(() {
      _capturedImage = null;
    });
  }

  Future<void> _registerStaff() async {
    if (!_formKey.currentState!.validate()) return;
    if (_capturedImage == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("Please snap a face photo first")),
      );
      return;
    }

    setState(() {
      _isRegistering = true;
    });

    final res = await _apiService.registerWithFace(
      employeeId: _employeeIdController.text.trim(),
      firstName: _firstNameController.text.trim(),
      lastName: _lastNameController.text.trim(),
      email: _emailController.text.trim(),
      phone: _phoneController.text.trim(),
      imagePath: _capturedImage!.path,
    );

    setState(() {
      _isRegistering = false;
    });

    if (res != null && res.containsKey('status') && res['status'] == 'SUCCESS') {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text("Staff '${_firstNameController.text}' registered successfully!"),
            backgroundColor: Colors.green,
          ),
        );
        Navigator.pop(context);
      }
    } else {
      final errMsg = res != null && res.containsKey('error') ? res['error'] : 'Unknown error';
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text("Registration failed: $errMsg"),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  @override
  void dispose() {
    _cameraController?.dispose();
    _employeeIdController.dispose();
    _firstNameController.dispose();
    _lastNameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F172A),
      appBar: AppBar(
        title: const Text("Register Staff & Face Profile", style: TextStyle(fontWeight: FontWeight.bold)),
        backgroundColor: const Color(0xFF1E293B),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 24.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // 1. Camera Frame / Capture Preview
              Center(
                child: Container(
                  width: 260,
                  height: 260,
                  decoration: BoxDecoration(
                    color: const Color(0xFF1E293B),
                    borderRadius: BorderRadius.circular(130),
                    border: Border.all(color: const Color(0xFF64FFDA), width: 3),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF64FFDA).withOpacity(0.15),
                        blurRadius: 20,
                        spreadRadius: 2,
                      )
                    ],
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(130),
                    child: _capturedImage != null
                        ? Image.file(
                            File(_capturedImage!.path),
                            fit: BoxFit.cover,
                          )
                        : (_cameraInitialized && _cameraController != null
                            ? AspectRatio(
                                aspectRatio: 1.0,
                                child: CameraPreview(_cameraController!),
                              )
                            : const Center(
                                child: CircularProgressIndicator(color: Color(0xFF64FFDA)),
                              )),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              // Camera Actions Button
              Center(
                child: _capturedImage == null
                    ? ElevatedButton.icon(
                        onPressed: _takePhoto,
                        icon: const Icon(Icons.camera_alt),
                        label: const Text("Capture Face"),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF64FFDA),
                          foregroundColor: const Color(0xFF0F172A),
                          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                          textStyle: const TextStyle(fontWeight: FontWeight.bold),
                        ),
                      )
                    : OutlinedButton.icon(
                        onPressed: _retakePhoto,
                        icon: const Icon(Icons.refresh),
                        label: const Text("Retake Photo"),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: const Color(0xFF64FFDA),
                          side: const BorderSide(color: Color(0xFF64FFDA)),
                          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                        ),
                      ),
              ),
              const SizedBox(height: 32),

              // 2. Input Fields Card
              Card(
                color: const Color(0xFF1E293B),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                child: Padding(
                  padding: const EdgeInsets.all(20.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      TextFormField(
                        controller: _employeeIdController,
                        style: const TextStyle(color: Colors.white),
                        decoration: const InputDecoration(
                          labelText: "Employee ID / Staff ID",
                          labelStyle: TextStyle(color: Colors.grey),
                          focusedBorder: UnderlineInputBorder(
                            borderSide: BorderSide(color: Color(0xFF64FFDA)),
                          ),
                        ),
                        validator: (v) => v == null || v.isEmpty ? "Required" : null,
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: TextFormField(
                              controller: _firstNameController,
                              style: const TextStyle(color: Colors.white),
                              decoration: const InputDecoration(
                                labelText: "First Name",
                                labelStyle: TextStyle(color: Colors.grey),
                                focusedBorder: UnderlineInputBorder(
                                  borderSide: BorderSide(color: Color(0xFF64FFDA)),
                                ),
                              ),
                              validator: (v) => v == null || v.isEmpty ? "Required" : null,
                            ),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: TextFormField(
                              controller: _lastNameController,
                              style: const TextStyle(color: Colors.white),
                              decoration: const InputDecoration(
                                labelText: "Last Name",
                                labelStyle: TextStyle(color: Colors.grey),
                                focusedBorder: UnderlineInputBorder(
                                  borderSide: BorderSide(color: Color(0xFF64FFDA)),
                                ),
                              ),
                              validator: (v) => v == null || v.isEmpty ? "Required" : null,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _emailController,
                        style: const TextStyle(color: Colors.white),
                        decoration: const InputDecoration(
                          labelText: "Email Address (Matches Main Portal User)",
                          labelStyle: TextStyle(color: Colors.grey),
                          focusedBorder: UnderlineInputBorder(
                            borderSide: BorderSide(color: Color(0xFF64FFDA)),
                          ),
                        ),
                        validator: (v) => v == null || v.isEmpty ? "Required" : null,
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _phoneController,
                        style: const TextStyle(color: Colors.white),
                        decoration: const InputDecoration(
                          labelText: "Phone (Optional)",
                          labelStyle: TextStyle(color: Colors.grey),
                          focusedBorder: UnderlineInputBorder(
                            borderSide: BorderSide(color: Color(0xFF64FFDA)),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 32),

              // 3. Submit Registration Button
              ElevatedButton(
                onPressed: _isRegistering ? null : _registerStaff,
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF64FFDA),
                  foregroundColor: const Color(0xFF0F172A),
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  disabledBackgroundColor: Colors.grey.withOpacity(0.3),
                ),
                child: _isRegistering
                    ? const SizedBox(
                        width: 24,
                        height: 24,
                        child: CircularProgressIndicator(color: Color(0xFF0F172A), strokeWidth: 2),
                      )
                    : const Text(
                        "Register Staff & Profile",
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
