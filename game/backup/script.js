const state = {
  stars: 250,
  xp: 1250,
  xpMax: 2000,
  level: 5,
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
    { label: "merry go round", title: "Merry Go Round", image: "images/sentancebuild/marry go.png" },
    { label: "sensory room", title: "Sensory Room", image: "images/sentancebuild/sensory room.png" },
    { label: "hedgehog game", title: "Hedgehog Game", image: "images/sentancebuild/hedgehog.png" },
    { label: "pizza", title: "Pizza", image: "images/sentancebuild/pizza.png" },
    { label: "ice cream", title: "Ice Cream", image: "images/sentancebuild/ice-cream.png" },
    { label: "ball", title: "Ball", image: "images/sentancebuild/ball.png" },
    { label: "maggi", title: "Maggi", image: "images/sentancebuild/maggi.png" }
  ]
};

const imageItems = categories["I want"];

const el = {
  homeScreen: document.getElementById("homeScreen"),
  sentenceScreen: document.getElementById("sentenceScreen"),
  homeBtn: document.getElementById("homeBtn"),
  restartTopBtn: document.getElementById("restartTopBtn"),
  resetGameBtn: document.getElementById("resetGameBtn"),
  listenTopBtn: document.getElementById("listenTopBtn"),
  listenSentenceBtn: document.getElementById("listenSentenceBtn"),
  speakSentenceIcon: document.getElementById("speakSentenceIcon"),
  sentenceText: document.getElementById("sentenceText"),
  previewImageWrap: document.getElementById("previewImageWrap"),
  dropArea: document.getElementById("dropArea"),
  imageGrid: document.getElementById("imageGrid"),
  starterButtons: document.querySelectorAll(".starter-btn"),
  sentencePlayBtn: document.querySelector('[data-game="sentence"]')
};

function updateStats() {
  const ids = ["Home", "Game"];
  ids.forEach((suffix) => {
    document.getElementById(`starsCount${suffix}`).textContent = state.stars;
    document.getElementById(`xpCount${suffix}`).textContent = state.xp;
    document.getElementById(`levelCount${suffix}`).textContent = state.level;
    document.getElementById(`xpFill${suffix}`).style.width = `${(state.xp / state.xpMax) * 100}%`;
  });
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
    el.previewImageWrap.innerHTML = `<img src="${state.selectedItem.image}" alt="${state.selectedItem.title}" />`;
  } else {
    el.previewImageWrap.classList.add("empty");
    el.previewImageWrap.innerHTML = "<span>Selected image will appear here</span>";
  }
}

function speak(text) {
  if (!window.speechSynthesis || !text || text.includes("...")) {
    return;
  }

  window.speechSynthesis.cancel();
  const utter = new SpeechSynthesisUtterance(text);

  // Get available voices
  const voices = window.speechSynthesis.getVoices();

  // Try to find a good quality voice (like Google Translate quality)
  let selectedVoice = voices.find(voice =>
    voice.name.includes('Google') ||
    voice.name.includes('Microsoft') ||
    voice.name.includes('Samantha') ||
    voice.lang.startsWith('en')
  );

  if (selectedVoice) {
    utter.voice = selectedVoice;
  }

  utter.rate = 0.85;   // Slower for clarity (like Google Translate)
  utter.pitch = 1.0;   // Natural pitch
  utter.volume = 1;    // Full volume
  utter.lang = "en-US";

  window.speechSynthesis.speak(utter);
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
    return;
  }
  state.selectedItem = item;
  renderSentence();
  speak(buildSentenceText());
}

function renderImageGrid() {
  const items = categories[state.starter] || [];
  el.imageGrid.innerHTML = items
    .map((item, index) => `
      <div class="image-item draggable" draggable="true" data-label="${item.label}" role="button" tabindex="0" aria-label="Drag ${item.title}">
        <img src="${item.image}" alt="${item.title}" />
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

function showScreen(screen) {
  const showHome = screen === "home";
  el.homeScreen.classList.toggle("active", showHome);
  el.sentenceScreen.classList.toggle("active", !showHome);
}

function createImageCard(item) {
  const card = document.createElement("button");
  card.type = "button";
  card.className = "draggable-card";
  card.draggable = true;
  card.setAttribute("aria-label", `Drag ${item.title}`);
  card.dataset.label = item.label;
  card.innerHTML = `<img src="${item.image}" alt="${item.title}" /><span>${item.title}</span>`;

  card.addEventListener("dragstart", (event) => {
    event.dataTransfer.setData("text/plain", item.label);
    event.dataTransfer.effectAllowed = "copy";
  });

  card.addEventListener("click", () => {
    handleDropItem(item.label);
  });

  return card;
}

function mountImageGrid() {
  renderImageGrid();
}

function bindEvents() {
  el.sentencePlayBtn.addEventListener("click", () => showScreen("sentence"));
  el.homeBtn.addEventListener("click", () => showScreen("home"));

  el.restartTopBtn.addEventListener("click", resetGame);
  el.resetGameBtn.addEventListener("click", resetGame);

  el.listenTopBtn.addEventListener("click", () => speak(buildSentenceText()));
  el.listenSentenceBtn.addEventListener("click", () => speak(buildSentenceText()));
  el.speakSentenceIcon.addEventListener("click", () => speak(buildSentenceText()));

  bindStarterDragEvents();

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
      handleDropItem(data);
    }
  });
}

function init() {
  mountImageGrid();
  renderSentence();
  bindEvents();
}

init();