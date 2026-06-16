const state = {
  starter: "I want",
  selectedItem: null
};

const categories = {
  "I want": [
    { label: "to swing", title: "to Swing", image: "images/sentancebuild/swing.png" },
    { label: "to go to washroom", title: "Washroom", image: "images/sentancebuild/washroom.png" },
    { label: "tiffin box", title: "Tiffin Box", image: "images/sentancebuild/lunch box.png" },
    { label: "choco pie", title: "Choco Pie", image: "images/sentancebuild/choco pie.png" },
    { label: "stamps", title: "Stamps", image: "images/sentancebuild/stamp.png" },
    { label: "crayon", title: "Crayon", image: "images/sentancebuild/crayon.png" },
    { label: "beads", title: "Beads", image: "images/sentancebuild/beads.png" }
  ],
  "I don't want": [
    { label: "to be sick", title: "Sick", image: "images/sentancebuild/sick.png" },
    { label: "to be hurt", title: "Hurt", image: "images/sentancebuild/hurt.png" },
    { label: "noise", title: "Noise", image: "images/sentancebuild/noise.png" },
    { label: "spicy food", title: "Spicy Food", image: "images/sentancebuild/spicy.png" }
  ],
  "I watch": [
    { label: "aa ha tamatar", title: "Aa Ha Tamatar", image: "images/sentancebuild/tamatar.png" },
    { label: "motu patlu", title: "Motu Patlu", image: "images/sentancebuild/motu patlu.png" },
    { label: "number song", title: "Number Song", image: "images/sentancebuild/song.png" },
    { label: "tom and jerry", title: "Tom and Jerry", image: "images/sentancebuild/tom jerry.png" }
  ],
  "I feel": [
    { label: "cold", title: "Cold", image: "images/sentancebuild/cold.png" },
    { label: "hot", title: "Hot", image: "images/sentancebuild/hot.png" },
    { label: "thirsty", title: "Thirsty", image: "images/sentancebuild/thirsty.png" },
    { label: "hungry", title: "Hungry", image: "images/sentancebuild/hungry.png" },
    { label: "happy", title: "Happy", image: "images/sentancebuild/happy.png" },
    { label: "sad", title: "Sad", image: "images/sentancebuild/sad.png" }
  ],
  "I like": [
    { label: "cake", title: "Cake", image: "images/sentancebuild/cake.png" },
    { label: "merry go round", title: "Merry Go Round", image: "images/sentancebuild/marrygo.png" },
    { label: "sensory room", title: "Sensory Room", image: "images/sentancebuild/sensory room.png" },
    { label: "hedgehog game", title: "Hedgehog Game", image: "images/sentancebuild/hedgehog.png" },
    { label: "pizza", title: "Pizza", image: "images/sentancebuild/pizza.png" },
    { label: "ice cream", title: "Ice Cream", image: "images/sentancebuild/ice-cream.png" },
    { label: "ball", title: "Ball", image: "images/sentancebuild/ball.png" },
    { label: "maggi", title: "Maggi", image: "images/sentancebuild/maggi.png" }
  ]
};

const el = {
  restartTopBtn: document.getElementById("restartTopBtn"),
  resetGameBtn: document.getElementById("resetGameBtn"),
  listenTopBtn: document.getElementById("listenTopBtn"),
  listenSentenceBtn: document.getElementById("listenSentenceBtn"),
  speakSentenceIcon: document.getElementById("speakSentenceIcon"),
  audioSubtitle: document.getElementById("audioSubtitle"),
  sentenceText: document.getElementById("sentenceText"),
  previewImageWrap: document.getElementById("previewImageWrap"),
  dropArea: document.getElementById("dropArea"),
  imageGrid: document.getElementById("imageGrid"),
  starterButtons: document.querySelectorAll(".starter-btn"),
  appShell: document.querySelector(".app-shell")
};

function setAudioSubtitle(text) {
  if (el.audioSubtitle) {
    el.audioSubtitle.textContent = text ? `Audio: ${text}` : "";
  }
}

function buildSentenceText() {
  if (!state.selectedItem) {
    return `${state.starter} ...`;
  }
  return `${state.starter} ${state.selectedItem.label}`;
}

function renderSentence() {
  const sentence = buildSentenceText();
  const words = sentence.split(" ");
  const highlighted = words.length > 2 && state.selectedItem
    ? `${words.slice(0, -1).join(" ")} <span style="color:#6a4df5;">${words[words.length - 1]}</span>`
    : sentence;

  el.sentenceText.innerHTML = highlighted;

  if (state.selectedItem) {
    el.previewImageWrap.classList.remove("empty");
    el.previewImageWrap.innerHTML = `<img src="${state.selectedItem.image}" alt="${state.selectedItem.title}" loading="lazy" decoding="async" />`;
  } else {
    el.previewImageWrap.classList.add("empty");
    el.previewImageWrap.innerHTML = "<span>Selected image will appear here</span>";
  }
}

