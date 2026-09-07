// DATA
const PRODUCT_BLUEPRINTS = [
  { id: "saving-cream-sachet", name: "Saving Cream (sachet)", price: 10, image: "images/shopping/shave.png" },
  { id: "mobile-covers", name: "Mobile Covers", price: 90, image: "images/shopping/cover.png" },
  { id: "fork-spoon-single", name: "Fork / Spoon (single)", price: 110, image: "images/shopping/spoon.png" },
  { id: "disposable-razor", name: "Disposable Razor", price: 150, image: "images/shopping/razor.png" },
  { id: "wallet", name: "Wallet", price: 500, image: "images/shopping/wallet.png" },
  { id: "perfume-regular", name: "Perfume (regular)", price: 510, image: "images/shopping/sprey.webp" },
  { id: "game-kits-small-toys", name: "Game Kits / Small Toys", price: 620, image: "images/shopping/game.jpg" },
  { id: "small-bouquet", name: "Small Bouquet", price: 650, image: "images/shopping/bouquet.jpg" },
  { id: "bracelet-simple", name: "Bracelet (simple)", price: 1110, image: "images/shopping/bracelet.jpg" },
  { id: "watch", name: "Watch", price: 2350, image: "images/shopping/watch.jpg" },
  { id: "amul-milk-shakti-1", name: "Amul Milk (Shakti-1)", price: 32, image: "images/shopping/milk.jpg" },
  { id: "pencil-box-set-12", name: "Pencil Box (set of 12)", price: 110, image: "images/shopping/pencil box.jpg" },
  { id: "spoon-set-12", name: "Spoon (set of 12)", price: 200, image: "images/shopping/spoon.png" },
  { id: "kurta-basic", name: "Kurta (Basic)", price: 350, image: "images/shopping/kurta.jpg" },
  { id: "basic-headphones", name: "Basic Headphones", price: 500, image: "images/shopping/headphone.jpg" },
  { id: "dustbin", name: "Dustbin", price: 650, image: "images/shopping/dustbin.jpg" },
  { id: "goggles", name: "Goggles", price: 720, image: "images/shopping/goggles.jpg" },
  { id: "shirt-basic", name: "Shirt (Basic)", price: 750, image: "images/shopping/shirt.webp" },
  { id: "iron-press", name: "Iron (press)", price: 800, image: "images/shopping/iron.jpg" },
  { id: "silver-earring", name: "Silver Earring", price: 2300, image: "images/shopping/earring.jpg" },
  { id: "cricket-ball", name: "Cricket Ball", price: 70, image: "images/shopping/cricket ball.jpg" },
  { id: "carom-coins-basic", name: "Carom Coins (basic set)", price: 80, image: "images/shopping/carron coin.jpg" },
  { id: "table-tennis-ball", name: "Table Tennis Ball", price: 90, image: "images/shopping/tennis ball.jpg" },
  { id: "swimming-cap", name: "Swimming Cap", price: 95, image: "images/shopping/swimming cap.jpg" },
  { id: "table-tennis-racket", name: "Table Tennis Racket", price: 250, image: "images/shopping/3racket.jpg" },
  { id: "swimming-goggles", name: "Swimming Goggles", price: 260, image: "images/shopping/sgoggles.jpg" },
  { id: "skipping-rope", name: "Skipping Rope", price: 330, image: "images/shopping/skipping rope.jpg" },
  { id: "frisbee", name: "Frisbee", price: 550, image: "images/shopping/frisbee.jpg" },
  { id: "badminton-racket", name: "Badminton Racket", price: 700, image: "images/shopping/badminton racket.jpg" },
  { id: "volleyball", name: "Volleyball", price: 980, image: "images/shopping/volleyball.jpg" },
  { id: "coffee-sachet", name: "Coffee (sachet)", price: 10, image: "images/shopping/coffe.jpg" },
  { id: "french-fries", name: "French Fries", price: 115, image: "images/shopping/fries.jpg" },
  { id: "noodles", name: "Noodles", price: 180, image: "images/shopping/noodells.jpg" },
  { id: "wagh-bakri-tea-packet", name: "Wagh Bakri Tea Packet", price: 350, image: "images/shopping/tea.jpg" },
  { id: "t-shirt-basic", name: "T-Shirt (Basic)", price: 400, image: "images/shopping/tshirt.jpg" },
  { id: "pant-shirt-basic", name: "Pant Shirt (Basic)", price: 950, image: "images/shopping/pant.jpg" },
  { id: "carom-board", name: "Carom Board", price: 1000, image: "images/shopping/carom board.jpg" },
  { id: "swimming-costume", name: "Swimming Costume", price: 1100, image: "images/shopping/swimming costume.jpg" },
  { id: "cricket-bat-branded", name: "Cricket Bat Branded", price: 1700, image: "images/shopping/cricket bat.jpg" },
  { id: "cricket-gloves", name: "Cricket Gloves", price: 2000, image: "images/shopping/gloves.jpg" },
  { id: "paneer-basic", name: "Paneer (Basic)", price: 40, image: "images/shopping/paneer.jpg" },
  { id: "ketchup-basic", name: "Ketchup (Basic)", price: 60, image: "images/shopping/ketchup.jpg" },
  { id: "butter-basic", name: "Butter (Basic)", price: 75, image: "images/shopping/butter.jpg" },
  { id: "soap", name: "Soap", price: 85, image: "images/shopping/soap.jpg" },
  { id: "hair-oil", name: "Hair Oil", price: 115, image: "images/shopping/hair oil.jpg" },
  { id: "cheese", name: "Cheese", price: 130, image: "images/shopping/cheese.jpg" },
  { id: "deodorant", name: "Deodorant", price: 200, image: "images/shopping/deodorant.jpg" },
  { id: "ghee", name: "Ghee", price: 250, image: "images/shopping/ghee.jpg" },
  { id: "shampoo", name: "Shampoo", price: 270, image: "images/shopping/shampoo.jpg" },
  { id: "oil", name: "Oil", price: 2200, image: "images/shopping/oil.jpg" }
];

