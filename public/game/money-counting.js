// DATA
// RANDOM PLAYABLE LEVELS
function generateRandomLevels(count = 10) {
  const moneyValues = [500, 200, 100, 50, 20, 10, 5, 2, 1];
  const levels = [];

  for (let i = 0; i < count; i++) {
    let amount = 0;
    const itemCount = Math.floor(Math.random() * 4) + 2;

    for (let j = 0; j < itemCount; j++) {
      const randomValue =
        moneyValues[Math.floor(Math.random() * moneyValues.length)];

      amount += randomValue;
    }

    levels.push(amount);
  }

  return [...new Set(levels)];
}

const LEVELS = generateRandomLevels(7);
const START_TIME_SECONDS = 300;
const XP_PER_LEVEL = 50;
const STARS_PER_LEVEL = 10;
const XP_MAX = LEVELS.length * XP_PER_LEVEL;

const MONEY_ITEMS = [
  { id: "n500", value: 500, label: "₹500", image: "images/money/500.png", type: "note" },
  { id: "n200", value: 200, label: "₹200", image: "images/money/200.png", type: "note" },
  { id: "n100", value: 100, label: "₹100", image: "images/money/100.png", type: "note" },
  { id: "n50", value: 50, label: "₹50", image: "images/money/50.png", type: "note" },
  { id: "n20", value: 20, label: "₹20", image: "images/money/20.png", type: "note" },
  { id: "n10", value: 10, label: "₹10", image: "images/money/10.png", type: "note" },
  { id: "c1", value: 1, label: "₹1", image: "images/money/1.png", type: "coin" },
  { id: "c2", value: 2, label: "₹2", image: "images/money/2.png", type: "coin" },
  { id: "c5", value: 5, label: "₹5", image: "images/money/5.png", type: "coin" },
  { id: "c10", value: 10, label: "₹10 Coin", image: "images/money/10 coin.png", type: "coin" }
];

const state = {
  levelIndex: 0,
  stars: 0,
  xp: 0,
  dropped: [],
  timeLeft: START_TIME_SECONDS,
  timerId: null,
  isComplete: false
};

// UI
const el = {
  starsCount: document.getElementById("starsCount"),
  xpCount: document.getElementById("xpCount"),
  xpFill: document.getElementById("xpFill"),
  levelCount: document.getElementById("levelCount"),
  targetAmount: document.getElementById("targetAmount"),
  timerDisplay: document.getElementById("timerDisplay"),
  resetTopBtn: document.getElementById("resetTopBtn"),
  audioSubtitle: document.getElementById("audioSubtitle"),
  hintText: document.getElementById("hintText"),
  dropZone: document.getElementById("dropZone"),
  dropItems: document.getElementById("dropItems"),
  dropPlaceholder: document.getElementById("dropPlaceholder"),
  totalAmount: document.getElementById("totalAmount"),
  moneyGrid: document.getElementById("moneyGrid"),
  levelsList: document.getElementById("levelsList"),
  checkBtn: document.getElementById("checkBtn"),
  hintBtn: document.getElementById("hintBtn"),
  resetBtn: document.getElementById("resetBtn"),
  listenBtn: document.getElementById("listenBtn"),
  toast: document.getElementById("toast"),
  confettiCanvas: document.getElementById("confettiCanvas"),
  appShell: document.querySelector(".app-shell")
};

// GAME LOGIC
function currentTarget() {
  return LEVELS[state.levelIndex];
}

function totalDroppedAmount() {
  return state.dropped.reduce((sum, item) => sum + item.value, 0);
}

function formatTime(seconds) {
  const mins = String(Math.floor(seconds / 60)).padStart(2, "0");
  const secs = String(seconds % 60).padStart(2, "0");
  return `${mins}:${secs}`;
}

