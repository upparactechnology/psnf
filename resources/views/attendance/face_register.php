<?php
$layout    = 'app';
$pageTitle = 'Face Registration';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Attendance'], ['label' => 'Face Registration']];
ob_start();

$isLoggedIn = !empty($_SESSION['face_reg_authenticated']) || !empty($_SESSION['user']);
$adminName  = $_SESSION['face_reg_user'] ?? ($_SESSION['user']['name'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_face_reg_action'])) {
    header('Content-Type: application/json');
    $action = $_POST['_face_reg_action'];
    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $pass  = trim($_POST['password'] ?? '');
        try {
            $pdo = new PDO("mysql:host=127.0.0.1;dbname=psnf_drm;charset=utf8mb4","root","",[
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            $s = $pdo->prepare("SELECT * FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1");
            $s->execute([$email]);
            $u = $s->fetch();
            if ($u && password_verify($pass, $u['password'])) {
                $_SESSION['face_reg_authenticated'] = true;
                $_SESSION['face_reg_user'] = $u['name'] ?? 'Admin';
                echo json_encode(['success' => true, 'name' => $u['name']]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
            }
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Auth error: ' . $e->getMessage()]);
        }
        exit();
    }
    if ($action === 'logout') {
        unset($_SESSION['face_reg_authenticated'], $_SESSION['face_reg_user']);
        echo json_encode(['success' => true]);
        exit();
    }
}
?>

<style>
/* ─── Camera wrapper ────────────────────────────────────────── */
.freg-wrap {
    position: relative;
    width: 100%; max-width: 440px;
    margin: 0 auto;
    border-radius: 18px;
    overflow: hidden;
    background: transparent;
    border: 3px solid #6366f1;
    box-shadow: 0 0 30px rgba(99,102,241,0.22);
    aspect-ratio: 4/3;
}
#fregVideo { width: 100%; height: 100%; object-fit: cover; display: block; transform: scaleX(-1); }
#fregOverlay { position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; z-index: 3; }

/* Countdown badge inside video */
.freg-cd {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    z-index: 10;
    font-size: 3.5rem; font-weight: 900;
    color: #f59e0b;
    text-shadow: 0 0 24px rgba(245,158,11,0.9), 0 2px 8px #000;
    pointer-events: none; display: none;
    font-family: 'Courier New', monospace;
}
.freg-status-bar {
    position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%);
    z-index: 6; font-size: 11px; font-weight: 700; padding: 5px 18px;
    border-radius: 99px; white-space: nowrap; pointer-events: none;
    backdrop-filter: blur(6px);
    transition: all 0.3s;
}
.freg-status-bar.idle    { background: rgba(71,85,105,.55); border:1.5px solid #475569; color:#94a3b8; }
.freg-status-bar.wait    { background: rgba(245,158,11,.2); border:1.5px solid #f59e0b; color:#fbbf24; }
.freg-status-bar.good    { background: rgba(34,197,94,.2);  border:1.5px solid #22c55e; color:#4ade80; }
.freg-status-bar.scan    { background: rgba(99,102,241,.3); border:1.5px solid #818cf8; color:#c7d2fe; }
.freg-status-bar.done    { background: rgba(34,197,94,.3);  border:1.5px solid #22c55e; color:#4ade80; }

/* Scan progress line */
.freg-scan-line {
    position: absolute; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, transparent, #22c55e, #a5b4fc, #22c55e, transparent);
    box-shadow: 0 0 14px #22c55e;
    animation: fregScan 1.6s ease-in-out infinite;
    z-index: 4; pointer-events: none; display: none;
}
@keyframes fregScan { 0%{top:0%} 50%{top:95%} 100%{top:0%} }

/* ─── Angle indicator dots (Face-ID style) ──────────────────── */
.angle-dots {
    display: flex; gap: 8px; align-items: center; justify-content: center;
    flex-wrap: wrap;
}
.angle-dot {
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    cursor: default;
}
.angle-dot-ring {
    width: 40px; height: 40px; border-radius: 50%;
    border: 2px solid #334155;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    background: #0f172a;
    transition: all 0.4s ease;
    position: relative; overflow: hidden;
}
.angle-dot-ring.active   { border-color: #6366f1; box-shadow: 0 0 14px rgba(99,102,241,.6); animation: adbounce .6s ease; }
.angle-dot-ring.captured { border-color: #22c55e; box-shadow: 0 0 10px rgba(34,197,94,.4); background: rgba(34,197,94,.1); }
.angle-dot-ring.captured::after { content: '✔'; position: absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:14px; color:#22c55e; font-weight:900; }
.angle-dot-ring.captured .angle-icon { display:none; }
.angle-dot-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
.angle-dot.active   .angle-dot-label { color: #a5b4fc; }
.angle-dot.captured .angle-dot-label { color: #4ade80; }
@keyframes adbounce { 0%,100%{transform:scale(1)} 40%{transform:scale(1.15)} }

/* ─── Auth overlay ──────────────────────────────────────────── */
.auth-overlay {
    position: fixed; inset: 0;
    background: rgba(2,6,23,0.92);
    z-index: 9999;
    display: flex; align-items: center; justify-content: center;
    backdrop-filter: blur(8px);
}
.auth-card {
    width: 100%; max-width: 420px;
    border-radius: 24px;
    background: #0f172a;
    border: 1px solid #334155;
    box-shadow: 0 25px 60px rgba(0,0,0,0.7);
    padding: 2rem;
}
.reg-emp-photo {
    width: 40px; height: 40px; border-radius: 50%;
    object-fit: cover; border: 2px solid #6366f1; background: #0f172a;
}
</style>

<?php if (!$isLoggedIn): ?>
<div id="authOverlay" class="auth-overlay">
    <div class="auth-card">
        <div class="text-center mb-5">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mx-auto mb-3 shadow-lg">
                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h2 class="text-lg font-bold text-white">Admin Authentication</h2>
            <p class="text-xs text-slate-400 mt-1">Enter your admin credentials to unlock face registration</p>
        </div>
        <div id="authErr" class="hidden rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs p-3 mb-4"></div>
        <form id="authForm" class="space-y-4">
            <div>
                <label class="block text-2xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Email</label>
                <input type="email" id="authEmail" required placeholder="admin@psnf.edu"
                    class="w-full bg-slate-800 border border-slate-700 text-white rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:border-indigo-500 transition">
            </div>
            <div>
                <label class="block text-2xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Password</label>
                <input type="password" id="authPass" required placeholder="••••••••"
                    class="w-full bg-slate-800 border border-slate-700 text-white rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:border-indigo-500 transition">
            </div>
            <button type="submit" id="authBtn" class="w-full py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-bold hover:from-indigo-500 hover:to-purple-500 transition shadow-lg">
                Login & Unlock Registration
            </button>
        </form>
        <div class="text-center mt-4">
            <a href="<?= url('attendance/face-kiosk') ?>" class="text-xs text-slate-500 hover:text-slate-300 transition">← Back to Attendance Kiosk</a>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="space-y-5" id="mainContent" <?= (!$isLoggedIn ? 'style="filter:blur(4px);pointer-events:none;"' : '') ?>>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl border border-slate-800/60 bg-slate-900/50 backdrop-blur">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-white">Face Registration</h1>
                <p class="text-xs text-slate-400 mt-0.5">5-angle scan (like Face ID) for maximum accuracy</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <?php if ($isLoggedIn): ?>
                <span class="text-xs text-slate-400">Logged in as <b class="text-indigo-400"><?= htmlspecialchars($adminName) ?></b></span>
                <button onclick="doLogout()" class="px-3 py-1.5 rounded-xl text-xs font-bold text-red-400 border border-red-500/20 hover:bg-red-500/10 transition">Lock</button>
            <?php endif; ?>
            <a href="<?= url('attendance/face-kiosk') ?>" class="px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition shadow-sm">← Kiosk</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        <!-- Left: Form + Camera + Angle Dots -->
        <div class="lg:col-span-6 space-y-4">

            <!-- Step 1: Select ERP User -->
            <div id="step1Card" class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <span class="w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center">1</span>
                    <h3 class="text-sm font-bold text-white">Select Staff / Teacher</h3>
                    <a href="<?= url('staff/users') ?>" target="_blank" class="ml-auto text-2xs text-indigo-400 hover:text-indigo-300 transition">+ Add User →</a>
                </div>

                <!-- User search + select -->
                <div class="mb-4">
                    <label class="block text-2xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Search User</label>
                    <input type="text" id="userSearch" placeholder="Type name or employee ID..."
                        oninput="filterUsers()"
                        class="w-full bg-slate-800 border border-slate-700 text-white rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:border-indigo-500 transition">
                </div>

                <div id="userList" class="space-y-2 max-h-64 overflow-y-auto mb-4 pr-1">
                    <div class="text-xs text-slate-500 text-center py-6">Loading users...</div>
                </div>

                <!-- Selected user preview -->
                <div id="selectedUserPreview" class="hidden rounded-xl border border-indigo-500/20 bg-indigo-500/8 p-3 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-indigo-600/40 flex items-center justify-center text-indigo-300 font-bold text-sm" id="selUserInitials">?</div>
                        <div>
                            <div class="text-xs font-bold text-white" id="selUserName">—</div>
                            <div class="text-2xs text-slate-400" id="selUserMeta">—</div>
                        </div>
                        <button onclick="clearSelectedUser()" class="ml-auto text-2xs text-red-400 hover:text-red-300 px-2 py-1 rounded-lg border border-red-500/20 hover:bg-red-500/10 transition">Clear</button>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-2xs text-slate-400 font-mono" id="scanStatusLabel">Initializing...</span>
                    <button onclick="toggleCam()" class="p-1.5 rounded-lg hover:bg-slate-800 transition text-slate-400 hover:text-white" title="Flip Camera">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                    </button>
                    <button onclick="restartCam()" class="p-1.5 rounded-lg hover:bg-slate-800 transition text-slate-400 hover:text-white" title="Restart">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                </div>
                <button id="startScanBtn" onclick="beginScan()" disabled
                    class="w-full py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-bold hover:from-indigo-500 hover:to-purple-500 transition shadow-lg flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Start Face Scan
                </button>
            </div>

            <!-- Step 2: Camera Capture -->
            <div id="step2Card" class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-5 shadow-sm hidden">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-amber-500 text-white text-xs font-bold flex items-center justify-center">2</span>
                        <h3 class="text-sm font-bold text-white">Multi-Angle Scan</h3>
                    </div>
                    <span id="angleProgressBadge" class="text-2xs font-bold px-2 py-1 rounded-full bg-slate-700 text-slate-300">0 / 5</span>
                </div>

                <!-- Angle Indicator — Face ID style -->
                <div class="angle-dots mb-4" id="angleDots">
                    <!-- Rendered by JS -->
                </div>

                <!-- Camera + overlay -->
                <div class="freg-wrap">
                    <video id="fregVideo" autoplay playsinline muted></video>
                    <canvas id="fregOverlay"></canvas>
                    <div class="freg-scan-line" id="fregScanLine"></div>
                    <div class="freg-cd" id="fregCd"></div>
                    <div class="freg-status-bar idle" id="fregStatus">Get ready...</div>
                    <canvas id="fregCapCanvas" style="display:none;"></canvas>
                </div>

                <!-- Current angle instruction -->
                <div class="mt-3 rounded-xl border border-indigo-500/20 bg-indigo-500/8 p-3 text-center">
                    <div class="text-xs font-bold text-indigo-300" id="angleInstruction">Loading...</div>
                    <div class="text-2xs text-slate-500 mt-0.5" id="angleHint">Align face in oval</div>
                </div>

                <!-- Alert -->
                <div id="fregAlert" class="mt-3 rounded-xl text-xs p-3 text-center hidden"></div>

                <!-- Done actions -->
                <div id="fregDoneActions" class="mt-3 space-y-2 hidden">
                    <div class="rounded-xl bg-emerald-500/10 border border-emerald-500/20 p-3 text-center text-emerald-300 text-xs font-bold" id="fregSuccessMsg"></div>
                    <button onclick="resetReg()" class="w-full py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-500 transition">
                        + Register Another Employee
                    </button>
                </div>

                <button onclick="resetReg()" class="w-full mt-2 py-2 rounded-xl border border-slate-700 text-slate-400 text-xs hover:bg-slate-800 transition">
                    ← Start Over
                </button>
            </div>
        </div>

        <!-- Right: Registered Employees -->
        <div class="lg:col-span-6">
            <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-5 shadow-sm h-full">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-white">Registered Employees</h3>
                    <div class="flex items-center gap-2">
                        <input type="text" id="empSearch" placeholder="Search..." oninput="filterEmps()"
                            class="bg-slate-800 border border-slate-700 text-white rounded-lg py-1.5 px-2.5 text-xs w-28 focus:outline-none focus:border-indigo-500">
                        <span id="empCountBadge" class="text-2xs font-bold px-2 py-1 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">0</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="text-2xs text-slate-500 uppercase tracking-wider border-b border-slate-800">
                            <tr>
                                <th class="pb-2 pr-2">#</th>
                                <th class="pb-2 pr-2">Photo</th>
                                <th class="pb-2 pr-2">ID</th>
                                <th class="pb-2 pr-2">Name</th>
                                <th class="pb-2 pr-2">Angles</th>
                                <th class="pb-2 text-right">Del</th>
                            </tr>
                        </thead>
                        <tbody id="empListBody" class="divide-y divide-slate-800">
                            <tr><td colspan="6" class="py-4 text-center text-slate-500 text-xs">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {

// ─── Angle definitions (Face-ID style 5 positions) ─────────────────────────────
const ANGLES = [
    { id:'center', label:'Front',  icon:'👤', hint:'Look straight at the camera',         cdCount:3 },
    { id:'left',  label:'Left',   icon:'👈', hint:'Turn your head to YOUR left',          cdCount:2 },
    { id:'right', label:'Right',  icon:'👉', hint:'Turn your head to YOUR right',         cdCount:2 },
    { id:'up',    label:'Up',     icon:'⬆️',  hint:'Tilt your chin slightly upward',       cdCount:2 },
    { id:'down',  label:'Down',   icon:'⬇️',  hint:'Tilt your chin slightly downward',     cdCount:2 },
];

const API_REG  = '/psnf/public/attendance/api.php?endpoint=/api/register-face';
const API_LIST = '/psnf/public/attendance/api.php?endpoint=/api/employees/list';  // registered faces list
const API_DEL  = '/psnf/public/attendance/api.php?endpoint=/api/employees/delete';

const vid    = document.getElementById('fregVideo');
const ovl    = document.getElementById('fregOverlay');
const capCvs = document.getElementById('fregCapCanvas');
const cdEl   = document.getElementById('fregCd');
const scanLine = document.getElementById('fregScanLine');
const statusEl = document.getElementById('fregStatus');
const instrEl  = document.getElementById('angleInstruction');
const hintEl   = document.getElementById('angleHint');
const progBadge = document.getElementById('angleProgressBadge');

let camStream    = null;
let camReady     = false;
let curAngle     = 0;
let capturedImgs = [];     // array of base64 strings (one per angle)
let faceFrames   = 0;      // consecutive frames with face in oval
let cdTimer      = null;
let countRemain  = 0;
let phase        = 'idle'; // idle | countdown | capturing | paused | done
let drawLoopRunning = false;
let submitting   = false;
let currentFacingMode = 'user';

// ── Auth ────────────────────────────────────────────────────────────────────────
const authForm = document.getElementById('authForm');
if (authForm) {
    authForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('authBtn');
        const errEl = document.getElementById('authErr');
        btn.disabled = true; btn.textContent = 'Authenticating...'; errEl.classList.add('hidden');
        const fd = new FormData();
        fd.append('_face_reg_action','login');
        fd.append('email', document.getElementById('authEmail').value);
        fd.append('password', document.getElementById('authPass').value);
        try {
            const r = await fetch(window.location.href, {method:'POST',body:fd});
            const d = await r.json();
            if (d.success) {
                document.getElementById('authOverlay')?.remove();
                document.getElementById('mainContent').removeAttribute('style');
                loadEmps();
                initCam();
            } else { errEl.textContent = d.message||'Invalid.'; errEl.classList.remove('hidden'); }
        } catch(err) { errEl.textContent = 'Error: '+err.message; errEl.classList.remove('hidden'); }
        finally { btn.disabled = false; btn.textContent = 'Login & Unlock Registration'; }
    });
}
window.doLogout = async function() {
    if (!confirm('Lock registration?')) return;
    const fd = new FormData(); fd.append('_face_reg_action','logout');
    await fetch(window.location.href, {method:'POST',body:fd});
    window.location.reload();
};

// ── Build angle dots ─────────────────────────────────────────────────────────────
function buildAngleDots() {
    const container = document.getElementById('angleDots');
    container.innerHTML = '';
    ANGLES.forEach((a, i) => {
        const div = document.createElement('div');
        div.className = 'angle-dot' + (i === curAngle ? ' active' : '') + (capturedImgs[i] ? ' captured' : '');
        div.id = 'adot_' + i;
        div.innerHTML = `
            <div class="angle-dot-ring ${i === curAngle ? 'active' : ''} ${capturedImgs[i] ? 'captured' : ''}">
                <span class="angle-icon">${a.icon}</span>
            </div>
            <div class="angle-dot-label">${a.label}</div>
        `;
        container.appendChild(div);
    });
}

function updateAngleDots() {
    ANGLES.forEach((a, i) => {
        const dot = document.getElementById('adot_' + i);
        if (!dot) return;
        dot.className = 'angle-dot' + (i === curAngle ? ' active' : '') + (capturedImgs[i] ? ' captured' : '');
        const ring = dot.querySelector('.angle-dot-ring');
        ring.className = 'angle-dot-ring' + (i === curAngle ? ' active' : '') + (capturedImgs[i] ? ' captured' : '');
    });
    progBadge.textContent = capturedImgs.filter(Boolean).length + ' / ' + ANGLES.length;
}

function updateAngleUI(idx) {
    const a = ANGLES[idx];
    instrEl.textContent = a.icon + ' ' + a.label + ' — angle ' + (idx+1) + ' of ' + ANGLES.length;
    hintEl.textContent  = a.hint;
}

// ── User picker (loads from ERP users table via Python API) ──────────────────
let allUsers   = [];
let selUserId  = null;

async function loadUsers() {
    const ul = document.getElementById('userList');
    try {
        const r = await fetch('/psnf/public/attendance/api.php?endpoint=/api/users/list');
        const d = await r.json();
        allUsers = (d && d.data) ? d.data : [];
        renderUsers(allUsers);
    } catch(e) {
        ul.innerHTML = `<div class="text-xs text-red-400 text-center py-3">Could not load users: ${e.message}</div>`;
    }
}

window.filterUsers = function() {
    const q = (document.getElementById('userSearch').value || '').toLowerCase();
    renderUsers(q ? allUsers.filter(u => u.name.toLowerCase().includes(q) || (u.employee_id||'').toLowerCase().includes(q)) : allUsers);
};

function renderUsers(list) {
    const ul = document.getElementById('userList');
    if (!list.length) { ul.innerHTML = '<div class="text-xs text-slate-500 text-center py-4">No users found.</div>'; return; }
    ul.innerHTML = '';
    list.forEach(u => {
        const emb = u.embedding_count || 0;
        const row = document.createElement('div');
        row.className = 'flex items-center gap-3 p-2.5 rounded-xl cursor-pointer hover:bg-slate-800 transition border border-transparent hover:border-slate-700';
        row.innerHTML = `
            <div class="w-8 h-8 rounded-full bg-indigo-600/30 flex items-center justify-center text-indigo-300 text-xs font-bold">${u.name[0]}</div>
            <div class="flex-1 min-w-0">
                <div class="text-xs font-bold text-white truncate">${u.name}</div>
                <div class="text-2xs text-slate-500">${u.employee_id||u.email||''}${u.designation?' · '+u.designation:''}</div>
            </div>
            ${emb ? `<span class="shrink-0 text-2xs text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded">${emb}v ✔</span>` : '<span class="shrink-0 text-2xs text-slate-600">No face</span>'}`;
        row.onclick = () => selectUser(u);
        ul.appendChild(row);
    });
}

function selectUser(u) {
    selUserId = u.id;
    document.getElementById('selUserInitials').textContent = u.name[0];
    document.getElementById('selUserName').textContent = u.name;
    document.getElementById('selUserMeta').textContent = (u.employee_id || u.email || '') + (u.designation ? ' · ' + u.designation : '');
    document.getElementById('selectedUserPreview').classList.remove('hidden');
    document.getElementById('startScanBtn').disabled = false;
}

window.clearSelectedUser = function() {
    selUserId = null;
    document.getElementById('selectedUserPreview').classList.add('hidden');
    document.getElementById('startScanBtn').disabled = true;
};

// ── Camera ────────────────────────────────────────────────────────────
async function initCam() {
    document.getElementById('scanStatusLabel').textContent = 'Starting...';
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        document.getElementById('scanStatusLabel').textContent = 'HTTPS Required';
        alert("Camera access is blocked by the browser. When accessing via a local network IP (like 192.168.x.x) on mobile, browsers require a secure HTTPS connection to use the camera. Please use HTTPS, or test on localhost.");
        return;
    }
    try {
        let s;
        try {
            s = await navigator.mediaDevices.getUserMedia({ video: { facingMode: currentFacingMode } });
        } catch (err1) {
            // Fallback if facingMode is unsupported or throws error
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
                document.getElementById('scanStatusLabel').textContent = `W:${vid.videoWidth} H:${vid.videoHeight} - ` + (track ? track.label : 'Scanning...');
                if (!drawLoopRunning) { drawLoopRunning = true; requestAnimationFrame(drawLoop); }
            };
            checkVideoReady();
        });
    } catch(e) {
        document.getElementById('scanStatusLabel').textContent = 'Cam error';
        alert("Camera Error: " + e.message);
    }
}

window.toggleCam = function() {
    currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
    window.restartCam();
};

window.restartCam = function() {
    if(vid.srcObject) vid.srcObject.getTracks().forEach(t=>t.stop());
    initCam();
};

function resizeOverlay() {
    ovl.width = vid.videoWidth; ovl.height = vid.videoHeight;
    capCvs.width = ovl.width;  capCvs.height = ovl.height;
}

window.beginScan = function() {
    if (!selUserId) return;
    document.getElementById('step1Card').classList.add('hidden');
    document.getElementById('step2Card').classList.remove('hidden');
    buildAngleDots(); updateAngleUI(0);
};

// ── Camera ────────────────────────────────────────────────────────────────────────
async function startCamera() {
    try {
        camStream = await navigator.mediaDevices.getUserMedia({ video:{width:{ideal:1280},height:{ideal:720},facingMode:'user'} });
        vid.srcObject = camStream;
        vid.onloadedmetadata = () => vid.play().catch(()=>{});
        vid.addEventListener('playing', () => {
            ovl.width = vid.videoWidth; ovl.height = vid.videoHeight;
            capCvs.width = ovl.width;  capCvs.height = ovl.height;
            camReady = true;
            if (!drawLoopRunning) { drawLoopRunning = true; requestAnimationFrame(drawLoop); }
            startAngle(curAngle);
        }, {once:true});
    } catch(e) { showAlert('error', 'Camera Error: ' + e.message); }
}

function stopCamera() {
    camReady = false;
    if (camStream) { camStream.getTracks().forEach(t => t.stop()); camStream = null; }
}

// ── Draw Loop ─────────────────────────────────────────────────────────────────────
function drawLoop() {
    if (!camReady || !vid.videoWidth) { requestAnimationFrame(drawLoop); return; }
    if (ovl.width !== vid.videoWidth) { ovl.width = vid.videoWidth; ovl.height = vid.videoHeight; }

    const W = ovl.width, H = ovl.height;
    const ctx = ovl.getContext('2d');
    const cx = W * 0.5, cy = H * 0.47;
    const rx = W * 0.27, ry = H * 0.43;

    // ── Draw mirrored video frame onto capCvs ───────────────────────────────────
    const cc = capCvs.getContext('2d');
    cc.save(); cc.translate(capCvs.width, 0); cc.scale(-1,1);
    cc.drawImage(vid, 0, 0, capCvs.width, capCvs.height); cc.restore();

    // ── Dark overlay with oval cutout ──────────────────────────────────────────
    ctx.clearRect(0,0,W,H);
    ctx.save();
    ctx.beginPath(); ctx.ellipse(cx, cy, rx, ry, 0, 0, Math.PI*2);
    ctx.fillStyle = 'rgba(0,0,0,0.70)';
    ctx.fillRect(0,0,W,H);
    ctx.globalCompositeOperation = 'destination-out';
    ctx.fill(); ctx.restore();

    // ── Oval border ─────────────────────────────────────────────────────────────
    const captured = capturedImgs[curAngle];
    ctx.save();
    ctx.beginPath(); ctx.ellipse(cx, cy, rx, ry, 0, 0, Math.PI*2);
    if (phase === 'capturing' || captured) {
        ctx.strokeStyle = '#22c55e'; ctx.shadowColor = 'rgba(34,197,94,0.8)'; ctx.shadowBlur = 20;
    } else if (phase === 'countdown') {
        ctx.strokeStyle = '#f59e0b'; ctx.shadowColor = 'rgba(245,158,11,0.8)'; ctx.shadowBlur = 18;
    } else {
        ctx.strokeStyle = '#6366f1'; ctx.shadowColor = 'rgba(99,102,241,0.5)'; ctx.shadowBlur = 12;
    }
    ctx.lineWidth = 3.5; ctx.stroke(); ctx.restore();

    // ── Face-fit detection ─────────────────────────────────────────────────────
    if (phase === 'idle') detectAndWait(cc, W, H, cx, cy, rx, ry);

    requestAnimationFrame(drawLoop);
}

let lastDetectTime = 0;
let isDetecting = false;

async function detectAndWait(cc, W, H, cx, cy, rx, ry) {
    if (isDetecting) return;
    const now = Date.now();
    if (now - lastDetectTime < 250) return; // 4 FPS polling
    
    isDetecting = true;
    lastDetectTime = now;

    const base64Img = capCvs.toDataURL('image/jpeg', 0.6);

    try {
        const res = await fetch('http://127.0.0.1:8000/api/detect-frame', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({image_base64: base64Img.split(',')[1]})
        });
        const data = await res.json();

        if (data.success && data.is_valid) {
            const pitch = data.pitch;
            const yaw = data.yaw;
            let poseValid = false;
            let poseMsg = 'Align face in oval';

            const angleId = ANGLES[curAngle].id;
            if (angleId === 'center') {
                if (Math.abs(yaw) < 0.15 && pitch > 0.8 && pitch < 1.3) poseValid = true;
                else poseMsg = 'Look straight ahead';
            } else if (angleId === 'left') {
                if (yaw < -0.15) poseValid = true;
                else poseMsg = 'Turn head to YOUR left';
            } else if (angleId === 'right') {
                if (yaw > 0.15) poseValid = true;
                else poseMsg = 'Turn head to YOUR right';
            } else if (angleId === 'up') {
                if (pitch < 0.8) poseValid = true;
                else poseMsg = 'Tilt head slightly up';
            } else if (angleId === 'down') {
                if (pitch > 1.3) poseValid = true;
                else poseMsg = 'Tilt head slightly down';
            }

            // Check if bounding box center is roughly within the circle
            const [x1, y1, x2, y2] = data.bbox;
            const bCx = (x1 + x2) / 2;
            const bCy = (y1 + y2) / 2;
            
            // capCvs size could be different from detection size if we resized in backend, but we send it at original scale.
            // Actually, the backend resizes it to max 640. So we need to use the returned img_width/img_height to scale it back.
            const scaleX = W / data.img_width;
            const scaleY = H / data.img_height;
            const realBCx = bCx * scaleX;
            const realBCy = bCy * scaleY;

            if (Math.abs(realBCx - cx) > rx * 0.8 || Math.abs(realBCy - cy) > ry * 0.8) {
                poseValid = false;
                poseMsg = 'Center your face in the oval';
            }

            if (poseValid) {
                faceFrames++;
                if (faceFrames >= 3) {
                    phase = 'countdown';
                    faceFrames = 0;
                    statusEl.className = 'freg-status-bar wait';
                    statusEl.textContent = '✅ Perfect pose!';
                    runCountdown(ANGLES[curAngle].cdCount);
                } else {
                    statusEl.className = 'freg-status-bar good';
                    statusEl.textContent = `✅ Hold still... (${faceFrames}/3)`;
                }
            } else {
                faceFrames = 0;
                statusEl.className = 'freg-status-bar idle';
                statusEl.textContent = `❌ ${poseMsg}`;
            }
        } else {
            faceFrames = 0;
            statusEl.className = 'freg-status-bar idle';
            statusEl.textContent = `⚠️ ${data.error_message || 'Face not detected'}`;
            // If text is too long, we might need to truncate, but it's fine for now
        }
    } catch (e) {
        // Silent fail on network error, will retry on next tick
    } finally {
        isDetecting = false;
    }
}


// ── Per-angle start ────────────────────────────────────────────────────────────
function startAngle(idx) {
    phase = 'idle'; faceFrames = 0;
    updateAngleUI(idx); updateAngleDots();
    scanLine.style.display = 'none';
    cdEl.style.display = 'none';
    statusEl.className = 'freg-status-bar idle';
    statusEl.textContent = '👤 Align face in oval';
}

// ── Countdown before capture ───────────────────────────────────────────────────
function runCountdown(n) {
    clearTimeout(cdTimer);
    countRemain = n;
    cdEl.style.display = 'block'; cdEl.textContent = countRemain;
    statusEl.textContent = `📸 Capturing in ${countRemain}...`;
    statusEl.className = 'freg-status-bar wait';

    cdTimer = setInterval(() => {
        countRemain--;
        cdEl.textContent = countRemain;
        statusEl.textContent = `📸 Capturing in ${countRemain}...`;
        if (countRemain <= 0) {
            clearInterval(cdTimer); cdTimer = null;
            cdEl.style.display = 'none';
            captureCurrentAngle();
        }
    }, 1000);
}

// ── Capture the current angle ──────────────────────────────────────────────────
function captureCurrentAngle() {
    phase = 'capturing';
    scanLine.style.display = 'block';
    statusEl.className = 'freg-status-bar scan';
    statusEl.textContent = `⚡ Capturing ${ANGLES[curAngle].label}...`;

    // Small delay for animation
    setTimeout(() => {
        const img = capCvs.toDataURL('image/jpeg', 0.95);
        capturedImgs[curAngle] = img;
        updateAngleDots();

        scanLine.style.display = 'none';
        statusEl.className = 'freg-status-bar done';
        statusEl.textContent = `✅ ${ANGLES[curAngle].label} captured!`;

        setTimeout(() => {
            if (curAngle < ANGLES.length - 1) {
                curAngle++;
                startAngle(curAngle);
            } else {
                phase = 'submitting';
                submitAllAngles();
            }
        }, 800);
    }, 300);
}

// ── Submit all captured angles ─────────────────────────────────────────────────
async function submitAllAngles() {
    if (submitting) return;
    submitting = true;
    stopCamera();

    if (!selUserId) { showAlert('error', 'No user selected. Please go back and select a user.'); submitting = false; return; }

    statusEl.className = 'freg-status-bar scan';
    statusEl.textContent = '⬆ Uploading all angles...';
    showAlert('info', `<span style="display:inline-flex;align-items:center;gap:6px;"><span style="width:12px;height:12px;border:2px solid #6366f1;border-top-color:transparent;border-radius:50%;animation:spin .8s linear infinite;display:inline-block;"></span>Submitting ${capturedImgs.length} face angles...</span>`);

    try {
        const resp = await fetch(API_REG, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                user_id: selUserId,
                images_base64: capturedImgs.filter(Boolean)
            })
        });
        let data = null;
        try { data = await resp.json(); } catch(e) {}

        if (data && data.success) {
            hideAlert();
            statusEl.className = 'freg-status-bar done';
            statusEl.textContent = '✅ Registration complete!';

            const userName = data.data?.employee_name || data.data?.name || 'User';
            const count    = data.data?.embeddings_registered || capturedImgs.length;
            const msg = document.getElementById('fregSuccessMsg');
            msg.innerHTML = `✅ <b>${userName}</b> registered with <b>${count} face angle(s)</b> for maximum accuracy!`;
            document.getElementById('fregDoneActions').classList.remove('hidden');
            loadUsers(); loadEmps();
        } else {
            showAlert('error', '❌ ' + (data?.detail || data?.message || 'Registration failed. Please retry.'));
            submitting = false;
        }
    } catch(e) {
        showAlert('error', '❌ Network error: ' + e.message);
        submitting = false;
    }
}

// ── Reset ──────────────────────────────────────────────────────────────────────
window.resetReg = function() {
    clearInterval(cdTimer); cdTimer = null;
    stopCamera();
    curAngle = 0; capturedImgs = []; faceFrames = 0; phase = 'idle'; submitting = false;
    selUserId = null;
    document.getElementById('selectedUserPreview').classList.add('hidden');
    document.getElementById('startScanBtn').disabled = true;
    document.getElementById('userSearch').value = '';
    renderUsers(allUsers);
    document.getElementById('step2Card').classList.add('hidden');
    document.getElementById('step1Card').classList.remove('hidden');
    document.getElementById('fregDoneActions').classList.add('hidden');
    hideAlert();
    drawLoopRunning = false;
};

// ── Alert helpers ──────────────────────────────────────────────────────────────
function showAlert(type, html) {
    const el = document.getElementById('fregAlert');
    const s = {success:'bg-emerald-500/10 border-emerald-500/20 text-emerald-300',error:'bg-red-500/10 border-red-500/20 text-red-300',info:'bg-indigo-500/10 border-indigo-500/20 text-indigo-300',warn:'bg-amber-500/10 border-amber-500/20 text-amber-300'};
    el.className = `mt-3 rounded-xl border text-xs p-3 text-center ${s[type]||s.info}`;
    el.innerHTML = html; el.classList.remove('hidden');
}
function hideAlert() { document.getElementById('fregAlert').classList.add('hidden'); }

// ── Registered employees list ──────────────────────────────────────────────────
let allEmps = [];
async function loadEmps() {
    const body = document.getElementById('empListBody');
    try {
        const r = await fetch(API_LIST);
        const d = await r.json();
        allEmps = (d && d.success && d.data) ? d.data : [];

        // Get embedding count per employee
        await loadEmpEmbCounts();
        renderEmps(allEmps);
    } catch(e) { body.innerHTML = '<tr><td colspan="6" class="py-3 text-center text-slate-500 text-xs">Unable to load.</td></tr>'; }
}

// Fetch embedding counts per employee from a quick summary
let embCounts = {};
async function loadEmpEmbCounts() {
    try {
        const r = await fetch('/psnf/public/attendance/api.php?endpoint=/api/employees/list');
        const d = await r.json();
        // embedding_count comes from the API if available
        if (d && d.data) {
            d.data.forEach(e => { if (e.embedding_count !== undefined) embCounts[e.id] = e.embedding_count; });
        }
    } catch(e) {}
}

function renderEmps(list) {
    const body = document.getElementById('empListBody');
    const badge = document.getElementById('empCountBadge');
    badge.textContent = list.length + ' registered';
    if (!list.length) { body.innerHTML = '<tr><td colspan="6" class="py-3 text-center text-slate-500 text-xs">No registered employees.</td></tr>'; return; }
    body.innerHTML = '';
    list.forEach((e,i) => {
        const photo = e.image_path
            ? `<img src="/psnf/backend/${e.image_path}" class="reg-emp-photo" alt="Face">`
            : `<div class="reg-emp-photo bg-slate-800 flex items-center justify-center text-slate-500">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
               </div>`;
        const ec = embCounts[e.id] ? `<span class="ml-1 text-2xs text-indigo-400 bg-indigo-500/10 px-1 rounded">${embCounts[e.id]}v</span>` : '';
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="py-2 pr-2 text-slate-500 text-2xs">${i+1}</td>
            <td class="py-2 pr-2">${photo}</td>
            <td class="py-2 pr-2"><span class="font-mono text-2xs text-indigo-400 bg-indigo-500/10 px-1.5 py-0.5 rounded">${e.employee_code||'EMP-'+e.id}</span></td>
            <td class="py-2 pr-2 font-medium text-white text-xs">${e.name}${ec}</td>
            <td class="py-2 pr-2 text-2xs text-slate-500">${embCounts[e.id]||1} angle${(embCounts[e.id]||1)>1?'s':''}</td>
            <td class="py-2 text-right">
                <button onclick="deleteEmp(${e.id},'${e.name.replace(/'/g,"\\'")}') " class="p-1.5 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </td>`;
        body.appendChild(tr);
    });
}

window.filterEmps = function() {
    const q = (document.getElementById('empSearch').value||'').toLowerCase();
    if (!q) { renderEmps(allEmps); return; }
    renderEmps(allEmps.filter(e => (e.employee_code||'').toLowerCase().includes(q)||(e.name||'').toLowerCase().includes(q)));
};

window.deleteEmp = async function(id, name) {
    if (!confirm(`Delete all face profiles for ${name}?`)) return;
    try {
        const r = await fetch(`${API_DEL}&id=${id}`, {method:'DELETE'});
        const d = await r.json();
        if (d && d.success) loadEmps(); else alert(d.message||'Delete failed.');
    } catch(e) { alert('Error: '+e.message); }
};

// CSS spin
const st = document.createElement('style');
st.textContent='@keyframes spin{to{transform:rotate(360deg)}}';
document.head.appendChild(st);

loadUsers();
loadEmps();
})();
</script>

<?php
$content = ob_get_clean();
?>