const MONEY_ITEMS = [
  { id: "n500", value: 500, label: "₹500", image: "images/money/500.png" },
  { id: "n200", value: 200, label: "₹200", image: "images/money/200.png" },
  { id: "n100", value: 100, label: "₹100", image: "images/money/100.png" },
  { id: "n50", value: 50, label: "₹50", image: "images/money/50.png" },
  { id: "n20", value: 20, label: "₹20", image: "images/money/20.png" },
  { id: "n10", value: 10, label: "₹10", image: "images/money/10.png" },
  { id: "c1", value: 1, label: "₹1", image: "images/money/1.png" },
  { id: "c2", value: 2, label: "₹2", image: "images/money/2.png" },
  { id: "c5", value: 5, label: "₹5", image: "images/money/5.png" },
  { id: "c10", value: 10, label: "₹10 Coin", image: "images/money/10 coin.png" }
];

const LEVEL_COUNT = 5;
const PRODUCTS_PER_PAGE = 12;
const START_WALLET = 8000;
const START_TIME_SECONDS = 300;
const XP_PER_LEVEL = 50;
const STARS_PER_LEVEL = 10;
const PRODUCT_FALLBACK = "images/money/main.png";

function shuffle(list) {
  const copy = [...list];
  for (let i = copy.length - 1; i > 0; i -= 1) {
    const j = Math.floor(Math.random() * (i + 1));
    [copy[i], copy[j]] = [copy[j], copy[i]];
  }
  return copy;
}

function generateRandomProducts() {
  return PRODUCT_BLUEPRINTS.map((item) => ({
    id: item.id,
    name: item.name,
    image: item.image,
    price: item.price
  }));
}

