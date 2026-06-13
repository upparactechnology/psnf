# Design System & UI Guide

This system aligns with a professional, Odoo-inspired ERP style: clean lines, generous white space, strong typography hierarchy, and a harmonized color palette.

---

## 1. Design Tokens (CSS Variables)

```css
:root {
  /* Harmonious Accessible Palettes */
  --color-primary-50: #f5f3ff;
  --color-primary-100: #ede9fe;
  --color-primary-500: #6366f1; /* Brand indigo */
  --color-primary-600: #4f46e5;
  --color-primary-700: #4338ca;

  --color-success: #10b981; /* Premium Emerald */
  --color-warning: #f59e0b; /* Amber */
  --color-danger: #ef4444;  /* Crimson Rose */
  --color-info: #0ea5e9;    /* Sky Blue */

  /* Neutral tones */
  --color-bg-base: #f8f8f8;
  --color-bg-surface: #ffffff;
  --color-text-primary: #1e293b;
  --color-text-muted: #64748b;
  --color-border: #e2e8f0;

  /* Shadows */
  --shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.03);
  --shadow-soft-hover: 0 12px 30px rgba(0, 0, 0, 0.07);
  --shadow-pill: 0 8px 30px rgba(0, 0, 0, 0.04);

  /* Typography */
  --font-sans: 'Inter', system-ui, -apple-system, sans-serif;
  --font-headers: 'Outfit', sans-serif;
}

/* High Contrast Override Theme */
[data-theme="high-contrast"] {
  --color-primary-50: #000000;
  --color-primary-100: #1a1a1a;
  --color-primary-500: #ffff00; /* High visible yellow */
  --color-primary-600: #ffff00;
  --color-primary-700: #ffffff;

  --color-bg-base: #000000;
  --color-bg-surface: #121212;
  --color-text-primary: #ffffff;
  --color-text-muted: #e2e8f0;
  --color-border: #ffffff;
}
```

---

## 2. Navigation Architecture
* **Module Launcher Dashboard:**
  * Clean grid cards displaying available applications with modern iconography (incorporating subtle SVG wiggle/bounce animations on card hover).
  * Fast-search bar filtering cards in real-time.
* **Module Layouts:**
  * **Dedicated Sidebar:** Fixed collapsable sidebar displaying functional paths for the current module.
  * **Top Navbar:** breadcrumb trail displaying deep hierarchy paths (e.g. `Dashboard / Transport / Vehicles / Edit (MH-12-AB-1234)`), quick notification bell, and user profile switcher.

---

## 3. UI Component Specifications

### 3.1 DataTable
* **Behavior:** Infinite scroll or performant pagination (default: 25 rows).
* **Controls:** Fixed header columns, multi-column sorting triggers, quick-filter toggles, checkable rows, and instant CSV/PDF export.

### 3.2 FormBuilder
* **Layout:** Grid system aligning fields based on screen width (1-column on mobile, up to 4-columns on wide monitors).
* **Validation:** Inline real-time feedback (invalid attributes trigger soft-tinted red borders with helpful error text; successful validation indicators display green ticks).

### 3.3 Calendar
* **Modes:** Monthly grid, weekly overview, and list timeline view.
* **Interactions:** Drag-and-drop support to re-schedule therapy or class tasks, color-coded based on category labels.

### 3.4 Kanban Board
* **States:** Drag-and-drop card columns tracking workflow status (e.g. student enrollment, transport loops, or maintenance checks).

### 3.5 Drawer & Modal
* **Drawer:** Slips in from the right edge covering 40% of the screen width to display quick editing parameters or check-in lists without losing context.
* **Modal:** Centered container for confirmation dialogues, with focus-trapping code active to intercept tab navigations.

---

## 4. Micro-Animations & Interactions

```css
/* Smooth visual transitions */
.transition-smooth {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

/* Float Animation for UI highlights */
@keyframes floatUp {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-6px); }
}
.animate-float {
  animation: floatUp 4s ease-in-out infinite;
}

/* Wiggle shake for notification bells / warnings */
@keyframes wiggleShake {
  0%, 100% { transform: rotate(0deg); }
  20% { transform: rotate(-8deg); }
  40% { transform: rotate(8deg); }
  60% { transform: rotate(-4deg); }
  80% { transform: rotate(4deg); }
}
.group:hover .animate-wiggle {
  animation: wiggleShake 0.6s ease-in-out;
}
```

---

## 5. Accessibility (WCAG 2.1 AA Checklist)
1. **Contrast Ratio:** Enforce a minimum text contrast ratio of 4.5:1 (7:1 in high contrast mode) for normal text and 3:1 for large text.
2. **Keyboard Navigation:** Everything reachable via standard Tab flows. Active elements must display clear focus rings (`outline: 3px solid var(--color-primary-500); outline-offset: 2px`).
3. **Screen Reader Compatibility:** Extensive utilization of descriptive ARIA tags (`aria-expanded`, `aria-live`, `aria-label`).
4. **Motion Control:** Check system preferences (`prefers-reduced-motion`) to dynamically mute or remove micro-animations.

