<?php
$layout    = 'app';
$pageTitle = 'Face Attendance Kiosk';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Attendance'], ['label' => 'Face Kiosk']];
ob_start();
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
    backdrop-filter: blur(6px);
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
    animation: offlinePulse 2.5s ease-in-out infinite;
}
@keyframes offlinePulse { 0%,100%{border-color:rgba(239,68,68,0.35)} 50%{border-color:rgba(239,68,68,0.75)} }
</style>

<div class="space-y-5" id="kioskRoot">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl border border-slate-800/60 bg-slate-900/50 backdrop-blur">
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
        <div class="flex items-center gap-3">
            <div class="digital-clock text-2xl" id="kioskClock">--:-- --</div>
            <span id="engineBadge" class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-2xs font-bold bg-slate-700 text-slate-400 border border-slate-600">
                <span class="w-2 h-2 rounded-full bg-slate-500"></span><span id="engineBadgeText">Checking...</span>
            </span>
            <a href="<?= url('attendance/face-register') ?>" class="px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition shadow-sm">
                + Register Face
            </a>
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
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        <!-- Camera Column -->
        <div class="lg:col-span-7 space-y-4">
            <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-5 shadow-sm">
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
                <div class="kiosk-wrap">
                    <video id="kioskVideo" autoplay playsinline muted></video>
                    <canvas id="kioskOverlayCanvas"></canvas>
                    <div class="kiosk-scan-line" id="scanLine" style="display:none;"></div>
                    <div class="kiosk-face-label wait" id="faceLbl">👤 Align face in oval</div>
                    <canvas id="kioskCapCanvas" style="display:none;"></canvas>
                </div>

                <p class="text-2xs text-slate-500 text-center mt-3">
                    <svg class="w-3.5 h-3.5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Only scans when your face fills the oval. Background outside oval is darkened.
                </p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-3">
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
        <div class="lg:col-span-5 space-y-4">

            <!-- Status Panel -->
            <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-5 shadow-sm">
                <h3 class="text-sm font-bold text-white mb-3">Verification Status</h3>
                <div id="kioskResult" class="k-result border border-slate-700 bg-slate-800/60 text-center">
                    <div class="py-4">
                        <svg class="w-10 h-10 mx-auto text-slate-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-xs text-slate-500 font-medium">Waiting for face...</p>
                    </div>
                </div>
            </div>

            <!-- Recent Check-Ins -->
            <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-white">Recent Check-Ins</h3>
                    <span class="text-2xs text-slate-500 font-mono" id="kioskDate"></span>
                </div>
                <div id="kioskFeed"><div class="text-xs text-slate-500 text-center py-4">No check-ins yet today.</div></div>
            </div>

        </div>
    </div>
</div>