function makeLevelTasks(products) {
  const tasks = [];

  for (let i = 0; i < LEVEL_COUNT; i += 1) {
    const requiredCount = i < 2 ? 2 : 3;
    let requiredItems = [];
    let attempts = 0;

    while (attempts < 120) {
      const candidates = shuffle(products).slice(0, requiredCount);
      const tentative = candidates.map((item) => ({
        id: item.id,
        name: item.name,
        price: item.price,
        image: item.image,
        requiredQty: Math.random() < 0.4 ? 2 : 1
      }));
      const total = tentative.reduce((sum, item) => sum + item.price * item.requiredQty, 0);
      if (total <= START_WALLET) {
        requiredItems = tentative;
        break;
      }
      attempts += 1;
    }

    if (!requiredItems.length) {
      const fallback = shuffle(products)
        .slice(0, 2)
        .map((item) => ({
          id: item.id,
          name: item.name,
          price: item.price,
          image: item.image,
          requiredQty: 1
        }));
      requiredItems = fallback;
    }

    tasks.push({
      id: `level-${i + 1}`,
      requiredItems
    });
  }

  return tasks;
}

const PRODUCT_CATALOG = generateRandomProducts();

const state = {
  stars: 0,
  xp: 0,
  wallet: START_WALLET,
  timeLeft: START_TIME_SECONDS,
  timerId: null,
  levelIndex: 0,
  storePage: 1,
  tasks: makeLevelTasks(PRODUCT_CATALOG),
  cartItems: [],
  paidItems: [],
  isComplete: false
};

// UI
const el = {
  starsCount: document.getElementById("starsCount"),
  xpCount: document.getElementById("xpCount"),
  xpFill: document.getElementById("xpFill"),
  levelCount: document.getElementById("levelCount"),
  walletBadge: document.getElementById("walletBadge"),
  walletAmount: document.getElementById("walletAmount"),
  resetTopBtn: document.getElementById("resetTopBtn"),
  audioSubtitle: document.getElementById("audioSubtitle"),
  timerDisplay: document.getElementById("timerDisplay"),
  hintText: document.getElementById("hintText"),
  taskItem: document.getElementById("taskItem"),
  storeGrid: document.getElementById("storeGrid"),
  prevPageBtn: document.getElementById("prevPageBtn"),
  nextPageBtn: document.getElementById("nextPageBtn"),
  pageInfo: document.getElementById("pageInfo"),
  cartArea: document.getElementById("cartArea"),
  cartTotal: document.getElementById("cartTotal"),
  paymentDropZone: document.getElementById("paymentDropZone"),
  dropPlaceholder: document.getElementById("dropPlaceholder"),
  paymentItems: document.getElementById("paymentItems"),
  paidAmount: document.getElementById("paidAmount"),
  moneyGrid: document.getElementById("moneyGrid"),
  levelsList: document.getElementById("levelsList"),
  receiptBody: document.getElementById("receiptBody"),
  payNowBtn: document.getElementById("payNowBtn"),
  hintBtn: document.getElementById("hintBtn"),
  resetBtn: document.getElementById("resetBtn"),
  listenBtn: document.getElementById("listenBtn"),
  toast: document.getElementById("toast"),
  confettiCanvas: document.getElementById("confettiCanvas"),
  appShell: document.querySelector(".app-shell")
};

// GAME LOGIC
function currentTask() {
  return state.tasks[state.levelIndex];
}

function paidTotal() {
  return state.paidItems.reduce((sum, item) => sum + item.value, 0);
}

function cartTotal() {
  return state.cartItems.reduce((sum, item) => sum + item.price * item.quantity, 0);
}

function taskRequiredTotal(task) {
  return task.requiredItems.reduce((sum, item) => sum + item.price * item.requiredQty, 0);
}

function formatTime(seconds) {
  const mins = String(Math.floor(seconds / 60)).padStart(2, "0");
  const secs = String(seconds % 60).padStart(2, "0");
  return `${mins}:${secs}`;
}

function updateHintText() {
  const task = currentTask();
  const names = task.requiredItems.map((item) => `${item.name} x${item.requiredQty}`).join(", ");
  el.hintText.textContent = `Buy exactly: ${names}. Then pay exact amount.`;
}