function buildHintText() {
  const target = currentTarget();
  const coinsOnly = MONEY_ITEMS.filter((item) => item.type === "coin" && item.value <= target);
  const bestCoin = coinsOnly.length ? coinsOnly[coinsOnly.length - 1].value : 1;
  const remaining = target % 10;
  if (target >= 100) {
    return `Try a big note first, then use coins/notes for the remaining amount ₹${target}.`;
  }
  return `Use coins and notes to make ₹${target}. Tip: ₹${bestCoin} coins can help with the last ₹${remaining || 10}.`;
}

function renderStats() {
  el.starsCount.textContent = String(state.stars);
  el.xpCount.textContent = String(state.xp);
  el.levelCount.textContent = String(state.levelIndex + 1);
  el.xpFill.style.width = `${(state.xp / XP_MAX) * 100}%`;
}

function renderQuestion() {
  el.targetAmount.textContent = `₹${currentTarget()}`;
  el.hintText.textContent = buildHintText();
}

function renderLevels() {
  el.levelsList.innerHTML = LEVELS.map((amount, index) => {
    const className = index === state.levelIndex ? "active" : "";
    return `<li class="${className}">Level ${index + 1} → Make ₹${amount}</li>`;
  }).join("");
}

function renderDropped() {
  const total = totalDroppedAmount();
  el.totalAmount.textContent = `₹${total}`;

  if (!state.dropped.length) {
    el.dropPlaceholder.style.display = "block";
    el.dropItems.innerHTML = "";
    return;
  }

  el.dropPlaceholder.style.display = "none";
  el.dropItems.innerHTML = state.dropped.map((item, index) => `
    <button class="dropped-item" type="button" data-remove-index="${index}" aria-label="Remove ${item.label}">
      <img src="${item.image}" alt="${item.label}" loading="lazy" decoding="async" />
      <span>${item.label}</span>
    </button>
  `).join("");

  el.dropItems.querySelectorAll("[data-remove-index]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const idx = Number(btn.dataset.removeIndex);
      state.dropped.splice(idx, 1);
      renderDropped();
    });
  });
}

function resetDropped() {
  state.dropped = [];
  renderDropped();
}

function moveToNextLevel() {
  if (state.levelIndex < LEVELS.length - 1) {
    state.levelIndex += 1;
    state.timeLeft = START_TIME_SECONDS;
    resetDropped();
    renderStats();
    renderQuestion();
    renderLevels();
    speak(`Great job. Now make rupees ${currentTarget()}.`);
  } else {
    state.isComplete = true;
    clearTimer();
    showToast("Fantastic! You completed all levels!", "success");
    speak("Fantastic. You completed all money counting levels.");
  }
}

function checkAnswer() {
  if (state.isComplete) return;

  const total = totalDroppedAmount();
  const target = currentTarget();

  if (total === target) {
    state.stars += STARS_PER_LEVEL;
    state.xp += XP_PER_LEVEL;
    renderStats();
    fireConfetti();
    showSuccessFeedback(
      `Correct! +${STARS_PER_LEVEL} Stars, +${XP_PER_LEVEL} XP`, 
      `Excellent. You made rupees ${target}.`, 
      () => {
        moveToNextLevel();
      }
    );
  } else {
    el.dropZone.classList.add("shake");
    setTimeout(() => el.dropZone.classList.remove("shake"), 350);
    showErrorFeedback(`Try again. You made ₹${total}. Target is ₹${target}.`, "Try again. Make the exact amount.");
  }
}

function handleTimeExpired() {
  showErrorFeedback("Time is up. Level reset.", "Time is up. Try this level again.");
  state.timeLeft = START_TIME_SECONDS;
  resetDropped();
  renderTimer();
}

function renderTimer() {
  el.timerDisplay.textContent = formatTime(state.timeLeft);
}

function startTimer() {
  clearTimer();
  state.timerId = window.setInterval(() => {
    if (state.isComplete) return;
    state.timeLeft -= 1;
    renderTimer();
    if (state.timeLeft <= 0) {
      handleTimeExpired();
    }
  }, 1000);
}

function clearTimer() {
  if (state.timerId) {
    window.clearInterval(state.timerId);
    state.timerId = null;
  }
}