function speak(text) {
  if (!window.speechSynthesis || !text || text.includes("...")) {
    return;
  }

  setAudioSubtitle(text);
  window.speechSynthesis.cancel();

  // Use global speech normalizer
  const spokenText = window.normalizeSpeechText ? window.normalizeSpeechText(text) : text;
  const utter = new SpeechSynthesisUtterance(spokenText);

  // Select the best voice using our global helper
  const voices = window.speechSynthesis.getVoices();
  const selectedVoice = window.getBestVoice ? window.getBestVoice(voices) : voices.find(v => v.lang.startsWith("en"));
  if (selectedVoice) {
    utter.voice = selectedVoice;
  }

  // Load speed and pitch from the voice controller settings
  const settings = window.getVoiceSettings ? window.getVoiceSettings() : { rate: 0.85, pitch: 1.0 };
  utter.rate = settings.rate;
  utter.pitch = settings.pitch;
  utter.volume = 1;    // Full volume
  utter.lang = "en-US";

  window.speechSynthesis.speak(utter);
}

function playFeedback(kind) {
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

function selectStarter(button) {
  state.starter = button.dataset.starter;
  state.selectedItem = null;
  el.starterButtons.forEach((btn) => {
    const active = btn === button;
    btn.classList.toggle("active", active);
    btn.setAttribute("aria-checked", String(active));
  });
  renderImageGrid();
  renderSentence();
}

function bindStarterDragEvents() {
  el.starterButtons.forEach((button) => {
    button.addEventListener("click", () => selectStarter(button));

    button.addEventListener("dragstart", (event) => {
      event.dataTransfer.effectAllowed = "copy";
      event.dataTransfer.setData("text/plain", button.dataset.starter);
      button.classList.add("dragging");
    });

    button.addEventListener("dragend", () => {
      button.classList.remove("dragging");
    });
  });
}

function handleDropItem(itemLabel) {
  const currentItems = categories[state.starter] || [];
  const item = currentItems.find((entry) => entry.label === itemLabel);
  if (!item) {
    return false;
  }
  state.selectedItem = item;
  renderSentence();
  flashFeedback("success");
  playFeedback("success");
  setAudioSubtitle(`Hurray! ${buildSentenceText()}`);
  speak(`Hurray! ${buildSentenceText()}`);
  return true;
}

function renderImageGrid() {
  const items = categories[state.starter] || [];
  el.imageGrid.innerHTML = items
    .map((item) => `
      <div class="image-item draggable" draggable="true" data-label="${item.label}" role="button" tabindex="0" aria-label="Drag ${item.title}">
        <img src="${item.image}" alt="${item.title}" loading="lazy" decoding="async" />
        <span class="item-label">${item.title}</span>
      </div>
    `)
    .join("");

  document.querySelectorAll(".image-item").forEach((element) => {
    element.addEventListener("dragstart", (event) => {
      const label = event.currentTarget.dataset.label;
      event.dataTransfer.effectAllowed = "copy";
      event.dataTransfer.setData("text/plain", label);
      event.currentTarget.classList.add("dragging");
    });

    element.addEventListener("dragend", (event) => {
      event.currentTarget.classList.remove("dragging");
    });

    element.addEventListener("click", () => {
      const label = element.dataset.label;
      handleDropItem(label);
    });

  });
}

function resetGame() {
  state.starter = "I want";
  state.selectedItem = null;
  const defaultStarterBtn = Array.from(el.starterButtons).find((btn) => btn.dataset.starter === "I want");
  if (defaultStarterBtn) {
    selectStarter(defaultStarterBtn);
  }
  renderSentence();
}

function bindEvents() {
  if (el.restartTopBtn) {
    el.restartTopBtn.addEventListener("click", () => window.location.reload());
  }
  if (el.resetGameBtn) {
    el.resetGameBtn.addEventListener("click", resetGame);
  }

  if (el.listenTopBtn) {
    el.listenTopBtn.addEventListener("click", () => speak(buildSentenceText()));
  }
  if (el.listenSentenceBtn) {
    el.listenSentenceBtn.addEventListener("click", () => speak(buildSentenceText()));
  }
  if (el.speakSentenceIcon) {
    el.speakSentenceIcon.addEventListener("click", () => speak(buildSentenceText()));
  }

  bindStarterDragEvents();

  if (el.dropArea) {
    el.dropArea.addEventListener("dragover", (event) => {
      event.preventDefault();
      event.dataTransfer.dropEffect = "copy";
      el.dropArea.classList.add("dragover");
    });

    el.dropArea.addEventListener("dragleave", () => {
      el.dropArea.classList.remove("dragover");
    });

    el.dropArea.addEventListener("drop", (event) => {
      event.preventDefault();
      el.dropArea.classList.remove("dragover");
      const data = event.dataTransfer.getData("text/plain");

      // Check if it's a starter phrase or an item label
      if (categories[data]) {
        // It's a starter phrase
        const button = Array.from(el.starterButtons).find(btn => btn.dataset.starter === data);
        if (button) {
          selectStarter(button);
        }
      } else {
        // It's an item label
        const ok = handleDropItem(data);
        if (!ok) {
          flashFeedback("error");
          playFeedback("error");
          speak("OwO! Try again.");
        }
      }
    });
  }
}

function init() {
  renderImageGrid();
  renderSentence();
  bindEvents();
}

init();