function renderStats() {
  el.starsCount.textContent = String(state.stars);
  el.xpCount.textContent = String(state.xp);
  el.levelCount.textContent = String(state.levelIndex + 1);
  const xpMax = LEVEL_COUNT * XP_PER_LEVEL;
  el.xpFill.style.width = `${(state.xp / xpMax) * 100}%`;
  const walletText = `₹${state.wallet}`;
  el.walletBadge.textContent = walletText;
  el.walletAmount.textContent = walletText;
}

function renderTask() {
  const task = currentTask();
  const total = taskRequiredTotal(task);
  const lines = task.requiredItems.map((item) => `<div>${item.name} x${item.requiredQty}</div>`).join("");

  const imagesHTML = task.requiredItems.map((item) => `
    <img src="${item.image}" alt="${item.name}" style="width: 45%; max-height: clamp(50px, 8vh, 80px); object-fit: contain; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 2px;" onerror="this.src='${PRODUCT_FALLBACK}'" />
  `).join("");

  el.taskItem.innerHTML = `
    <div style="display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; margin-bottom: 6px;">
      ${imagesHTML}
    </div>
    <h3>Level Basket</h3>
    <div style="font-weight:700;color:#25355a;line-height:1.35;">${lines}</div>
    <p>₹${total}</p>
  `;

  updateHintText();
}

function renderLevels() {
  el.levelsList.innerHTML = state.tasks.map((task, index) => {
    const className = index === state.levelIndex ? "active" : "";
    const shortText = task.requiredItems.map((item) => `${item.name} x${item.requiredQty}`).join(" + ");
    return `<li class="${className}">Level ${index + 1} → Buy ${shortText}</li>`;
  }).join("");
}

function addToCart(productId) {
  if (state.isComplete) return;

  const item = PRODUCT_CATALOG.find((product) => product.id === productId);
  if (!item) return;

  const existing = state.cartItems.find((cartItem) => cartItem.id === item.id);
  if (existing) {
    existing.quantity += 1;
  } else {
    state.cartItems.push({ id: item.id, name: item.name, price: item.price, image: item.image, quantity: 1 });
  }

  renderCart();
  speak(`${item.name} added to cart`);
}

function removeFromCart(index) {
  if (index < 0 || index >= state.cartItems.length) return;
  state.cartItems.splice(index, 1);
  renderCart();
}

function renderStore() {
  el.storeGrid.innerHTML = PRODUCT_CATALOG.map((item) => {
    const disabled = state.isComplete ? "disabled" : "";
    return `
      <article class="product-card">
        <img src="${item.image}" alt="${item.name}" loading="lazy" decoding="async" onerror="this.src='${PRODUCT_FALLBACK}'" />
        <div class="product-body">
          <h3>${item.name}</h3>
          <p>₹${item.price}</p>
          <button class="add-cart-btn" type="button" data-product-id="${item.id}" ${disabled}>Add to Cart</button>
        </div>
      </article>
    `;
  }).join("");

  el.storeGrid.querySelectorAll("[data-product-id]").forEach((button) => {
    button.addEventListener("click", () => addToCart(button.dataset.productId || ""));
  });
}

function renderCart() {
  if (!state.cartItems.length) {
    el.cartArea.innerHTML = "<p style='color:#5d7097;'>No items in cart yet.</p>";
    el.cartTotal.textContent = "₹0";
    return;
  }

  el.cartArea.innerHTML = state.cartItems.map((item, index) => {
    const subtotal = item.price * item.quantity;
    return `
      <article class="cart-item">
        <img src="${item.image}" alt="${item.name}" loading="lazy" decoding="async" onerror="this.src='${PRODUCT_FALLBACK}'" />
        <div>
          <strong>${item.name}</strong>
          <p>Qty: ${item.quantity} | Price: ₹${item.price} | Subtotal: ₹${subtotal}</p>
        </div>
        <button class="remove-btn" type="button" data-remove-index="${index}">Remove</button>
      </article>
    `;
  }).join("");

  el.cartArea.querySelectorAll("[data-remove-index]").forEach((button) => {
    button.addEventListener("click", () => removeFromCart(Number(button.dataset.removeIndex)));
  });

  el.cartTotal.textContent = `₹${cartTotal()}`;
}

