// DATA
const SIGNS = [
  { id: "stop", name: "Stop", hint: "This sign tells vehicles to stop.", image: "images/safety/stop.png" },
  { id: "noEntry", name: "No Entry", hint: "This sign means do not enter.", image: "images/safety/no entry.png" },
  { id: "stairway", name: "Stairway", hint: "This sign indicates a stairway.", image: "images/safety/stairway.png" },
  { id: "doNotTouch", name: "Do Not Touch", hint: "This sign indicates something dangerous to touch.", image: "images/safety/not touch.png" },
  { id: "danger", name: "Danger", hint: "This sign warns of a dangerous hazard.", image: "images/safety/danger.png" },
  { id: "hospital", name: "Hospital", hint: "This sign indicates a medical facility.", image: "images/safety/hospital.png" },
  { id: "fireDanger", name: "Fire Danger", hint: "This sign warns of fire hazard.", image: "images/safety/fire danger.png" },
  { id: "electricHazard", name: "Electric Hazard", hint: "This sign warns of electrical danger.", image: "images/safety/electric.png" },
  { id: "poison", name: "Poison", hint: "This sign warns of poisonous substance.", image: "images/safety/posion.png" },
  { id: "emergencyExit", name: "Emergency Exit", hint: "This sign indicates an emergency exit route.", image: "images/safety/emergency.png" },
  { id: "restroom", name: "Restroom", hint: "This sign indicates restroom facilities.", image: "images/safety/restroom.png" },
  { id: "fireExtinguisher", name: "Fire Extinguisher", hint: "This sign indicates location of fire extinguisher.", image: "images/safety/extinguisher.png" },
  { id: "noSmoking", name: "No Smoking", hint: "This sign indicates smoking is not allowed.", image: "images/safety/no smoke.png" },
  { id: "airport", name: "Airport", hint: "This sign indicates airport facilities.", image: "images/safety/airport.png" },
  { id: "handicap", name: "Handicap", hint: "This sign indicates accessible facilities for people with disabilities.", image: "images/safety/handicap.png" },
  { id: "menAtWork", name: "Men At Work", hint: "This sign indicates construction work area.", image: "images/safety/work.png" },
  { id: "railroadCrossing", name: "Railroad Crossing", hint: "This sign warns of railroad crossing ahead.", image: "images/safety/crossing.png" },
  { id: "noPets", name: "No Pets", hint: "This sign indicates pets are not allowed.", image: "images/safety/no pet.png" },
  { id: "noFoodAndDrinks", name: "No Food And Drinks", hint: "This sign indicates food and drinks are not allowed.", image: "images/safety/no food drink.png" },
  { id: "slipperyWhenWet", name: "Slippery When Wet", hint: "This sign warns of slippery surfaces when wet.", image: "images/safety/wet.png" },
  { id: "escalator", name: "Escalator", hint: "This sign indicates escalator location.", image: "images/safety/escalotor.png" },
  { id: "busStop", name: "Bus Stop", hint: "This sign indicates bus stop location.", image: "images/safety/bus.png" },
  { id: "seatBelt", name: "Seat Belt", hint: "This sign indicates wearing seat belt is required.", image: "images/safety/seat belt.png" },
  { id: "recycle", name: "Recycle", hint: "This sign indicates recycling facilities.", image: "images/safety/recycle.png" },
  { id: "pull", name: "Pull", hint: "This sign indicates to pull the door or handle.", image: "images/safety/pull.png" },
  { id: "push", name: "Push", hint: "This sign indicates to push the door or handle.", image: "images/safety/push.png" },
  { id: "school", name: "School", hint: "This sign indicates a school facility.", image: "images/safety/school.png" },
  { id: "open", name: "Open", hint: "This sign indicates to open the door or handle.", image: "images/safety/open.png" },
  { id: "close", name: "Close", hint: "This sign indicates to close the door or handle.", image: "images/safety/close.png" },

];
const ROUNDS = 10;
const TIME_PER_GAME = 90;
const XP_PER_ROUND = 50;
const STARS_PER_ROUND = 10;
const FALLBACK_SIGN = "images/money/main.png";

const state = { stars: 0, xp: 0, round: 0, rounds: [], selectedSignId: null, timeLeft: TIME_PER_GAME, timerId: null, locked: false };

// UI
const el = {
  starsCount: document.getElementById("starsCount"), xpCount: document.getElementById("xpCount"), xpFill: document.getElementById("xpFill"),
  levelCount: document.getElementById("levelCount"), listenBtn: document.getElementById("listenBtn"), questionText: document.getElementById("questionText"),
  timerText: document.getElementById("timerText"), hintText: document.getElementById("hintText"), roundText: document.getElementById("roundText"),
  signGrid: document.getElementById("signGrid"), roundsList: document.getElementById("roundsList"), checkBtn: document.getElementById("checkBtn"),
  hintBtn: document.getElementById("hintBtn"), resetTopBtn: document.getElementById("resetTopBtn"), audioSubtitle: document.getElementById("audioSubtitle"), toast: document.getElementById("toast"), confettiCanvas: document.getElementById("confettiCanvas"),
  appShell: document.querySelector(".app-shell")
};