// DRAG DROP
function renderMoneyGrid() {
  el.moneyGrid.innerHTML = MONEY_ITEMS.map((item) => `
    <article class="money-card" draggable="true" data-money-id="${item.id}" aria-label="Drag ${item.label}">
      <img src="${item.image}" alt="${item.label}" loading="lazy" decoding="async" />
      <p>${item.label}</p>
    </article>
  `).join("");

  el.moneyGrid.querySelectorAll(".money-card").forEach((card) => {
    card.addEventListener("dragstart", (event) => {
      event.dataTransfer.effectAllowed = "copy";
      event.dataTransfer.setData("text/plain", card.dataset.moneyId || "");
      card.classList.add("dragging");
    });

    card.addEventListener("dragend", () => {
      card.classList.remove("dragging");
    });

    let lastClickTime = 0;
    card.addEventListener("click", () => {
      const now = Date.now();
      if (now - lastClickTime < 300) return;
      lastClickTime = now;
      const item = MONEY_ITEMS.find((entry) => entry.id === card.dataset.moneyId);
      if (!item || state.isComplete) return;
      state.dropped.push(item);
      renderDropped();
    });
  });
}

function bindDropZone() {
  el.dropZone.addEventListener("dragover", (event) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = "copy";
    el.dropZone.classList.add("dragover");
  });

  el.dropZone.addEventListener("dragleave", () => {
    el.dropZone.classList.remove("dragover");
  });

  el.dropZone.addEventListener("drop", (event) => {
    event.preventDefault();
    el.dropZone.classList.remove("dragover");
    if (state.isComplete) return;

    const id = event.dataTransfer.getData("text/plain");
    const item = MONEY_ITEMS.find((entry) => entry.id === id);
    if (!item) return;
    state.dropped.push(item);
    renderDropped();
  });
}

// AUDIO
function speak(text) {
  if (!window.speechSynthesis || !text) return;

  if (el.audioSubtitle) {
    el.audioSubtitle.textContent = `Audio: ${text}`;
  }
  window.speechSynthesis.cancel();

  // Use global speech normalizer
  const spokenText = window.normalizeSpeechText ? window.normalizeSpeechText(text) : text;
  const utterance = new SpeechSynthesisUtterance(spokenText);

  // Select the best voice using our global helper
  const voices = window.speechSynthesis.getVoices();
  const selectedVoice = window.getBestVoice ? window.getBestVoice(voices) : voices.find(v => v.lang.startsWith("en"));
  if (selectedVoice) {
    utterance.voice = selectedVoice;
  }

  // Load speed and pitch from the voice controller settings
  const settings = window.getVoiceSettings ? window.getVoiceSettings() : { rate: 0.9, pitch: 1.0 };
  utterance.rate = settings.rate;
  utterance.pitch = settings.pitch;
  utterance.volume = 1;
  utterance.lang = "en-IN";

  window.speechSynthesis.speak(utterance);
}

function playFeedback(kind, onComplete) {
  const audioContext = new (window.AudioContext || window.webkitAudioContext)();
  const now = audioContext.currentTime;

  if (kind === "success") {
    // Beautiful Chime Arpeggio: C5 -> E5 -> G5 -> C6
    const notes = [523.25, 659.25, 784.99, 1046.50];
    notes.forEach((freq, index) => {
      const osc = audioContext.createOscillator();
      const gain = audioContext.createGain();
      osc.type = "sine";
      osc.frequency.setValueAtTime(freq, now + index * 0.08);

      gain.gain.setValueAtTime(0, now + index * 0.08);
      gain.gain.linearRampToValueAtTime(0.25, now + index * 0.08 + 0.02);
      gain.gain.exponentialRampToValueAtTime(0.0001, now + index * 0.08 + 0.35);

      osc.connect(gain).connect(audioContext.destination);
      osc.start(now + index * 0.08);
      osc.stop(now + index * 0.08 + 0.4);
    });

    // Play our new global success overlay with audio and video
    if (window.playSuccessOverlay) {
      window.playSuccessOverlay(onComplete);
    } else if (onComplete) {
      onComplete();
    }
  } else {
    // Failure Buzzer: Descending triangle wave with slide
    const osc = audioContext.createOscillator();
    const gain = audioContext.createGain();
    osc.type = "triangle";
    osc.frequency.setValueAtTime(220, now);
    osc.frequency.linearRampToValueAtTime(110, now + 0.35);

    gain.gain.setValueAtTime(0, now);
    gain.gain.linearRampToValueAtTime(0.35, now + 0.05);
    gain.gain.linearRampToValueAtTime(0.35, now + 0.25);
    gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.4);

    osc.connect(gain).connect(audioContext.destination);
    osc.start(now);
    osc.stop(now + 0.45);
  }
}