<script>
(function() {
    const API  = '/psnf/public/attendance/api.php?endpoint=/api/verify-face';
    const vid  = document.getElementById('kioskVideo');
    const cap  = document.getElementById('kioskCapCanvas');   // capture
    const ovl  = document.getElementById('kioskOverlayCanvas'); // dark mask overlay
    const lbl  = document.getElementById('faceLbl');
    const scanLine = document.getElementById('scanLine');
    const resultEl = document.getElementById('kioskResult');
    const feedEl   = document.getElementById('kioskFeed');

    let isProcessing  = false;
    let pauseUntil    = 0;
    let lastScan      = 0;
    let scanCount     = 0;
    let checkedToday  = 0;
    let faceReady     = false;
    let faceCheckFrame = 0;
    let currentFacingMode = 'user';

    const SCAN_INTERVAL = 800;  // ms between scans
    const FACE_HOLD_FRAMES = 8; // consecutive "face detected" frames before scan fires

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
        statusTxt.textContent = 'Starting camera...';
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            statusTxt.textContent = 'HTTPS Required';
            alert("Camera access is blocked by the browser. When accessing via a local network IP (like 192.168.x.x) on mobile, browsers require a secure HTTPS connection to use the camera. Please use HTTPS, or test on localhost.");
            return;
        }
        try {
            let s;
            try {
                s = await navigator.mediaDevices.getUserMedia({ video: { facingMode: currentFacingMode } });
            } catch (err1) {
                s = await navigator.mediaDevices.getUserMedia({ video: true });
            }
            vid.srcObject = s;
            vid.style.transform = currentFacingMode === 'user' ? 'scaleX(-1)' : 'scaleX(1)';
            vid.onloadedmetadata = () => {
                vid.play().catch(e => alert("Video Play Error: " + e.message));
            };
            
            vid.addEventListener('playing', () => {
                const checkVideoReady = () => {
                    if (vid.videoWidth === 0) {
                        requestAnimationFrame(checkVideoReady);
                        return;
                    }
                    resizeOverlay();
                    const track = s.getVideoTracks()[0];
                    statusTxt.textContent = `W:${vid.videoWidth} H:${vid.videoHeight} - ` + (track ? track.label : 'Scanning...');
                    requestAnimationFrame(drawLoop);
                };
                checkVideoReady();
            });
        } catch(e) {
            statusTxt.textContent = 'Cam error';
            alert("Camera Error: " + e.message);
        }
    }
    
    window.toggleCam = function() {
        currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
        window.restartCam();
    };

    window.restartCam = function() {
        pauseUntil = 0; faceCheckFrame = 0; faceReady = false;
        if(vid.srcObject) vid.srcObject.getTracks().forEach(t=>t.stop());
        initCam();
    };

    // ── Overlay Canvas Sizing ─────────────────────────────────────────────────
    function resizeOverlay() {
        ovl.width  = vid.videoWidth  || vid.clientWidth;
        ovl.height = vid.videoHeight || vid.clientHeight;
        cap.width  = ovl.width;
        cap.height = ovl.height;
    }

    // ── Draw Loop (overlay + face detection) ──────────────────────────────────
    function drawLoop() {
        if (!vid.videoWidth) { requestAnimationFrame(drawLoop); return; }

        // Keep canvas sized to video
        if (ovl.width !== vid.videoWidth) resizeOverlay();

        const W = ovl.width, H = ovl.height;
        const ctx = ovl.getContext('2d');

        // ── 1. Draw dark vignette with transparent oval cutout ────────────────
        const cx = W * 0.5;
        const cy = H * 0.48;
        const rx = W * 0.26;   // oval half-width  (26% of frame width)
        const ry = H * 0.42;   // oval half-height (42% of frame height)

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
        if (faceReady) {
            ctx.strokeStyle = '#22c55e';
            ctx.shadowColor  = 'rgba(34,197,94,0.8)';
            ctx.shadowBlur   = 18;
        } else {
            ctx.strokeStyle = '#6366f1';
            ctx.shadowColor  = 'rgba(99,102,241,0.6)';
            ctx.shadowBlur   = 12;
        }
        ctx.lineWidth = 3.5;
        ctx.stroke();
        ctx.restore();

        // ── 3. Face-fit detection ──────────────────────────────────────────────
        detectFaceInOval(ctx, W, H, cx, cy, rx, ry);

        requestAnimationFrame(drawLoop);

        // ── 4. Trigger scan if face is in oval ────────────────────────────────
        const now = Date.now();
        if (faceReady && !isProcessing && now > pauseUntil && (now - lastScan >= SCAN_INTERVAL)) {
            lastScan = now;
            doScan(cx, cy, rx, ry);
        }
    }

    // ── Face-fit detection using pixel variance inside oval ────────────────────
    function detectFaceInOval(ovlCtx, W, H, cx, cy, rx, ry) {
        // Capture current video frame
        const cc = cap.getContext('2d');
        cc.save();
        cc.translate(cap.width, 0); cc.scale(-1, 1);
        cc.drawImage(vid, 0, 0, cap.width, cap.height);
        cc.restore();

        const pixels = cc.getImageData(0, 0, W, H).data;

        // Sample pixels inside the oval region
        let bright = 0, dark = 0, totalSamples = 0;
        const step = 8;
        for (let y = Math.floor(cy - ry); y < cy + ry; y += step) {
            for (let x = Math.floor(cx - rx); x < cx + rx; x += step) {
                // Check if (x,y) is inside the ellipse
                const dx = (x - cx) / rx, dy = (y - cy) / ry;
                if (dx*dx + dy*dy > 1) continue;

                const idx = (Math.round(y) * W + Math.round(x)) * 4;
                const lum = 0.299 * pixels[idx] + 0.587 * pixels[idx+1] + 0.114 * pixels[idx+2];
                if (lum > 60) bright++;
                else dark++;
                totalSamples++;
            }
        }

        // Also sample OUTSIDE oval (border zone)
        let outerBright = 0, outerSamples = 0;
        for (let y = Math.floor(cy - ry*1.15); y < cy + ry*1.15; y += step*2) {
            for (let x = Math.floor(cx - rx*1.15); x < cx + rx*1.15; x += step*2) {
                const dx = (x - cx) / rx, dy = (y - cy) / ry;
                const dist = dx*dx + dy*dy;
                if (dist < 1.0 || dist > 1.3) continue; // only border ring

                const ix = Math.round(x), iy = Math.round(y);
                if (ix < 0 || iy < 0 || ix >= W || iy >= H) continue;
                const idx = (iy * W + ix) * 4;
                const lum = 0.299 * pixels[idx] + 0.587 * pixels[idx+1] + 0.114 * pixels[idx+2];
                if (lum > 50) outerBright++;
                outerSamples++;
            }
        }

        // Heuristics:
        // face fills oval if >55% of oval pixels are reasonably bright (not black bg)
        const fillRatio = totalSamples > 0 ? bright / totalSamples : 0;
        // face contrast vs outer border — face areas usually brighter than pure black bg
        const faceContrast = outerSamples > 0 ? (outerBright / outerSamples) : 1;

        const faceInOval = fillRatio > 0.50 && totalSamples > 100;

        if (faceInOval) {
            faceCheckFrame++;
            if (faceCheckFrame >= FACE_HOLD_FRAMES) {
                faceReady = true;
                lbl.textContent = '✅ Face detected — scanning...';
                lbl.className = 'kiosk-face-label ready';
                scanLine.style.display = 'block';
            } else {
                lbl.textContent = `⬆ Hold still... (${faceCheckFrame}/${FACE_HOLD_FRAMES})`;
                lbl.className = 'kiosk-face-label wait';
            }
        } else {
            faceCheckFrame = Math.max(0, faceCheckFrame - 1);
            if (faceCheckFrame === 0) {
                faceReady = false;
                scanLine.style.display = 'none';
                const pct = Math.round(fillRatio * 100);
                if (pct > 30) {
                    lbl.textContent = `🔄 Move closer — fill the oval (${pct}%)`;
                } else {
                    lbl.textContent = '👤 Align face inside oval';
                }
                lbl.className = 'kiosk-face-label wait';
            }
        }
    }

    // ── Scan & Submit ─────────────────────────────────────────────────────────
    async function doScan() {
        isProcessing = true;
        scanCount++;
        document.getElementById('statScans').textContent = scanCount;
        lbl.textContent = '⚡ Scanning...'; lbl.className = 'kiosk-face-label scan';

        const img = cap.toDataURL('image/jpeg', 0.88);
        try {
            const r = await fetch(API, {
                method:'POST', headers:{'Content-Type':'application/json'},
                body: JSON.stringify({image_base64: img})
            });
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
                    pauseUntil = Date.now() + 5000;
                } else {
                    beep('ok');
                    checkedToday++;
                    document.getElementById('statChecked').textContent = checkedToday;
                    showResult('success', data.employee_name||'Staff', '✔ Attendance marked successfully!', data.confidence);
                    addFeed(data.employee_name, data.employee_code, data.check_in);
                    pauseUntil = Date.now() + 6000;
                }
            } else if (data && data.no_faces_registered) {
                showResult('warn', 'No Faces Registered', 'Please register at least one employee face first.', null);
                pauseUntil = Date.now() + 5000;
            } else {
                pauseUntil = Date.now() + 1500;
            }
        } catch(e) {
            // Network error — backend likely offline
            setBackendStatus(false);
            showResult('error', 'Backend Offline', 'InsightFace service is unreachable. Please start the Python backend.');
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
            badgeTxt.textContent = 'InsightFace LIVE';
            offBanner.style.display = 'none';
            document.getElementById('scanStatusLabel').textContent = 'Scanning...';
        } else {
            badge.className = 'flex items-center gap-1.5 px-3 py-1.5 rounded-full text-2xs font-bold bg-red-500/10 text-red-400 border border-red-500/20';
            badge.querySelector('span').className = 'w-2 h-2 rounded-full bg-red-400';
            badgeTxt.textContent = 'Backend Offline';
            offBanner.style.display = 'block';
            document.getElementById('scanStatusLabel').textContent = 'Backend offline!';
        }
    }

    window.checkBackend = async function() {
        try {
            const r = await fetch('/psnf/public/attendance/api.php?endpoint=/health', { method: 'GET' });
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

    // Check backend health on load, then start camera
    checkBackend();
    setInterval(checkBackend, 30000); // poll every 30s
    initCam();
})();
</script>

<?php
$content = ob_get_clean();
?>
