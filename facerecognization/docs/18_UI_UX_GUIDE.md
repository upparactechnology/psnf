# UI/UX Specifications and Design Tokens

This document details the visual systems, design components, layouts, and accessibility guidelines for the Android Kiosk Application and React Admin Dashboard.

---

## 1. Core Color System (Design Tokens)

The system uses Material Design 3 (M3) color tokens, optimized for high visibility on tablet displays:

### Light Theme Colors
* **Primary**: `#005faf` (Action elements, primary buttons)
* **Secondary**: `#535f70` (Subtle visual indicators, icons)
* **Background**: `#fdfcff` (Screen backgrounds)
* **Surface**: `#f0f4f8` (Card components, input containers)
* **Error**: `#ba1a1a` (Spoof warnings, system errors)
* **Success**: `#008f39` (Successful attendance match cards)

### Dark Theme Colors
* **Primary**: `#a4c8ff`
* **Secondary**: `#bbc7db`
* **Background**: `#1a1c1e`
* **Surface**: `#2d3033`
* **Error**: `#ffb4ab`
* **Success**: `#4ef07d`

---

## 2. Typography & Spatial Grid

### 2.1 Typography (Google Fonts: Inter / Outfit)
* **Display Large** (Kiosk Timer): $57\text{pt}$ size, bold weight.
* **Title Large** (Header Areas): $22\text{pt}$ size, medium weight.
* **Body Large** (Standard Content): $16\text{pt}$ size, regular weight.
* **Label Medium** (Button Labels): $12\text{pt}$ size, medium weight.

### 2.2 Layout Grid & Spacing
* **Base Grid**: $8\text{dp}$ base grid system. Spacing increments: $8\text{dp}$, $16\text{dp}$, $24\text{dp}$, $32\text{dp}$, $48\text{dp}$.
* **Device Margins**: Kiosk layouts must use a minimum screen margin of $24\text{dp}$ to prevent edge-cropping on tablet bezels.

---

## 3. UI Component Specifications

```
┌───────────────────────────────────────────────────────────────┐
│                        COMPONENTS SYSTEM                      │
├───────────────────────┬──────────────────────┬────────────────┤
│    Standard Cards     │    System Dialogs    │ Loading States │
│                       │                      │                │
│ • Surface Color fill  │ • Confirmation modals│ • Skeletal placeholders
│ • 8dp border radius   │ • Auto-dismiss sheets│ • Linear status bar    │
│ • Minimal elevations  │ • Success/Error alerts • Spinner overlays     │
└───────────────────────┴──────────────────────┴────────────────┘
```

### 3.1 Dialogs (Success vs. Spoof)
* **Attendance Success Dialog**: Shows a green card overlay featuring the employee's name and designation, accompanied by a checkmark animation. This dialog auto-dismisses after 3 seconds.
* **Spoof Alert Dialog**: Shows a red card overlay indicating that liveness validation failed. Play a warning chime audio cue.

### 3.2 Skeleton Loaders
Use skeletal placeholders to indicate loading states during data retrieval, avoiding empty screens.

---

## 4. Kiosk Mode Optimization

The Android client layout is optimized for landscape tablet devices:

```
┌───────────────────────────────────────────────────────────────┐
│                      KIOSK LANDSCAPE GRID                     │
├───────────────────────────────────┬───────────────────────────┤
│                                   │                           │
│        Left: Camera Feed          │    Right: Status Panel    │
│             (60% width)           │         (40% width)       │
│                                   │                           │
└───────────────────────────────────┴───────────────────────────┘
```

* **Layout Split**: The screen uses a 60-40 horizontal split layout. The camera feed is on the left, and status panels, transaction counts, and connection states are on the right.
* **Interactive Elements**: Hide navigation controls and interactive UI inputs from the kiosk view to prevent unauthorized employee tampering.

For system architecture details, refer to [ARCHITECTURE.md](../ARCHITECTURE.md).
