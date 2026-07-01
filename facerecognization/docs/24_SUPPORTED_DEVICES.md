# Supported Hardware and Infrastructure Specifications

This document defines the physical hardware configurations, camera specifications, server targets, network infrastructure, and mounting guidelines.

---

## 1. Android Kiosk Device Specifications

```
┌───────────────────────────────────────────────────────────────┐
│                      KIOSK HARDWARE LIMITS                    │
├───────────────────────┬──────────────────────┬────────────────┤
│    Android OS API     │      System RAM      │   Camera Unit  │
│                       │                      │                │
│ • API Level 21+       │ • Minimum: 2 GB      │ • 5 MP minimum │
│ • Target: API 34+     │ • Recommended: 4 GB  │ • 1080p stream │
│ • Core support        │ • Octa-core CPU      │ • Fixed focus  │
└───────────────────────┴──────────────────────┴────────────────┘
```

To run real-time face tracking and liveness checks smoothly on the client device:

### 1.1 CPU and Memory Requirements
* **Minimum**: Quad-core $1.8\text{ GHz}$ CPU, $2\text{ GB}$ system RAM.
* **Recommended**: Octa-core $2.0\text{ GHz}$ or higher CPU, $4\text{ GB}$ system RAM.

### 1.2 Camera Requirements
* **Camera Module**: Minimum 5 Megapixels.
* **Aspect Ratio**: 4:3 or 16:9 aspect ratios.
* **Capture Profile**: Must capture video streams at a minimum of $1280 \times 720$ (720p) resolution at 30 frames per second.
* **Focus Profile**: Fixed focus lens modules are recommended; autofocus systems can introduce delay or focus-hunting blur.

### 1.3 Recommended Devices
* **Android Kiosk Tablets**: Samsung Galaxy Tab A9 (8.7"), Lenovo Tab M10 Gen 3, or Xiaomi Pad 6 (for high-volume entryways).

---

## 2. Server Infrastructure (Hostinger VPS Tiers)

The backend runs inside containerized environments on Ubuntu. Choose VPS tiers based on employee counts:

| Scaling Tier | Active Employees | Recommended VPS Profile | VCPU Cores | RAM size | Storage Space |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Tier 1** | $20 - 150$ | Hostinger KVM 2 | 2 Cores | 4 GB | 50 GB NVMe |
| **Tier 2** | $150 - 500$ | Hostinger KVM 4 | 4 Cores | 8 GB | 100 GB NVMe|
| **Tier 3** | $500 - 2000$ | Hostinger KVM 8 | 8 Cores | 16 GB | 200 GB NVMe|

---

## 3. Physical Installation and Mounting Guidelines

To ensure optimal recognition rates and prevent camera alignment issues:

```
                  WALL MOUNT HEIGHT GUIDELINE
                  
                           [ CAMERA ]   ◄─── Height: 145cm - 150cm
                           /    │
                          /     │
                         /      │
                  [ Employee ]  │
                  Distance:     │
                  50cm - 80cm   │
                                └─────────── Ground Floor
```

* **Mounting Height**: Mount the tablet with the camera positioned $145\text{cm} - 150\text{cm}$ above the ground, angled downward by $5^\circ - 10^\circ$ to accommodate varying employee heights.
* **User Distance**: The employee should stand between $50\text{cm} - 80\text{cm}$ away from the camera for optimal scans.
* **Lighting Guidelines**: Avoid mounting the kiosk facing direct sunlight, windows, or bright backlights. Ambient indoor lighting of $300 - 500\text{ lux}$ is recommended.
* **Power Source**: Use continuous wall outlets with a backing Uninterruptible Power Supply (UPS) to keep devices running during power outages.

For details on the face recognition algorithms and configurations, refer to [22_FACE_AI_CONFIGURATION.md](22_FACE_AI_CONFIGURATION.md).