// GAME LOGIC
function shuffle(list) { const a = [...list]; for (let i = a.length - 1; i > 0; i -= 1) { const j = Math.floor(Math.random() * (i + 1));[a[i], a[j]] = [a[j], a[i]]; } return a; }
function currentRound() { return state.rounds[state.round]; }
function generateRounds() { return shuffle(SIGNS).slice(0, ROUNDS); }

function updateStats() {
  el.starsCount.textContent = String(state.stars);
  el.xpCount.textContent = String(state.xp);
  el.levelCount.textContent = String(state.round + 1);
  el.xpFill.style.width = `${(state.xp / (ROUNDS * XP_PER_ROUND)) * 100}%`;
}
function updateSidebar() {
  const roundObj = currentRound();
  el.questionText.textContent = roundObj ? `${roundObj.name.toUpperCase()} SIGN` : "COMPLETE";
  el.hintText.textContent = roundObj ? roundObj.hint : "Great work!";
  el.roundText.textContent = `${Math.min(state.round + 1, ROUNDS)} / ${ROUNDS}`;
}
function renderRoundsList() {
  el.roundsList.innerHTML = state.rounds.map((r, idx) => `<li class="${idx === state.round ? "active" : ""}">Round ${idx + 1} → ${r.name}</li>`).join("");
}
function renderGrid() {
  const signChoices = shuffle(SIGNS);
  el.signGrid.innerHTML = signChoices.map((s) => `<article class="sign-card" data-sign-id="${s.id}"><img src="${s.image}" alt="${s.name}" loading="lazy" decoding="async" onerror="this.src='${FALLBACK_SIGN}'" /><p>${s.name}</p></article>`).join("");
  el.signGrid.querySelectorAll(".sign-card").forEach((card) => {
    card.addEventListener("click", () => {
      if (state.locked) return;
      state.selectedSignId = card.dataset.signId || null;
      el.signGrid.querySelectorAll(".sign-card").forEach((c) => c.classList.remove("selected"));
      card.classList.add("selected");
      autoCheck();
    });
  });
}

function startNewRound() {
  if (state.round >= ROUNDS) {
    showToast("Amazing! You completed Safety Signs!", "success");
    speak("Amazing. You completed all rounds.");
    state.locked = true;
    return;
  }
  state.selectedSignId = null;
  state.locked = false;
  updateStats(); updateSidebar(); renderRoundsList(); renderGrid();
  speak(`Find ${currentRound().name} sign`);
}

function nextRound() { state.round += 1; startNewRound(); }

function checkSelection() {
  if (state.locked || !currentRound()) return;
  if (!state.selectedSignId) { showToast("Select a sign first.", "error"); return; }
  const target = currentRound();
  const selectedCard = el.signGrid.querySelector(`[data-sign-id="${state.selectedSignId}"]`);

  if (state.selectedSignId === target.id) {
    state.stars += STARS_PER_ROUND; state.xp += XP_PER_ROUND;
    updateStats(); fireConfetti();
    showSuccessFeedback(`Correct! +${STARS_PER_ROUND} Stars, +${XP_PER_ROUND} XP`, `Correct! This is ${target.name} sign.`);
    state.locked = true;
    setTimeout(nextRound, 900);
  } else {
    if (selectedCard) { selectedCard.classList.add("wrong"); setTimeout(() => selectedCard.classList.remove("wrong"), 350); }
    showErrorFeedback("Try again.", "Try again");
  }
}
function autoCheck() { checkSelection(); }

function resetGame() {
  state.rounds = generateRounds(); state.round = 0; state.stars = 0; state.xp = 0; state.timeLeft = TIME_PER_GAME;
  updateTimer(); startNewRound();
}

function updateTimer() {
  const mm = String(Math.floor(state.timeLeft / 60)).padStart(2, "0"); const ss = String(state.timeLeft % 60).padStart(2, "0");
  el.timerText.textContent = `${mm}:${ss}`;
}
function startTimer() {
  clearTimer();
  state.timerId = window.setInterval(() => {
    if (state.locked && state.round >= ROUNDS) return;
    state.timeLeft -= 1; updateTimer();
    if (state.timeLeft <= 0) {
      showErrorFeedback("Time is up. Restarting game.", "Time is up. Let us try again."); resetGame();
    }
  }, 1000);
}
function clearTimer() { if (state.timerId) { window.clearInterval(state.timerId); state.timerId = null; } }