function flashFeedback(kind) {
  if (!el.appShell) return;
  const className = kind === "success" ? "feedback-success" : "feedback-error";
  el.appShell.classList.remove("feedback-success", "feedback-error");
  void el.appShell.offsetWidth;
  el.appShell.classList.add(className);
  window.setTimeout(() => el.appShell.classList.remove(className), 520);
}

function showSuccessFeedback(message, voiceText, onComplete) {
  flashFeedback("success");
  playFeedback("success", onComplete);
  showToast(message, "success");
  speak(`Hurray! ${voiceText || message}`);
}

function showErrorFeedback(message, voiceText) {
  flashFeedback("error");
  playFeedback("error");
  showToast(message, "error");
  speak(`OwO! ${voiceText || "Try again"}`);
}

// REWARDS
let toastTimer = null;
function showToast(message, type = "") {
  el.toast.textContent = message;
  el.toast.className = `toast ${type} show`;
  window.clearTimeout(toastTimer);
  toastTimer = window.setTimeout(() => {
    el.toast.classList.remove("show");
  }, 2100);
}

function fireConfetti() {
  const canvas = el.confettiCanvas;
  const context = canvas.getContext("2d");
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;

  const bits = Array.from({ length: 90 }, () => ({
    x: Math.random() * canvas.width,
    y: -20 - Math.random() * canvas.height * 0.3,
    size: 4 + Math.random() * 6,
    speed: 2 + Math.random() * 4,
    drift: -1 + Math.random() * 2,
    color: ["#6a4df5", "#4cc955", "#2f8fff", "#ffaa2b", "#ef476f"][Math.floor(Math.random() * 5)]
  }));

  let frames = 0;
  function animate() {
    context.clearRect(0, 0, canvas.width, canvas.height);
    bits.forEach((bit) => {
      bit.y += bit.speed;
      bit.x += bit.drift;
      context.fillStyle = bit.color;
      context.fillRect(bit.x, bit.y, bit.size, bit.size * 0.6);
    });
    frames += 1;
    if (frames < 80) {
      requestAnimationFrame(animate);
    } else {
      context.clearRect(0, 0, canvas.width, canvas.height);
    }
  }

  animate();
}

function bindControls() {
  el.checkBtn.addEventListener("click", checkAnswer);
  el.hintBtn.addEventListener("click", () => {
    const hint = buildHintText();
    showToast(hint, "");
    speak(hint);
  });
  if (el.resetTopBtn) {
    el.resetTopBtn.addEventListener("click", () => window.location.reload());
  }
  el.listenBtn.addEventListener("click", () => {
    speak(`Make rupees ${currentTarget()}`);
  });
}

function init() {
  renderStats();
  renderQuestion();
  renderLevels();
  renderDropped();
  renderMoneyGrid();
  renderTimer();
  bindDropZone();
  bindControls();
  startTimer();
  speak(`Make rupees ${currentTarget()}`);
}

window.addEventListener("beforeunload", clearTimer);
window.addEventListener("resize", () => {
  el.confettiCanvas.width = window.innerWidth;
  el.confettiCanvas.height = window.innerHeight;
});

init();