function renderPaidItems() {
  const total = paidTotal();
  el.paidAmount.textContent = `₹${total}`;

  if (!state.paidItems.length) {
    el.dropPlaceholder.style.display = "block";
    el.paymentItems.innerHTML = "";
    return;
  }

  el.dropPlaceholder.style.display = "none";
  el.paymentItems.innerHTML = state.paidItems.map((item, index) => `
    <button class="dropped-item" type="button" data-remove-index="${index}" aria-label="Remove ${item.label}">
      <img src="${item.image}" alt="${item.label}" loading="lazy" decoding="async" />
      <span>${item.label}</span>
    </button>
  `).join("");

  el.paymentItems.querySelectorAll("[data-remove-index]").forEach((button) => {
    button.addEventListener("click", () => {
      state.paidItems.splice(Number(button.dataset.removeIndex), 1);
      renderPaidItems();
    });
  });
}

function resetPayment() {
  state.paidItems = [];
  renderPaidItems();
}

function resetCartAndPayment() {
  state.cartItems = [];
  resetPayment();
  renderCart();
}

function renderReceipt(text = "Complete a payment to view receipt.") {
  el.receiptBody.innerHTML = `<p>${text}</p>`;
}

function renderReceiptFromCart(required, paid) {
  const lines = state.cartItems.map((item) => `<p>${item.name} x${item.quantity} - ₹${item.price * item.quantity}</p>`).join("");
  el.receiptBody.innerHTML = `
    <p><strong>Items:</strong></p>
    ${lines}
    <p><strong>Total:</strong> ₹${required}</p>
    <p><strong>Paid:</strong> ₹${paid}</p>
    <p><strong>Wallet Balance:</strong> ₹${state.wallet}</p>
  `;
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
      showErrorFeedback("Time is up. Level restarted.", "Time is up. Try this shopping task again.");
      state.timeLeft = START_TIME_SECONDS;
      resetCartAndPayment();
      renderTimer();
    }
  }, 1000);
}

function clearTimer() {
  if (!state.timerId) return;
  window.clearInterval(state.timerId);
  state.timerId = null;
}

function validateTaskRequirements(task) {
  return task.requiredItems.every((requiredItem) => {
    const cartItem = state.cartItems.find((item) => item.id === requiredItem.id);
    return cartItem && cartItem.quantity >= requiredItem.requiredQty;
  });
}

function advanceLevel() {
  if (state.levelIndex < LEVEL_COUNT - 1) {
    state.levelIndex += 1;
    state.timeLeft = START_TIME_SECONDS;
    resetCartAndPayment();
    renderStats();
    renderTask();
    renderLevels();
    renderTimer();
    const text = currentTask().requiredItems.map((item) => `${item.name} x${item.requiredQty}`).join(", ");
    speak(`Next level. Buy ${text}`);
    return;
  }

  state.isComplete = true;
  clearTimer();
  showToast("All shopping levels complete. Excellent work!", "success");
  speak("Excellent work. You completed all shopping levels.");
}

