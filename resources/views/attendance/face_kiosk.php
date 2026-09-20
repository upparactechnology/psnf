<?php
// Prevent aggressive mobile/tablet browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$layout    = 'app';
$pageTitle = 'Face Attendance Kiosk';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Attendance'], ['label' => 'Face Kiosk']];
?>

<style>
/* ─── Kiosk Camera Wrapper ─────────────────────────────────── */
.kiosk-wrap {
    position: relative;
    width: 100%; max-width: 580px;
    margin: 0 auto;
    border-radius: 20px;
    overflow: hidden;
    background: transparent;
    border: 4px solid #6366f1;
    box-shadow: 0 0 30px rgba(99,102,241,0.15);
    aspect-ratio: 4/3;
}
#kioskVideo {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
    transform: scaleX(-1);
}
/* Dark vignette overlay - transparent in oval center */
#kioskOverlayCanvas {
    position: absolute;
    inset: 0;
    width: 100%; height: 100%;
    pointer-events: none;
    z-index: 3;
}
/* Scan line */
.kiosk-scan-line {
    position: absolute;
    left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, transparent 0%, #6366f1 30%, #a5b4fc 50%, #6366f1 70%, transparent 100%);
    box-shadow: 0 0 16px rgba(99,102,241,0.8);
    z-index: 4;
    top: 0;
    animation: scanMove 2.5s ease-in-out infinite;
    pointer-events: none;
}
@keyframes scanMove { 0%{top:0%} 50%{top:95%} 100%{top:0%} }