// AUDIO
function speak(text) {
  if (!window.speechSynthesis || !text) return;
  if (el.audioSubtitle) {
    el.audioSubtitle.textContent = `Audio: ${text}`;
  }
  window.speechSynthesis.cancel();

  // Use global speech normalizer
  const spokenText = window.normalizeSpeechText ? window.normalizeSpeechText(text) : text;
  const u = new SpeechSynthesisUtterance(spokenText);

  // Select the best voice using our global helper
  const voices = window.speechSynthesis.getVoices();
  const selectedVoice = window.getBestVoice ? window.getBestVoice(voices) : voices.find(v => v.lang.startsWith("en"));
  if (selectedVoice) u.voice = selectedVoice;

  // Load speed and pitch from settings
  const settings = window.getVoiceSettings ? window.getVoiceSettings() : { rate: 0.9, pitch: 1.0 };
  u.rate = settings.rate;
  u.pitch = settings.pitch;
  u.volume = 1;
  u.lang = "en-IN";
  window.speechSynthesis.speak(u);
}
function playFeedback(kind) {
  const ac = new (window.AudioContext || window.webkitAudioContext)(); const now = ac.currentTime;

  if (kind === "success") {
    // Beautiful Chime Arpeggio: C5 -> E5 -> G5 -> C6
    const notes = [523.25, 659.25, 784.99, 1046.50];
    notes.forEach((freq, index) => {
      const osc = ac.createOscillator();
      const gain = ac.createGain();
      osc.type = "sine";
      osc.frequency.setValueAtTime(freq, now + index * 0.08);

      gain.gain.setValueAtTime(0, now + index * 0.08);
      gain.gain.linearRampToValueAtTime(0.25, now + index * 0.08 + 0.02);
      gain.gain.exponentialRampToValueAtTime(0.0001, now + index * 0.08 + 0.35);

      osc.connect(gain).connect(ac.destination);
      osc.start(now + index * 0.08);
      osc.stop(now + index * 0.08 + 0.4);
    });
  } else {
    // Failure Buzzer: Descending triangle wave with slide
    const osc = ac.createOscillator();
    const gain = ac.createGain();
    osc.type = "triangle";
    osc.frequency.setValueAtTime(220, now);
    osc.frequency.linearRampToValueAtTime(110, now + 0.35);

    gain.gain.setValueAtTime(0, now);
    gain.gain.linearRampToValueAtTime(0.35, now + 0.05);
    gain.gain.linearRampToValueAtTime(0.35, now + 0.25);
    gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.4);

    osc.connect(gain).connect(ac.destination);
    osc.start(now);
    osc.stop(now + 0.45);
  }
}

function flashFeedback(kind) {
  if (!el.appShell) return; const className = kind === "success" ? "feedback-success" : "feedback-error";
  el.appShell.classList.remove("feedback-success", "feedback-error"); void el.appShell.offsetWidth;
  el.appShell.classList.add(className);
  window.setTimeout(() => el.appShell.classList.remove(className), 520);
}

function showSuccessFeedback(message, voiceText) {
  flashFeedback("success"); playFeedback("success"); showToast(message, "success"); speak(`Hurray! ${voiceText || message}`);
}

function showErrorFeedback(message, voiceText) {
  flashFeedback("error"); playFeedback("error"); showToast(message, "error"); speak(`OwO! ${voiceText || "Try again"}`);
}

// REWARDS
let toastTimer = null;
function showToast(message, type = "") {
  el.toast.textContent = message; el.toast.className = `toast ${type} show`; window.clearTimeout(toastTimer);
  toastTimer = window.setTimeout(() => el.toast.classList.remove("show"), 2100);
}
function fireConfetti() {
  const c = el.confettiCanvas; const ctx = c.getContext("2d"); c.width = window.innerWidth; c.height = window.innerHeight;
  const bits = Array.from({ length: 90 }, () => ({ x: Math.random() * c.width, y: -20 - Math.random() * c.height * .3, size: 4 + Math.random() * 6, speed: 2 + Math.random() * 4, drift: -1 + Math.random() * 2, color: ["#6a4df5", "#4cc955", "#2f8fff", "#ffaa2b", "#ef476f"][Math.floor(Math.random() * 5)] }));
  let frames = 0; (function animate() { ctx.clearRect(0, 0, c.width, c.height); bits.forEach((b) => { b.y += b.speed; b.x += b.drift; ctx.fillStyle = b.color; ctx.fillRect(b.x, b.y, b.size, b.size * .6); }); frames += 1; if (frames < 80) requestAnimationFrame(animate); else ctx.clearRect(0, 0, c.width, c.height); })();
}

function bindEvents() {
  el.checkBtn.addEventListener("click", checkSelection);
  el.hintBtn.addEventListener("click", () => { if (!currentRound()) return; showToast(currentRound().hint); speak(currentRound().hint); });
  if (el.resetTopBtn) {
    el.resetTopBtn.addEventListener("click", () => window.location.reload());
  }
  el.listenBtn.addEventListener("click", () => { if (!currentRound()) return; speak(`Find ${currentRound().name} sign`); });
}

function init() { state.rounds = generateRounds(); updateTimer(); bindEvents(); startNewRound(); startTimer(); }
window.addEventListener("beforeunload", clearTimer);
window.addEventListener("resize", () => { el.confettiCanvas.width = window.innerWidth; el.confettiCanvas.height = window.innerHeight; });
init();