function validatePayment() {
  if (state.isComplete) return;
  if (!state.cartItems.length) {
    showErrorFeedback("Add items to cart first.", "Please add items to your cart first.");
    return;
  }

  const task = currentTask();
  if (!validateTaskRequirements(task)) {
    showErrorFeedback("Cart is missing required level items/quantities.", "Please add the required products and quantities.");
    return;
  }

  const required = cartTotal();
  const paid = paidTotal();

  if (paid !== required) {
    el.paymentDropZone.classList.add("shake");
    setTimeout(() => el.paymentDropZone.classList.remove("shake"), 350);
    showErrorFeedback(`Try again. Paid ₹${paid}, needed ₹${required}.`, "Try again. Use exact amount.");
    return;
  }

  if (state.wallet < paid) {
    showErrorFeedback("Not enough wallet balance.", "Not enough wallet balance.");
    return;
  }

  state.wallet -= paid;
  state.stars += STARS_PER_LEVEL;
  state.xp += XP_PER_LEVEL;

  renderStats();
  fireConfetti();
  showSuccessFeedback(
    `Payment successful. +${STARS_PER_LEVEL} Stars, +${XP_PER_LEVEL} XP`, 
    "Payment successful",
    () => {
      advanceLevel();
    }
  );
  renderReceiptFromCart(required, paid);
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

    card.addEventListener("dragend", () => card.classList.remove("dragging"));

    let lastClickTime = 0;
    card.addEventListener("click", () => {
      const now = Date.now();
      if (now - lastClickTime < 300) return;
      lastClickTime = now;
      if (state.isComplete) return;
      const item = MONEY_ITEMS.find((entry) => entry.id === card.dataset.moneyId);
      if (!item) return;
      state.paidItems.push(item);
      renderPaidItems();
    });
  });
}

function bindPaymentDropZone() {
  el.paymentDropZone.addEventListener("dragover", (event) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = "copy";
    el.paymentDropZone.classList.add("dragover");
  });

  el.paymentDropZone.addEventListener("dragleave", () => {
    el.paymentDropZone.classList.remove("dragover");
  });

  el.paymentDropZone.addEventListener("drop", (event) => {
    event.preventDefault();
    el.paymentDropZone.classList.remove("dragover");
    if (state.isComplete) return;

    const id = event.dataTransfer.getData("text/plain");
    const item = MONEY_ITEMS.find((entry) => entry.id === id);
    if (!item) return;
    state.paidItems.push(item);
    renderPaidItems();
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

  // Load speed and pitch from settings
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
  toastTimer = window.setTimeout(() => el.toast.classList.remove("show"), 2200);
}

function fireConfetti() {
  const canvas = el.confettiCanvas;
  const context = canvas.getContext("2d");
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;

  const bits = Array.from({ length: 100 }, () => ({
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
  if (el.prevPageBtn) {
    el.prevPageBtn.addEventListener("click", () => {
      if (state.storePage <= 1) return;
      state.storePage -= 1;
      renderStore();
    });
  }

  if (el.nextPageBtn) {
    el.nextPageBtn.addEventListener("click", () => {
      const totalPages = Math.max(1, Math.ceil(PRODUCT_CATALOG.length / PRODUCTS_PER_PAGE));
      if (state.storePage >= totalPages) return;
      state.storePage += 1;
      renderStore();
    });
  }

  el.payNowBtn.addEventListener("click", validatePayment);

  el.hintBtn.addEventListener("click", () => {
    const task = currentTask();
    const taskText = task.requiredItems.map((item) => `${item.name} x${item.requiredQty}`).join(", ");
    const hint = `Hint: buy ${taskText} and pay exact total.`;
    showToast(hint);
    speak(hint);
  });

  if (el.resetTopBtn) {
    el.resetTopBtn.addEventListener("click", () => window.location.reload());
  }

  el.listenBtn.addEventListener("click", () => {
    const taskText = currentTask().requiredItems.map((item) => `${item.name} x${item.requiredQty}`).join(", ");
    speak(`Buy ${taskText}`);
  });
}

function init() {
  renderStats();
  renderTask();
  renderLevels();
  renderStore();
  renderCart();
  renderPaidItems();
  renderTimer();
  renderReceipt();
  renderMoneyGrid();
  bindPaymentDropZone();
  bindControls();
  startTimer();

  const taskText = currentTask().requiredItems.map((item) => `${item.name} x${item.requiredQty}`).join(", ");
  speak(`Buy ${taskText}`);
}

window.addEventListener("beforeunload", clearTimer);
window.addEventListener("resize", () => {
  el.confettiCanvas.width = window.innerWidth;
  el.confettiCanvas.height = window.innerHeight;
});

init();