/* Face status label inside video */
.kiosk-face-label {
    position: absolute;
    bottom: 12px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 5;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .04em;
    padding: 5px 16px;
    border-radius: 99px;
    white-space: nowrap;
    pointer-events: none;
    transition: all 0.3s ease;
}
.kiosk-face-label.wait   { background: rgba(245,158,11,0.25); border: 1.5px solid #f59e0b; color: #fbbf24; }
.kiosk-face-label.ready  { background: rgba(34,197,94,0.25);  border: 1.5px solid #22c55e; color: #4ade80; }
.kiosk-face-label.scan   { background: rgba(99,102,241,0.35); border: 1.5px solid #818cf8; color: #c7d2fe; }

/* Result panel */
.k-result {
    border-radius: 16px;
    padding: 1.1rem 1rem;
    transition: all 0.3s ease;
}
.k-feed-item {
    padding: 0.55rem 0.8rem;
    border-left: 3px solid #22c55e;
    border-radius: 0 10px 10px 0;
    background: rgba(34,197,94,0.07);
    margin-bottom: 0.45rem;
}
.k-pulse { animation: kpulse 1.4s ease-in-out infinite; }
@keyframes kpulse { 0%,100%{opacity:1} 50%{opacity:.35} }
.digital-clock {
    font-family: 'Courier New', monospace;
    font-weight: 800;
    letter-spacing: .08em;
    background: linear-gradient(135deg, #6366f1, #a5b4fc);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.backend-offline-banner {
    display: none;
    border-radius: 16px;
    background: rgba(239,68,68,0.08);
    border: 1.5px solid rgba(239,68,68,0.35);
    padding: 1rem 1.25rem;
}
.backend-offline-banner.show {
    animation: offlinePulse 2.5s ease-in-out infinite;
}
@keyframes offlinePulse { 0%,100%{border-color:rgba(239,68,68,0.35)} 50%{border-color:rgba(239,68,68,0.75)} }

/* ─── Low-Memory Device Optimizations ──────────────────────── */
.low-memory .kiosk-wrap {
    border-width: 3px;
    border-radius: 16px;
    box-shadow: 0 0 15px rgba(99,102,241,0.1);
}
.low-memory .kiosk-scan-line { box-shadow: none; }
.low-memory .backend-offline-banner { animation: none; }

/* ─── Tablet Responsive Layout ──────────────────────────────── */
@media (max-width: 900px) {
    .kiosk-header-row {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 8px !important;
    }
    .kiosk-header-badges {
        flex-wrap: wrap !important;
        gap: 6px !important;
        width: 100%;
    }
    .kiosk-header-badges .digital-clock { font-size: 1.1rem !important; }
    .kiosk-main-grid { grid-template-columns: 1fr !important; }
    .kiosk-camera-col { order: 1 !important; }
    .kiosk-right-col { order: 2 !important; }
    .kiosk-wrap {
        max-width: 100% !important;
        border-radius: 16px !important;
        border-width: 3px !important;
    }
    .kiosk-stats-row { grid-template-columns: repeat(3, 1fr) !important; gap: 6px !important; }
    .kiosk-camera-panel { padding: 12px !important; }
    .kiosk-right-panel { padding: 12px !important; }
    .kiosk-face-label { font-size: 10px !important; padding: 4px 12px !important; }
    .k-result { padding: 0.8rem 0.7rem !important; }
    .k-feed-item { padding: 0.4rem 0.6rem !important; }
}
@media (max-width: 900px) and (orientation: portrait) {
    .kiosk-wrap { aspect-ratio: 3/4 !important; }
}
@media (max-width: 900px) and (orientation: landscape) {
    .kiosk-wrap { aspect-ratio: 16/9 !important; max-height: 60vh !important; }
}
@media (max-width: 480px) {
    .kiosk-header-badges { width: 100% !important; }
    .kiosk-stats-row { grid-template-columns: 1fr !important; gap: 4px !important; }
}

/* ─── TEMPORARY DETECTOR DIAGNOSTICS ─────────────────────────── */
.dd-panel {
    position: fixed;
    bottom: 16px;
    right: 16px;
    z-index: 1000;
    border-radius: 14px;
    background: rgba(15,23,42,0.92);
    border: 1px solid rgba(99,102,241,0.3);
    backdrop-filter: blur(12px);
    padding: 14px 16px;
    font-family: 'Courier New', monospace;
    font-size: 11px;
    color: #94a3b8;
    max-width: 360px;
    min-width: 280px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.5);
}
.dd-title {
    font-size: 12px;
    font-weight: 700;
    color: #a5b4fc;
    margin-bottom: 2px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.dd-build {
    font-size: 9px;
    color: #6366f1;
    font-weight: 400;
    background: rgba(99,102,241,0.12);
    padding: 1px 6px;
    border-radius: 4px;
}
.dd-stages { margin-top: 8px; }
.dd-stage {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 2px 0;
    line-height: 1.4;
}
.dd-dot {
    width: 14px;
    text-align: center;
    flex-shrink: 0;
    color: #475569;
    font-size: 12px;
}
.dd-success .dd-dot { color: #22c55e; }
.dd-failed .dd-dot { color: #ef4444; }
.dd-running .dd-dot { color: #f59e0b; }
.dd-label { flex: 1; }
.dd-success .dd-label { color: #4ade80; }
.dd-failed .dd-label { color: #f87171; }
.dd-running .dd-label { color: #fbbf24; }
.dd-err {
    display: none;
    margin-left: 22px;
    padding: 4px 8px;
    margin-top: 1px;
    margin-bottom: 3px;
    background: rgba(239,68,68,0.1);
    border: 1px solid rgba(239,68,68,0.2);
    border-radius: 6px;
    color: #fca5a5;
    font-size: 10px;
    word-break: break-word;
    line-height: 1.3;
}
.dd-buttons {
    margin-top: 10px;
    display: flex;
    gap: 8px;
}
.dd-btn {
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 10px;
    font-weight: 700;
    cursor: pointer;
    border: 1px solid rgba(99,102,241,0.3);
    background: rgba(99,102,241,0.15);
    color: #a5b4fc;
    transition: all 0.2s;
    font-family: 'Courier New', monospace;
}
.dd-btn:hover {
    background: rgba(99,102,241,0.3);
    color: #c7d2fe;
}
.dd-btn:active { transform: scale(0.97); }
.dd-detail {
    margin-top: 8px;
    padding: 8px 10px;
    background: rgba(239,68,68,0.06);
    border: 1px solid rgba(239,68,68,0.15);
    border-radius: 8px;
    font-size: 10px;
    line-height: 1.45;
    color: #cbd5e1;
    word-break: break-word;
    max-height: 260px;
    overflow-y: auto;
}
.dd-detail-row {
    padding: 2px 0;
    display: flex;
    gap: 6px;
}
.dd-detail-label {
    color: #94a3b8;
    flex-shrink: 0;
    min-width: 90px;
    font-weight: 700;
}
.dd-detail-value {
    color: #e2e8f0;
    flex: 1;
}
.dd-detail-value.err { color: #fca5a5; }
.dd-detail-value pre {
    margin: 2px 0 0 0;
    white-space: pre-wrap;
    font-family: 'Courier New', monospace;
    font-size: 9px;
    color: #f87171;
    background: rgba(0,0,0,0.3);
    padding: 4px 6px;
    border-radius: 4px;
    max-height: 80px;
    overflow-y: auto;
}
@media (max-width: 900px) {
    .dd-panel {
        bottom: 8px;
        right: 8px;
        left: 8px;
        max-width: none;
        min-width: 0;
        padding: 12px 14px;
        font-size: 12px;
    }
    .dd-btn { padding: 8px 14px; font-size: 11px; }
}
</style>

<div class="space-y-5" id="kioskRoot">

    <!-- Header -->
    <div class="kiosk-header-row flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl border border-slate-800/60 bg-slate-900/50 backdrop-blur">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-white">Face Attendance Kiosk</h1>
                <p class="text-xs text-slate-400 mt-0.5">Align face inside the oval — attendance marks automatically</p>
            </div>
        </div>
        <div class="kiosk-header-badges flex items-center gap-3">
            <div class="digital-clock text-2xl" id="kioskClock">--:-- --</div>
            <span id="detectorBadge" class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-2xs font-bold bg-slate-700 text-slate-400 border border-slate-600">
                <span class="w-2 h-2 rounded-full bg-slate-500"></span><span id="detectorBadgeText">Loading...</span>
            </span>
            <span id="engineBadge" class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-2xs font-bold bg-slate-700 text-slate-400 border border-slate-600">
                <span class="w-2 h-2 rounded-full bg-slate-500"></span><span id="engineBadgeText">Checking...</span>
            </span>
            <?php if (function_exists('auth') && auth()): ?>
            <a href="<?= url('attendance/face-register') ?>" class="px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition shadow-sm">
                + Register Face
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Python / InsightFace Backend Offline Banner -->
    <div id="backendOfflineBanner" class="backend-offline-banner">
        <div class="flex items-start gap-3">
            <span class="text-red-400 text-xl mt-0.5">⚠</span>
            <div class="flex-1">
                <div class="text-sm font-bold text-red-300 mb-1">InsightFace Backend Offline</div>
                <div class="text-xs text-red-400/80 mb-2">The Python recognition service is not running. Face attendance will not work until it is started.</div>
                <code class="block text-2xs text-amber-300 bg-slate-900/70 rounded-lg px-3 py-2 font-mono leading-relaxed">
                    cd C:\xampp\htdocs\psnf\backend<br>
                    python -m uvicorn app:app --host 0.0.0.0 --port 8000 --reload
                </code>
            </div>
            <button onclick="checkBackend()" class="shrink-0 px-2.5 py-1.5 rounded-lg text-2xs font-bold text-white bg-red-600/70 hover:bg-red-500 transition">Retry</button>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="kiosk-main-grid grid grid-cols-1 lg:grid-cols-12 gap-5">

        <!-- Camera Column -->
        <div class="kiosk-camera-col lg:col-span-7 space-y-4">
            <div class="kiosk-camera-panel rounded-2xl border border-slate-800/60 bg-slate-900/40 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-400 k-pulse"></span> Camera Feed
                    </h3>
                    <div class="flex items-center gap-2">
                        <span class="text-2xs text-slate-400 font-mono" id="scanStatusLabel">Initializing...</span>
                        <button onclick="toggleCam()" class="p-1.5 rounded-lg hover:bg-slate-800 transition text-slate-400 hover:text-white" title="Flip Camera">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                        </button>
                        <button onclick="restartCam()" class="p-1.5 rounded-lg hover:bg-slate-800 transition text-slate-400 hover:text-white" title="Restart">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Camera with overlay canvas -->
                <div class="kiosk-wrap" style="position: relative;">
                    <video id="kioskVideo" autoplay playsinline muted></video>
                    <canvas id="kioskOverlayCanvas"></canvas>
                    <div class="kiosk-scan-line" id="scanLine" style="display:none;"></div>
                    <div class="kiosk-face-label wait" id="faceLbl">👤 Position your face inside the guide</div>
                    <canvas id="kioskCapCanvas" style="display:none;"></canvas>
                    
                    <!-- Result Overlay Centered and Big -->
                    <div id="kioskResultOverlay" style="position: absolute; inset: 0px; display: none; flex-direction: column; align-items: center; justify-content: center; z-index: 99; transition: opacity 0.3s ease; opacity: 0; pointer-events: none; background: #0f172a;">
                        <div id="kioskResultOverlayContent" style="text-align: center; padding: 24px; transition: transform 0.3s ease; transform: scale(0.9); display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        </div>
                    </div>
                </div>

                <p class="text-2xs text-slate-500 text-center mt-3">
                    <svg class="w-3.5 h-3.5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Only scans when your face fills the oval. Background outside oval is darkened.
                </p>
            </div>

            <!-- Stats -->
            <div class="kiosk-stats-row grid grid-cols-3 gap-3">
                <div class="rounded-xl border border-slate-800/60 bg-slate-900/40 p-3 text-center">
                    <div class="text-lg font-bold text-emerald-400" id="statChecked">0</div>
                    <div class="text-2xs text-slate-500 mt-0.5">Checked In Today</div>
                </div>
                <div class="rounded-xl border border-slate-800/60 bg-slate-900/40 p-3 text-center">
                    <div class="text-lg font-bold text-indigo-400" id="statConf">—</div>
                    <div class="text-2xs text-slate-500 mt-0.5">Last Confidence</div>
                </div>
                <div class="rounded-xl border border-slate-800/60 bg-slate-900/40 p-3 text-center">
                    <div class="text-lg font-bold text-amber-400" id="statScans">0</div>
                    <div class="text-2xs text-slate-500 mt-0.5">Scans This Session</div>
                </div>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="kiosk-right-col lg:col-span-5 space-y-4">

            <!-- Status Panel -->
            <div class="kiosk-right-panel rounded-2xl border border-slate-800/60 bg-slate-900/40 p-5 shadow-sm">
                <h3 class="text-sm font-bold text-white mb-3">Verification Status</h3>
                <div id="kioskResult" class="k-result border border-slate-700 bg-slate-800/60 text-center">
                    <div class="py-4">
                        <svg class="w-10 h-10 mx-auto text-slate-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-xs text-slate-500 font-medium">Waiting for face...</p>
                    </div>
                </div>
            </div>

            <!-- Recent Check-Ins -->
            <div class="kiosk-right-panel rounded-2xl border border-slate-800/60 bg-slate-900/40 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-white">Recent Check-Ins</h3>
                    <span class="text-2xs text-slate-500 font-mono" id="kioskDate"></span>
                </div>
                <div id="kioskFeed"><div class="text-xs text-slate-500 text-center py-4">No check-ins yet today.</div></div>
            </div>

        </div>
    </div>

    <!-- ── TEMPORARY DETECTOR DIAGNOSTICS ── -->
    <div class="dd-panel" id="ddPanel">
        <div class="dd-title">
            Detector Diagnostics
            <span class="dd-build" id="ddBuild">v2</span>
        </div>
        <div class="dd-stages">
            <div class="dd-stage" id="ddJsStarted"><span class="dd-dot">○</span><span class="dd-label">JavaScript started</span><span class="dd-err" id="ddJsStartedError"></span></div>
            <div class="dd-stage" id="ddMediaPipe"><span class="dd-dot">○</span><span class="dd-label">MediaPipe import</span><span class="dd-err" id="ddMediaPipeError"></span></div>
            <div class="dd-stage" id="ddWasmInit"><span class="dd-dot">○</span><span class="dd-label">WASM initialization</span><span class="dd-err" id="ddWasmInitError"></span></div>
            <div class="dd-stage" id="ddModel"><span class="dd-dot">○</span><span class="dd-label">Model download</span><span class="dd-err" id="ddModelError"></span></div>
            <div class="dd-stage" id="ddCpuDetector"><span class="dd-dot">○</span><span class="dd-label">CPU detector</span><span class="dd-err" id="ddCpuDetectorError"></span></div>
            <div class="dd-stage" id="ddGpuDetector"><span class="dd-dot">○</span><span class="dd-label">GPU detector</span><span class="dd-err" id="ddGpuDetectorError"></span></div>
            <div class="dd-stage" id="ddDetectorReady"><span class="dd-dot">○</span><span class="dd-label">Detector ready</span><span class="dd-err" id="ddDetectorReadyError"></span></div>
            <div class="dd-stage" id="ddFaceDetection"><span class="dd-dot">○</span><span class="dd-label">Face detection</span><span class="dd-err" id="ddFaceDetectionError"></span></div>
        </div>
        <div class="dd-detail" id="ddImportDiag" style="display:none"></div>
        <div class="dd-buttons">
            <button class="dd-btn" id="ddRetryBtn">Retry Detector</button>
            <button class="dd-btn" id="ddCopyBtn">Copy Diagnostics</button>
        </div>
    </div>
</div>

<script>
(function() {
    const API  = '<?= url('attendance/api.php?endpoint=/api/recognize-face') ?>';
    const vid  = document.getElementById('kioskVideo');
    const cap  = document.getElementById('kioskCapCanvas');
    const ovl  = document.getElementById('kioskOverlayCanvas');
    const lbl  = document.getElementById('faceLbl');
    const scanLine = document.getElementById('scanLine');
    const resultEl = document.getElementById('kioskResult');
    const feedEl   = document.getElementById('kioskFeed');
    const overlay  = document.getElementById('kioskResultOverlay');
    const overlayContent = document.getElementById('kioskResultOverlayContent');

    let isProcessing  = false;
    let pauseUntil    = 0;
    let scanCount     = 0;
    let checkedToday  = 0;
    let faceReady     = false;
    let faceCheckFrame = 0;
    let currentFacingMode = 'user';

    // ── Device Capability Detection ──────────────────────────────────────────
    const deviceMemory = navigator.deviceMemory || 0;
    const hwConcurrency = navigator.hardwareConcurrency || 2;
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    const isTablet = isMobile && (navigator.maxTouchPoints > 1 || /tablet|iPad/i.test(navigator.userAgent));
    const isLowMemory = deviceMemory > 0 && deviceMemory <= 2;
    const isLowEnd = isLowMemory || (isMobile && hwConcurrency <= 4);

    if (isLowEnd) {
        document.getElementById('kioskRoot').classList.add('low-memory');
    }

    // Adaptive detection interval: low-end ~500ms (2 FPS), normal ~280ms (3-4 FPS)
    const DETECT_INTERVAL = isLowEnd ? 500 : 280;
    const DRAW_FPS = isLowEnd ? 18 : 30;

    console.log('[Detector] deviceMemory:', navigator.deviceMemory);
    console.log('[Detector] hardwareConcurrency:', navigator.hardwareConcurrency);
    console.log('[Detector] isMobile:', isMobile);
    console.log('[Detector] isLowMemory:', isLowMemory);

    // Delegate strategy: mobile/low-memory → CPU first, desktop → GPU first
    const primaryDelegate   = (isMobile || isLowMemory) ? 'CPU' : 'GPU';
    const fallbackDelegate  = primaryDelegate === 'CPU' ? 'GPU' : 'CPU';

    console.log('[Detector] primaryDelegate:', primaryDelegate);
    console.log('[Detector] fallbackDelegate:', fallbackDelegate);

    // ── TEMPORARY DETECTOR DIAGNOSTICS ──────────────────────────────────────
    const DD_BUILD = 'v2-tablet-diagnostics';
    console.log('[DetectorDebug] BUILD:', DD_BUILD);
    document.getElementById('ddBuild').textContent = DD_BUILD;

    const dd = {
        jsStarted: 'success',
        mediaPipeImport: 'pending',
        wasmInit: 'pending',
        modelDownload: 'pending',
        cpuDetector: 'pending',
        gpuDetector: 'pending',
        detectorReady: 'pending',
        faceDetection: 'pending',
        cpuError: null,
        gpuError: null,
        lastDetectError: null,
        detectErrorCount: 0,
        selectedDelegate: null,
        savedVision: null,
        savedFileset: null,
        importError: null,
        importFetchDiag: null
    };

    var IMPORT_URL = 'https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.18/vision_bundle.mjs';

    function ddUpdate(stage, state, errorMsg) {
        var el = document.getElementById('dd' + stage.charAt(0).toUpperCase() + stage.slice(1));
        if (!el) {
            el = document.getElementById('dd' + stage);
        }
        if (el) {
            el.className = 'dd-stage' + (state !== 'pending' ? ' dd-' + state : '');
            var dot = el.querySelector('.dd-dot');
            if (dot) {
                if (state === 'success') dot.textContent = '\u2713';
                else if (state === 'failed') dot.textContent = '\u2717';
                else if (state === 'running') dot.textContent = '\u25CE';
                else dot.textContent = '\u25CB';
            }
        }
        var errEl = document.getElementById('dd' + stage.charAt(0).toUpperCase() + stage.slice(1) + 'Error');
        if (!errEl) {
            errEl = document.getElementById('dd' + stage + 'Error');
        }
        if (errEl) {
            if (errorMsg) {
                errEl.textContent = errorMsg;
                errEl.style.display = 'block';
            } else {
                errEl.textContent = '';
                errEl.style.display = 'none';
            }
        }
    }

    function runImportDiag() {
        console.log('[DetectorDebug] Fetch diagnostic started for:', IMPORT_URL);
        return fetch(IMPORT_URL, { method: 'GET', mode: 'cors' })
            .then(function(response) {
                var info = {
                    fetchStatus: response.status,
                    fetchOk: response.ok,
                    fetchStatusText: response.statusText || 'N/A',
                    fetchUrl: response.url || IMPORT_URL,
                    fetchRedirected: response.redirected,
                    fetchType: 'N/A',
                    fetchLength: 'N/A'
                };
                try {
                    if (response.headers) {
                        info.fetchType = response.headers.get('Content-Type') || 'N/A';
                        info.fetchLength = response.headers.get('Content-Length') || 'N/A';
                    }
                } catch(hErr) {}
                return response.text().then(function(txt) {
                    info.fetchTotalChars = txt.length;
                    info.fetchFirst200 = txt.substring(0, 200);
                    console.log('[DetectorDebug] Fetch result:', JSON.stringify(info, null, 2));
                    return info;
                });
            })
            .catch(function(e) {
                var info = {
                    fetchError: e.message || String(e),
                    fetchErrorName: e.name || 'unknown'
                };
                console.error('[DetectorDebug] Fetch diagnostic error:', info);
                return info;
            });
    }

    function showImportDiag(errInfo, fetchInfo) {
        var el = document.getElementById('ddImportDiag');
        if (!el) return;
        var html = '';
        html += '<div class="dd-detail-row"><span class="dd-detail-label">error.name:</span><span class="dd-detail-value err">' + escapeHtml(errInfo.name) + '</span></div>';
        html += '<div class="dd-detail-row"><span class="dd-detail-label">error.message:</span><span class="dd-detail-value err">' + escapeHtml(errInfo.message) + '</span></div>';
        html += '<div class="dd-detail-row"><span class="dd-detail-label">error.stack:</span><span class="dd-detail-value"><pre>' + escapeHtml(errInfo.stack) + '</pre></span></div>';
        html += '<div class="dd-detail-row"><span class="dd-detail-label">userAgent:</span><span class="dd-detail-value">' + escapeHtml(navigator.userAgent) + '</span></div>';
        html += '<div class="dd-detail-row"><span class="dd-detail-label">document URL:</span><span class="dd-detail-value">' + escapeHtml(document.URL) + '</span></div>';
        html += '<div class="dd-detail-row"><span class="dd-detail-label">import URL:</span><span class="dd-detail-value">' + escapeHtml(IMPORT_URL) + '</span></div>';
        html += '<div style="border-top:1px solid rgba(239,68,68,0.15);margin:6px 0;"></div>';
        if (fetchInfo) {
            if (fetchInfo.fetchError) {
                html += '<div class="dd-detail-row"><span class="dd-detail-label">fetch error:</span><span class="dd-detail-value err">' + escapeHtml(fetchInfo.fetchError) + '</span></div>';
            } else {
                html += '<div class="dd-detail-row"><span class="dd-detail-label">HTTP status:</span><span class="dd-detail-value">' + fetchInfo.fetchStatus + ' (ok: ' + fetchInfo.fetchOk + ')</span></div>';
                html += '<div class="dd-detail-row"><span class="dd-detail-label">Content-Type:</span><span class="dd-detail-value">' + escapeHtml(fetchInfo.fetchType) + '</span></div>';
                html += '<div class="dd-detail-row"><span class="dd-detail-label">Content-Length:</span><span class="dd-detail-value">' + escapeHtml(String(fetchInfo.fetchLength)) + '</span></div>';
                html += '<div class="dd-detail-row"><span class="dd-detail-label">Total chars:</span><span class="dd-detail-value">' + fetchInfo.fetchTotalChars + '</span></div>';
                html += '<div class="dd-detail-row"><span class="dd-detail-label">First 200 chars:</span></div>';
                html += '<div class="dd-detail-value"><pre>' + escapeHtml(fetchInfo.fetchFirst200 || '') + '</pre></div>';
            }
        }
        el.innerHTML = html;
        el.style.display = 'block';
    }

    function logCapabilities() {
        var webgl = false, webgl2 = false;
        try { webgl = !!document.createElement('canvas').getContext('webgl'); } catch(e) {}
        try { webgl2 = !!document.createElement('canvas').getContext('webgl2'); } catch(e) {}
        var caps = {
            userAgent: navigator.userAgent,
            platform: navigator.platform,
            hardwareConcurrency: navigator.hardwareConcurrency,
            deviceMemory: navigator.deviceMemory || 'N/A',
            maxTouchPoints: navigator.maxTouchPoints,
            isSecureContext: window.isSecureContext,
            protocol: location.protocol,
            hostname: location.hostname,
            mediaDevices: !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia),
            webAssembly: typeof WebAssembly !== 'undefined',
            webgl: webgl,
            webgl2: webgl2,
            isMobile: isMobile,
            isTablet: isTablet,
            isLowMemory: isLowMemory,
            isLowEnd: isLowEnd,
            primaryDelegate: primaryDelegate,
            fallbackDelegate: fallbackDelegate
        };
        console.log('[DetectorDebug] Capabilities:', JSON.stringify(caps, null, 2));
        return caps;
    }
    logCapabilities();
    ddUpdate('JsStarted', 'success');

    window.ddRetryDetector = function() {
        console.log('[DetectorDebug] Retry requested');
        if (faceDetector) {
            try {
                if (typeof faceDetector.close === 'function') faceDetector.close();
                else if (typeof faceDetector.destroy === 'function') faceDetector.destroy();
            } catch(e) { console.warn('[DetectorDebug] Destroy error:', e.message); }
            faceDetector = null;
        }
        detectorReady = false;
        detectorLoading = false;
        detectorError = false;
        dd.mediaPipeImport = 'pending';
        dd.wasmInit = 'pending';
        dd.modelDownload = 'pending';
        dd.cpuDetector = 'pending';
        dd.gpuDetector = 'pending';
        dd.detectorReady = 'pending';
        dd.faceDetection = 'pending';
        dd.cpuError = null;
        dd.gpuError = null;
        dd.lastDetectError = null;
        dd.detectErrorCount = 0;
        dd.selectedDelegate = null;
        dd.savedVision = null;
        dd.savedFileset = null;
        ['MediaPipe','WasmInit','Model','CpuDetector','GpuDetector','DetectorReady','FaceDetection'].forEach(function(s) {
            ddUpdate(s, 'pending');
        });
        updateDetectorBadge('loading');
        loadFaceDetector();
    };

    window.ddCopyDiagnostics = function() {
        var caps = logCapabilities();
        var lines = [
            '=== Face Kiosk Detector Diagnostics ===',
            'Build: ' + DD_BUILD,
            '',
            '--- Browser Capabilities ---',
            'User Agent: ' + caps.userAgent,
            'Platform: ' + caps.platform,
            'Hardware Concurrency: ' + caps.hardwareConcurrency,
            'Device Memory: ' + caps.deviceMemory,
            'Max Touch Points: ' + caps.maxTouchPoints,
            'Secure Context: ' + caps.isSecureContext,
            'Protocol: ' + caps.protocol,
            'Hostname: ' + caps.hostname,
            'MediaDevices: ' + caps.mediaDevices,
            'WebAssembly: ' + caps.webAssembly,
            'WebGL: ' + caps.webgl,
            'WebGL2: ' + caps.webgl2,
            '',
            '--- Detector Results ---',
            'JS Started: ' + dd.jsStarted,
            'MediaPipe Import: ' + dd.mediaPipeImport,
            'WASM Init: ' + dd.wasmInit,
            'Model Download: ' + dd.modelDownload,
            'CPU Detector: ' + dd.cpuDetector + (dd.cpuError ? ' (Error: ' + dd.cpuError + ')' : ''),
            'GPU Detector: ' + dd.gpuDetector + (dd.gpuError ? ' (Error: ' + dd.gpuError + ')' : ''),
            'Selected Delegate: ' + (dd.selectedDelegate || 'none'),
            'Detector Ready: ' + dd.detectorReady,
            'Face Detection: ' + dd.faceDetection,
            'Last detectForVideo error: ' + (dd.lastDetectError || 'none'),
            'detectForVideo error count: ' + dd.detectErrorCount
        ].join('\n');
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(lines).then(function() {
                alert('Diagnostics copied to clipboard.');
            }).catch(function() {
                prompt('Copy diagnostics:', lines);
            });
        } else {
            prompt('Copy diagnostics:', lines);
        }
    };
    // ── END TEMPORARY DETECTOR DIAGNOSTICS ──────────────────────────────────

    // ── Browser face detector (MediaPipe BlazeFace) ─────────────────────────
    let faceDetector = null;
    let detectorReady = false;
    let detectorLoading = false;
    let detectorError = false;

    function updateDetectorBadge(state) {
        const detectorBadge = document.getElementById('detectorBadge');
        const detectorBadgeText = document.getElementById('detectorBadgeText');
        if (!detectorBadge) return;
        if (state === 'ready') {
            detectorBadge.className = 'flex items-center gap-1.5 px-3 py-1.5 rounded-full text-2xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
            detectorBadge.querySelector('span').className = 'w-2 h-2 rounded-full bg-emerald-400 k-pulse';
            detectorBadgeText.textContent = 'Detector Ready';
        } else if (state === 'loading') {
            detectorBadge.className = 'flex items-center gap-1.5 px-3 py-1.5 rounded-full text-2xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20';
            detectorBadge.querySelector('span').className = 'w-2 h-2 rounded-full bg-amber-400 k-pulse';
            detectorBadgeText.textContent = 'Loading Detector...';
        } else {
            detectorBadge.className = 'flex items-center gap-1.5 px-3 py-1.5 rounded-full text-2xs font-bold bg-red-500/10 text-red-400 border border-red-500/20';
            detectorBadge.querySelector('span').className = 'w-2 h-2 rounded-full bg-red-400';
            detectorBadgeText.textContent = 'Detector Error';
        }
    }

    async function createFaceDetector(vision, filesetResolver, delegate) {
        console.log('[Detector] Creating detector with delegate:', delegate);
        return await vision.FaceDetector.createFromOptions(filesetResolver, {
            baseOptions: {
                modelAssetPath: 'https://storage.googleapis.com/mediapipe-models/face_detector/blaze_face_short_range/float16/1/blaze_face_short_range.tflite',
                delegate: delegate
            },
            runningMode: 'VIDEO',
            minDetectionConfidence: 0.5
        });
    }

    async function loadFaceDetector() {
        if (detectorLoading || detectorReady) return;
        detectorLoading = true;
        updateDetectorBadge('loading');

        const t0 = performance.now();
        console.log('[DetectorDebug] initialization started');

        try {
            // Step 1: Import MediaPipe
            ddUpdate('MediaPipe', 'running');
            console.log('[DetectorDebug] MediaPipe import started');
            const vision = await import('https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.18/vision_bundle.mjs');
            dd.savedVision = vision;
            ddUpdate('MediaPipe', 'success');
            dd.mediaPipeImport = 'success';
            console.log('[DetectorDebug] MediaPipe import completed');

            // Step 2: Initialize WASM
            ddUpdate('WasmInit', 'running');
            console.log('[DetectorDebug] WASM initialization started');
            const filesetResolver = await vision.FilesetResolver.forVisionTasks(
                'https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.18/wasm'
            );
            dd.savedFileset = filesetResolver;
            ddUpdate('WasmInit', 'success');
            dd.wasmInit = 'success';
            console.log('[DetectorDebug] WASM initialization completed');

            // Step 3: Test CPU detector independently
            ddUpdate('CpuDetector', 'running');
            console.log('[DetectorDebug] CPU detector creation started');
            var cpuDetector = null;
            try {
                cpuDetector = await createFaceDetector(vision, filesetResolver, 'CPU');
                ddUpdate('CpuDetector', 'success');
                dd.cpuDetector = 'success';
                console.log('[DetectorDebug] CPU detector created successfully');
            } catch (e) {
                dd.cpuError = e.message || String(e);
                dd.cpuDetector = 'failed';
                ddUpdate('CpuDetector', 'failed', dd.cpuError);
                console.error('[DetectorDebug] CPU detector creation failed:', dd.cpuError);
            }

            // Step 4: Test GPU detector independently
            ddUpdate('GpuDetector', 'running');
            console.log('[DetectorDebug] GPU detector creation started');
            var gpuDetector = null;
            try {
                gpuDetector = await createFaceDetector(vision, filesetResolver, 'GPU');
                ddUpdate('GpuDetector', 'success');
                dd.gpuDetector = 'success';
                console.log('[DetectorDebug] GPU detector created successfully');
            } catch (e) {
                dd.gpuError = e.message || String(e);
                dd.gpuDetector = 'failed';
                ddUpdate('GpuDetector', 'failed', dd.gpuError);
                console.error('[DetectorDebug] GPU detector creation failed:', dd.gpuError);
            }

            // Model was downloaded during detector creation
            if (cpuDetector || gpuDetector) {
                ddUpdate('Model', 'success');
                dd.modelDownload = 'success';
                console.log('[DetectorDebug] Model download completed');
            } else {
                ddUpdate('Model', 'failed', 'Model download failed with both delegates');
                dd.modelDownload = 'failed';
            }

            // Step 5: Select the best available detector
            if (cpuDetector && gpuDetector) {
                faceDetector = (primaryDelegate === 'GPU') ? gpuDetector : cpuDetector;
                dd.selectedDelegate = (primaryDelegate === 'GPU') ? 'GPU' : 'CPU';
                console.log('[DetectorDebug] Both delegates work, selected:', dd.selectedDelegate);
            } else if (cpuDetector) {
                faceDetector = cpuDetector;
                dd.selectedDelegate = 'CPU';
                console.log('[DetectorDebug] Only CPU works, selected: CPU');
            } else if (gpuDetector) {
                faceDetector = gpuDetector;
                dd.selectedDelegate = 'GPU';
                console.log('[DetectorDebug] Only GPU works, selected: GPU');
            } else {
                faceDetector = null;
                throw new Error('Both CPU and GPU failed. CPU: ' + (dd.cpuError || 'unknown') + ' | GPU: ' + (dd.gpuError || 'unknown'));
            }

            var elapsed = Math.round(performance.now() - t0);
            detectorReady = true;
            detectorLoading = false;
            dd.detectorReady = 'success';
            ddUpdate('DetectorReady', 'success');
            updateDetectorBadge('ready');
            console.log('[DetectorDebug] Detector ready in ' + elapsed + 'ms with delegate:', dd.selectedDelegate);

        } catch (e) {
            var elapsed = Math.round(performance.now() - t0);
            detectorLoading = false;
            detectorError = true;
            dd.detectorReady = 'failed';
            ddUpdate('DetectorReady', 'failed', e.message || String(e));
            console.error('[DetectorDebug] initialization failed after ' + elapsed + 'ms:', e.message || e);
            console.error('[DetectorDebug] full error:', e);
            updateDetectorBadge('error');
        }
    }

    function detectLocal() {
        if (!detectorReady || !faceDetector || !vid.videoWidth) return { faceCount: 0, detections: [] };
        try {
            const result = faceDetector.detectForVideo(vid, performance.now());
            if (dd.detectErrorCount === 0 && dd.faceDetection !== 'success') {
                dd.faceDetection = 'success';
                ddUpdate('FaceDetection', 'success');
                console.log('[DetectorDebug] detectForVideo first successful call');
            }
            const detections = result.detections || [];
            if (detections.length > 0) {
                console.log('[DetectorDebug] Faces: ' + detections.length);
            }
            return { faceCount: detections.length, detections };
        } catch (e) {
            dd.detectErrorCount++;
            if (dd.detectErrorCount === 1) {
                dd.lastDetectError = e.message || String(e);
                dd.faceDetection = 'failed';
                ddUpdate('FaceDetection', 'failed', dd.lastDetectError);
                console.error('[DetectorDebug] detectForVideo first error:', dd.lastDetectError);
                console.error('[DetectorDebug] detectForVideo full error:', e);
            } else if (dd.detectErrorCount <= 5 || dd.detectErrorCount % 50 === 0) {
                console.warn('[DetectorDebug] detectForVideo error count:', dd.detectErrorCount, e.message || e);
            }
            return { faceCount: 0, detections: [] };
        }
    }

    // ── Scan session state ──────────────────────────────────────────────────
    let scanSessionActive = false;
    let lastFaceWasPresent = false;

    // ── Clock ────────────────────────────────────────────────────────────────
    const clockEl = document.getElementById('kioskClock');
    const dateEl  = document.getElementById('kioskDate');
    function tick() {
        const now = new Date();
        const t   = now.toLocaleTimeString('en-IN', {hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true,timeZone:'Asia/Kolkata'});
        clockEl.textContent = t.toUpperCase();
        dateEl.textContent  = now.toLocaleDateString('en-IN',{weekday:'short',day:'2-digit',month:'short',year:'numeric',timeZone:'Asia/Kolkata'});
    }
    tick(); setInterval(tick, 1000);

    // ── Beep ─────────────────────────────────────────────────────────────────
    function beep(type) {
        try {
            const ctx = new (window.AudioContext||window.webkitAudioContext)();
            const o = ctx.createOscillator(), g = ctx.createGain();
            o.connect(g); g.connect(ctx.destination);
            if (type==='ok')   { o.frequency.setValueAtTime(587,ctx.currentTime); o.frequency.setValueAtTime(880,ctx.currentTime+.12); }
            else if(type==='dupe') { o.frequency.setValueAtTime(440,ctx.currentTime); o.frequency.setValueAtTime(330,ctx.currentTime+.15); }
            else { o.frequency.setValueAtTime(220,ctx.currentTime); }
            g.gain.setValueAtTime(.12,ctx.currentTime); g.gain.exponentialRampToValueAtTime(.0001,ctx.currentTime+.35);
            o.start(); o.stop(ctx.currentTime+.35);
        } catch(e){}
    }

    // ── Camera ───────────────────────────────────────────────────────────────
    async function initCam() {
        const statusTxt = document.getElementById('scanStatusLabel');
        statusTxt.textContent = 'Starting camera...';

        console.log('[Camera] initCam called');
        console.log('[Camera] location.protocol:', location.protocol);
        console.log('[Camera] window.isSecureContext:', window.isSecureContext);

        if (!window.isSecureContext) {
            statusTxt.textContent = 'HTTPS Required';
            console.error('[Camera] Not a secure context.');
            alert('Camera requires HTTPS. Protocol: ' + location.protocol + '. Please use HTTPS.');
            return;
        }

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            statusTxt.textContent = 'API Not Supported';
            console.error('[Camera] navigator.mediaDevices not available');
            alert('Your browser does not support camera access.');
            return;
        }

        let s = null;
        try {
            const idealConstraints = {
                video: {
                    facingMode: { ideal: currentFacingMode },
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            };
            console.log('[Camera] Requesting camera with ideal constraints');
            s = await navigator.mediaDevices.getUserMedia(idealConstraints);
        } catch (err) {
            console.error('[Camera] Ideal constraints failed:', err.name, err.message);
            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                statusTxt.textContent = 'Permission denied';
                alert('Camera access was denied. Please allow camera permission and reload.');
                return;
            }
            if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                statusTxt.textContent = 'No camera found';
                alert('No camera found on this device.');
                return;
            }
            if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                statusTxt.textContent = 'Camera busy';
                alert('Camera is in use by another app.');
                return;
            }
            if (err.name === 'SecurityError') {
                statusTxt.textContent = 'Security blocked';
                alert('Camera blocked by browser security policy.');
                return;
            }
            // Fallback: basic constraints
            try {
                s = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            } catch (err2) {
                console.error('[Camera] Basic fallback failed:', err2.name, err2.message);
                statusTxt.textContent = 'Cam error';
                alert('Camera error: ' + (err2.message || err2.name || 'Unknown'));
                return;
            }
        }

        vid.srcObject = s;
        vid.style.transform = currentFacingMode === 'user' ? 'scaleX(-1)' : 'scaleX(1)';
        vid.onloadedmetadata = () => {
            vid.play().catch(e => console.warn('[Camera] video.play() error:', e.message));
        };

        vid.addEventListener('playing', () => {
            const checkVideoReady = () => {
                if (vid.videoWidth === 0) {
                    requestAnimationFrame(checkVideoReady);
                    return;
                }
                resizeOverlay();
                const track = s.getVideoTracks()[0];
                const settings = track ? track.getSettings() : {};
                statusTxt.textContent = 'Camera ready \u2014 ' + vid.videoWidth + 'x' + vid.videoHeight;
                console.log('[Camera] Active track:', track ? track.label : 'none', 'resolution:', settings.width + 'x' + settings.height);
                requestAnimationFrame(drawLoop);
                startDetectLoop();
            };
            checkVideoReady();
        }, { once: true });
    }
    
    window.toggleCam = function() {
        currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
        window.restartCam();
    };

    window.restartCam = function() {
        pauseUntil = 0; faceCheckFrame = 0; faceReady = false;
        scanSessionActive = false; lastFaceWasPresent = false;
        if(vid.srcObject) vid.srcObject.getTracks().forEach(t=>t.stop());
        initCam();
    };

    // ── Overlay Canvas Sizing ─────────────────────────────────────────────────
    function resizeOverlay() {
        const rect = vid.getBoundingClientRect();
        ovl.width  = Math.round(rect.width) || 400;
        ovl.height = Math.round(rect.height) || 300;
        cap.width  = ovl.width;
        cap.height = ovl.height;
    }

    // ── Draw Loop (overlay + face detection) ──────────────────────────────────
    let lastDrawTime = 0;
    const DRAW_INTERVAL = 1000 / DRAW_FPS;
    let lastDetections = [];

    function drawLoop(timestamp) {
        if (!vid.videoWidth || !vid.videoHeight) { requestAnimationFrame(drawLoop); return; }

        // Throttle overlay redraws
        if (timestamp - lastDrawTime < DRAW_INTERVAL) {
            requestAnimationFrame(drawLoop);
            return;
        }
        lastDrawTime = timestamp;

        // Keep canvas sized to video container
        const rect = vid.getBoundingClientRect();
        if (ovl.width !== Math.round(rect.width) || ovl.height !== Math.round(rect.height)) {
            resizeOverlay();
        }

        const W = ovl.width, H = ovl.height;
        const ctx = ovl.getContext('2d');

        // ── 1. Draw dark vignette with transparent oval cutout ────────────────
        const cx = W * 0.5;
        const cy = H * 0.48;
        // Wider oval: ~0.70:1 ratio (was 0.26:0.42 = 0.62:1, now wider)
        const rx = W * 0.34;
        const ry = H * 0.40;

        ctx.clearRect(0, 0, W, H);

        // Build oval path
        ctx.save();
        ctx.beginPath();
        ctx.ellipse(cx, cy, rx, ry, 0, 0, Math.PI * 2);

        // Full dark overlay, then cut oval out
        ctx.fillStyle = 'rgba(0,0,0,0.72)';
        ctx.fillRect(0, 0, W, H);
        ctx.globalCompositeOperation = 'destination-out';
        ctx.fill();
        ctx.restore();

        // ── 2. Draw oval border ────────────────────────────────────────────────
        ctx.save();
        ctx.beginPath();
        ctx.ellipse(cx, cy, rx, ry, 0, 0, Math.PI * 2);

        // Low-memory: static dashed border. Normal: animated.
        if (isLowEnd) {
            ctx.setLineDash([12, 12]);
        } else {
            ctx.setLineDash([15, 15]);
            ctx.lineDashOffset = -(Date.now() / 20);
        }

        if (faceReady) {
            ctx.strokeStyle = '#22c55e';
            ctx.shadowColor  = 'rgba(34,197,94,0.8)';
            ctx.shadowBlur   = isLowEnd ? 6 : 18;
        } else {
            ctx.strokeStyle = '#6366f1';
            ctx.shadowColor  = 'rgba(99,102,241,0.6)';
            ctx.shadowBlur   = isLowEnd ? 4 : 12;
        }
        ctx.lineWidth = 3.5;
        ctx.stroke();
        ctx.restore();

        // ── 3. Draw face bounding box if available ─────────────────────────────
        if (lastDetections.length === 1) {
            const det = lastDetections[0];
            const bbox = det.boundingBox;
            if (bbox) {
                ctx.save();
                // Mirror the bounding box if front camera
                const bx = currentFacingMode === 'user' ? (W - bbox.originX * W / vid.videoWidth - bbox.width * W / vid.videoWidth) : bbox.originX * W / vid.videoWidth;
                const by = bbox.originY * H / vid.videoHeight;
                const bw = bbox.width * W / vid.videoWidth;
                const bh = bbox.height * H / vid.videoHeight;
                ctx.strokeStyle = faceReady ? 'rgba(34,197,94,0.9)' : 'rgba(99,102,241,0.7)';
                ctx.lineWidth = 2;
                ctx.setLineDash([]);
                // Rounded rectangle
                const br = 8;
                ctx.beginPath();
                ctx.moveTo(bx + br, by);
                ctx.lineTo(bx + bw - br, by);
                ctx.quadraticCurveTo(bx + bw, by, bx + bw, by + br);
                ctx.lineTo(bx + bw, by + bh - br);
                ctx.quadraticCurveTo(bx + bw, by + bh, bx + bw - br, by + bh);
                ctx.lineTo(bx + br, by + bh);
                ctx.quadraticCurveTo(bx, by + bh, bx, by + bh - br);
                ctx.lineTo(bx, by + br);
                ctx.quadraticCurveTo(bx, by, bx + br, by);
                ctx.closePath();
                ctx.stroke();
                ctx.restore();
            }
        }

        // ── 4. Handle pause state ──────────────────────────────────────────────
        const now = Date.now();
        if (isProcessing) {
            lbl.textContent = 'Processing...';
            lbl.className = 'kiosk-face-label scan';
        } else if (now < pauseUntil) {
            if (!lbl.getAttribute('data-custom-status')) {
                const sec = Math.ceil((pauseUntil - now) / 1000);
                lbl.textContent = 'Next scan in ' + sec + 's...';
                lbl.className = 'kiosk-face-label wait';
            }
        } else {
            lbl.removeAttribute('data-custom-status');
            hideOverlayResult();
        }

        requestAnimationFrame(drawLoop);
    }

    // ── Independent Face Detection Loop (adaptive FPS) ───────────────────────
    let detectLoopRunning = false;

    function startDetectLoop() {
        if (detectLoopRunning) return;
        detectLoopRunning = true;

        function loop() {
            if (!vid.videoWidth || isProcessing || Date.now() < pauseUntil) {
                setTimeout(loop, DETECT_INTERVAL);
                return;
            }

            const W = ovl.width, H = ovl.height;
            const cx = W * 0.5, cy = H * 0.48;
            const rx = W * 0.34, ry = H * 0.40;

            detectAndWait(W, H, cx, cy, rx, ry);
            setTimeout(loop, DETECT_INTERVAL);
        }
        loop();
    }

    function detectAndWait(W, H, cx, cy, rx, ry) {
        // ── Browser-local face detection (MediaPipe BlazeFace) ────────────────
        if (!detectorReady) return;

        const { faceCount, detections } = detectLocal();
        lastDetections = detections;

        // ── Track face presence for scan session management ────────────────
        const facePresentNow = faceCount > 0;

        // When face disappears after a scan session, allow new scans
        if (scanSessionActive && lastFaceWasPresent && !facePresentNow) {
            scanSessionActive = false;
            faceCheckFrame = 0;
            faceReady = false;
        }
        lastFaceWasPresent = facePresentNow;

        // ── No face detected ──────────────────────────────────────────────
        if (!facePresentNow) {
            faceCheckFrame = 0;
            faceReady = false;
            scanLine.style.display = 'none';
            if (!scanSessionActive && !isProcessing) {
                lbl.textContent = '\uD83D\uDC64 Position your face inside the guide';
                lbl.className = 'kiosk-face-label wait';
            }
            return;
        }

        // ── Multiple faces detected ───────────────────────────────────────
        if (faceCount > 1) {
            faceCheckFrame = 0;
            faceReady = false;
            scanLine.style.display = 'none';
            lbl.textContent = 'Only one person allowed (' + faceCount + ' detected)';
            lbl.className = 'kiosk-face-label wait';
            return;
        }

        // ── Single face detected — check if scan session is active ────────
        if (scanSessionActive) return;

        // ── Single face detected — size heuristic ─────────────────────────
        // If bounding box available, check if face is too small (far) or too large (close)
        if (detections.length === 1 && detections[0].boundingBox) {
            const bbox = detections[0].boundingBox;
            const faceAreaRatio = (bbox.width * bbox.height) / (vid.videoWidth * vid.videoHeight);
            if (faceAreaRatio < 0.01) {
                faceCheckFrame = 0;
                faceReady = false;
                lbl.textContent = '\uD83D\uDD39 Move closer';
                lbl.className = 'kiosk-face-label wait';
                return;
            }
            if (faceAreaRatio > 0.35) {
                faceCheckFrame = 0;
                faceReady = false;
                lbl.textContent = '\uD83D\uDD39 Move back';
                lbl.className = 'kiosk-face-label wait';
                return;
            }
        }

        // ── Single face detected — capture frame for server recognition ───
        faceCheckFrame++;
        if (faceCheckFrame >= 3) {
            faceReady = true;
            lbl.textContent = 'Verifying...';
            lbl.className = 'kiosk-face-label ready';
            scanLine.style.display = 'block';

            // Capture frame for server recognition
            const cc = cap.getContext('2d', { willReadFrequently: true });
            cc.save();
            if (currentFacingMode === 'user') {
                cc.translate(cap.width, 0); cc.scale(-1,1);
            }
            const vRatio = vid.videoWidth / vid.videoHeight;
            const cRatio = cap.width / cap.height;
            let sWidth = vid.videoWidth, sHeight = vid.videoHeight, sX = 0, sY = 0;
            if (vRatio > cRatio) {
                sWidth = vid.videoHeight * cRatio;
                sX = (vid.videoWidth - sWidth) / 2;
            } else {
                sHeight = vid.videoWidth / cRatio;
                sY = (vid.videoHeight - sHeight) / 2;
            }
            cc.drawImage(vid, sX, sY, sWidth, sHeight, 0, 0, cap.width, cap.height);
            cc.restore();
            const base64Img = cap.toDataURL('image/jpeg', 0.7);

            console.log('[Scan] Sending recognition request');
            console.log('[Scan] Image size:', Math.round(base64Img.length / 1024) + 'KB');
            doScan(base64Img);
        } else {
            lbl.textContent = 'Hold still... (' + faceCheckFrame + '/3)';
            lbl.className = 'kiosk-face-label wait';
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function showOverlayResult(type, title, subtitle, description = '') {
        const icons = {
            success: `<div style="width: 80px; height: 80px; margin: 0 auto 16px auto; border-radius: 50%; background: rgba(16, 185, 129, 0.1); border: 1.5px solid rgba(16, 185, 129, 0.25); display: flex; align-items: center; justify-content: center; color: #10b981; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.15);">
                        <svg style="width: 40px; height: 40px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                      </div>`,
            warn: `<div style="width: 80px; height: 80px; margin: 0 auto 16px auto; border-radius: 50%; background: rgba(245, 158, 11, 0.1); border: 1.5px solid rgba(245, 158, 11, 0.25); display: flex; align-items: center; justify-content: center; color: #f59e0b; box-shadow: 0 10px 25px rgba(245, 158, 11, 0.15);">
                    <svg style="width: 40px; height: 40px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                   </div>`,
            error: `<div style="width: 80px; height: 80px; margin: 0 auto 16px auto; border-radius: 50%; background: rgba(239, 68, 68, 0.1); border: 1.5px solid rgba(239, 68, 68, 0.25); display: flex; align-items: center; justify-content: center; color: #ef4444; box-shadow: 0 10px 25px rgba(239, 68, 68, 0.15);">
                     <svg style="width: 40px; height: 40px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>`
        };

        const subtextColors = {
            success: '#10b981',
            warn: '#f59e0b',
            error: '#ef4444'
        };

        overlayContent.innerHTML = `
            ${icons[type]}
            <h4 style="color: #ffffff; font-size: 32px; font-weight: 900; letter-spacing: 0.02em; margin: 12px 0 4px 0; font-family: system-ui, -apple-system, sans-serif;">${escapeHtml(title)}</h4>
            <p style="color: ${subtextColors[type]}; font-size: 13px; font-weight: 800; letter-spacing: 0.15em; text-transform: uppercase; margin: 0; font-family: system-ui, -apple-system, sans-serif;">${escapeHtml(subtitle)}</p>
            ${description ? `<p style="color: #cbd5e1; font-size: 12.5px; margin: 16px auto 0 auto; max-width: 320px; background: rgba(0,0,0,0.55); border-radius: 12px; padding: 10px 16px; border: 1px solid rgba(255,255,255,0.06); font-weight: 500; line-height: 1.45; font-family: system-ui, -apple-system, sans-serif;">${escapeHtml(description)}</p>` : ''}
        `;
        
        overlay.style.display = 'flex';
        overlay.offsetHeight; // Force reflow
        overlay.style.opacity = '1';
        overlay.style.pointerEvents = 'auto';
        overlayContent.style.transform = 'scale(1)';
        overlayContent.style.webkitTransform = 'scale(1)';
    }

    function hideOverlayResult() {
        overlay.style.opacity = '0';
        overlay.style.pointerEvents = 'none';
        overlayContent.style.transform = 'scale(0.9)';
        overlayContent.style.webkitTransform = 'scale(0.9)';
        setTimeout(() => {
            if (overlay.style.opacity === '0') {
                overlay.style.display = 'none';
            }
        }, 300);
    }

    // ── Scan & Submit ─────────────────────────────────────────────────────────
    async function doScan(base64Img) {
        isProcessing = true;
        scanSessionActive = true;
        scanCount++;
        document.getElementById('statScans').textContent = scanCount;
        lbl.textContent = 'Identifying...'; lbl.className = 'kiosk-face-label scan';

        console.log('[Scan] POST:', API);

        try {
            const r = await fetch(API, {
                method:'POST', headers:{'Content-Type':'application/json'},
                body: JSON.stringify({image_base64: base64Img})
            });
            console.log('[Scan] Response:', r.status);
            let data = null;
            try { data = await r.json(); } catch(e){}

            // 503 = Python backend offline
            if (!handleApiResponse(r, data)) { return; }

            if (data && data.success) {
                const conf = data.confidence ? Math.round(data.confidence * 100)+'%' : '—';
                document.getElementById('statConf').textContent = conf;

                if (data.already_checked_in) {
                    beep('dupe');
                    showResult('warn', data.employee_name||'Staff', data.message, data.confidence);
                    lbl.textContent = `Already Checked In: ${data.employee_name || 'Staff'}`;
                    lbl.className = 'kiosk-face-label wait';
                    lbl.setAttribute('data-custom-status', '1');
                    showOverlayResult('warn', data.employee_name || 'Staff', 'Already Checked In', data.message);
                    pauseUntil = Date.now() + 5000;
                } else {
                    beep('ok');
                    checkedToday++;
                    document.getElementById('statChecked').textContent = checkedToday;
                    showResult('success', data.employee_name||'Staff', 'Attendance marked successfully!', data.confidence);
                    addFeed(data.employee_name, data.employee_code, data.check_in);
                    lbl.textContent = `Marked: ${data.employee_name || 'Staff'}`;
                    lbl.className = 'kiosk-face-label ready';
                    lbl.setAttribute('data-custom-status', '1');
                    showOverlayResult('success', data.employee_name || 'Staff', 'Attendance Marked', 'Clocked in successfully!');
                    pauseUntil = Date.now() + 6000;
                }
            } else if (data && data.no_faces_registered) {
                showResult('warn', 'No Faces Registered', 'Please register at least one employee face first.', null);
                lbl.textContent = `No Faces Registered`;
                lbl.className = 'kiosk-face-label wait';
                lbl.setAttribute('data-custom-status', '1');
                showOverlayResult('warn', 'No Faces Registered', 'Register faces first', 'Face profiles must be enrolled before verification.');
                pauseUntil = Date.now() + 5000;
            } else {
                beep('error');
                showResult('error', 'Not Recognized', data && data.message ? data.message : 'Face not recognized. Please try again.', data && data.confidence ? data.confidence : null);
                lbl.textContent = `Not Recognized`;
                lbl.className = 'kiosk-face-label wait';
                lbl.setAttribute('data-custom-status', '1');
                showOverlayResult('error', 'Not Recognized', 'Please try again', data && data.message ? data.message : 'Face not recognized.');
                pauseUntil = Date.now() + 4000;
            }
        } catch(e) {
            // Network error — backend likely offline
            setBackendStatus(false);
            showResult('error', 'Backend Offline', 'InsightFace service is unreachable. Please start the Python backend.');
            lbl.textContent = `Backend Offline`;
            lbl.className = 'kiosk-face-label wait';
            lbl.setAttribute('data-custom-status', '1');
            showOverlayResult('error', 'Backend Offline', 'Service unreachable', 'Please start the Python face recognition service.');
            pauseUntil = Date.now() + 5000;
        } finally {
            isProcessing = false;
            faceCheckFrame = 0; faceReady = false;
        }
    }

    // ── Backend Status ─────────────────────────────────────────────────────────
    const badge     = document.getElementById('engineBadge');
    const badgeTxt  = document.getElementById('engineBadgeText');
    const offBanner = document.getElementById('backendOfflineBanner');
    let backendOnline = false;

    function setBackendStatus(online) {
        backendOnline = online;
        if (online) {
            badge.className = 'flex items-center gap-1.5 px-3 py-1.5 rounded-full text-2xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
            badge.querySelector('span').className = 'w-2 h-2 rounded-full bg-emerald-400 k-pulse';
            badgeTxt.textContent = 'Server Ready';
            offBanner.style.display = 'none';
            offBanner.classList.remove('show');
            document.getElementById('scanStatusLabel').textContent = 'Scanning...';
        } else {
            badge.className = 'flex items-center gap-1.5 px-3 py-1.5 rounded-full text-2xs font-bold bg-red-500/10 text-red-400 border border-red-500/20';
            badge.querySelector('span').className = 'w-2 h-2 rounded-full bg-red-400';
            badgeTxt.textContent = 'Server Offline';
            offBanner.style.display = 'block';
            offBanner.classList.add('show');
            document.getElementById('scanStatusLabel').textContent = 'Backend offline!';
        }
    }

    window.checkBackend = async function() {
        try {
            const r = await fetch('<?= url('attendance/api.php?endpoint=/health') ?>', { method: 'GET' });
            if (r.ok || r.status === 200) {
                const d = await r.json().catch(() => null);
                const alive = d && (d.status === 'healthy' || d.model_loaded === true);
                setBackendStatus(alive);
            } else {
                setBackendStatus(false);
            }
        } catch(e) {
            setBackendStatus(false);
        }
    };

    // Also catch 503 inside doScan response
    function handleApiResponse(r, data) {
        if (r.status === 503) {
            setBackendStatus(false);
            showResult('error', 'Backend Offline', data?.fix || 'Start the Python InsightFace service.');
            pauseUntil = Date.now() + 6000;
            return false;
        }
        setBackendStatus(true);
        return true;
    }

    // ── UI ────────────────────────────────────────────────────────────────────
    function showResult(type, name, msg, conf) {
        const clr = {success:'border-emerald-500/40 bg-emerald-500/10',warn:'border-amber-500/40 bg-amber-500/10',error:'border-red-500/40 bg-red-500/10'};
        const ico = {
            success:`<svg class="w-10 h-10 text-emerald-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
            warn:`<svg class="w-10 h-10 text-amber-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`,
            error:`<svg class="w-10 h-10 text-red-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
        };
        const txtClr = {success:'text-emerald-300',warn:'text-amber-300',error:'text-red-300'};
        const confBadge = conf ? `<div class="mt-1.5"><span class="text-2xs px-2 py-0.5 rounded-full bg-slate-700 text-slate-300">Confidence: ${Math.round(conf*100)}%</span></div>` : '';
        resultEl.className = `k-result border ${clr[type]||clr.error} text-center`;
        resultEl.innerHTML = `${ico[type]||ico.error}<div class="font-bold ${txtClr[type]||'text-red-300'} text-sm">${name}</div><div class="text-xs text-slate-400 mt-1">${msg}</div>${confBadge}`;
    }

    function addFeed(name, code, time) {
        if (feedEl.querySelector('.text-slate-500')) feedEl.innerHTML = '';
        const opts = {hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true,timeZone:'Asia/Kolkata'};
        const t = time ? new Date(time.replace(' ','T')).toLocaleTimeString('en-IN',opts).toUpperCase() : new Date().toLocaleTimeString('en-IN',opts).toUpperCase();
        const el = document.createElement('div');
        el.className = 'k-feed-item';
        el.innerHTML = `<div class="flex items-center justify-between"><div><span class="text-xs font-bold text-emerald-300">${name||'Staff'}</span>${code?`<span class="ml-2 text-2xs text-slate-500 font-mono">${code}</span>`:''}</div><span class="text-2xs text-slate-400 font-mono">${t}</span></div>`;
        feedEl.prepend(el);
        if (feedEl.children.length > 6) feedEl.removeChild(feedEl.lastChild);
    }

    // ── Initialization ───────────────────────────────────────────────────────
    document.getElementById('ddRetryBtn').addEventListener('click', function() { window.ddRetryDetector(); });
    document.getElementById('ddCopyBtn').addEventListener('click', function() { window.ddCopyDiagnostics(); });

    checkBackend();
    setInterval(checkBackend, 30000);
    loadFaceDetector();
    initCam();
})();
</script>


