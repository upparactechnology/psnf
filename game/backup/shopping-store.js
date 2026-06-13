// DATA
const PRODUCT_BLUEPRINTS = [
  { id: "saving-cream-sachet", name: "Saving Cream (sachet)", price: 10, image: "https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=640&q=80" },
  { id: "mobile-covers", name: "Mobile Covers", price: 90, image: "https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=640&q=80" },
  { id: "fork-spoon-single", name: "Fork / Spoon (single)", price: 110, image: "https://images.unsplash.com/photo-1514986888952-8cd320577b68?auto=format&fit=crop&w=640&q=80" },
  { id: "disposable-razor", name: "Disposable Razor", price: 150, image: "https://images.unsplash.com/photo-1621607512214-68297480165e?auto=format&fit=crop&w=640&q=80" },
  { id: "wallet", name: "Wallet", price: 500, image: "https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=640&q=80" },
  { id: "perfume-regular", name: "Perfume (regular)", price: 510, image: "https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&w=640&q=80" },
  { id: "game-kits-small-toys", name: "Game Kits / Small Toys", price: 620, image: "https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?auto=format&fit=crop&w=640&q=80" },
  { id: "small-bouquet", name: "Small Bouquet", price: 650, image: "https://images.unsplash.com/photo-1487530811176-3780de880c2d?auto=format&fit=crop&w=640&q=80" },
  { id: "bracelet-simple", name: "Bracelet (simple)", price: 1110, image: "https://images.unsplash.com/photo-1611591437281-460bfbe1220a?auto=format&fit=crop&w=640&q=80" },
  { id: "watch", name: "Watch", price: 2350, image: "https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=640&q=80" },
  { id: "amul-milk-shakti-1", name: "Amul Milk (Shakti-1)", price: 32, image: "https://images.unsplash.com/photo-1563636619-e9143da7973b?auto=format&fit=crop&w=640&q=80" },
  { id: "pencil-box-set-12", name: "Pencil Box (set of 12)", price: 110, image: "https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=640&q=80" },
  { id: "spoon-set-12", name: "Spoon (set of 12)", price: 200, image: "https://images.unsplash.com/photo-1514986888952-8cd320577b68?auto=format&fit=crop&w=640&q=80" },
  { id: "kurta-basic", name: "Kurta (Basic)", price: 350, image: "https://images.unsplash.com/photo-1593032457862-e1f4f0e2f91f?auto=format&fit=crop&w=640&q=80" },
  { id: "basic-headphones", name: "Basic Headphones", price: 500, image: "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=640&q=80" },
  { id: "dustbin", name: "Dustbin", price: 650, image: "https://images.unsplash.com/photo-1528323273322-d81458248d40?auto=format&fit=crop&w=640&q=80" },
  { id: "goggles", name: "Goggles", price: 720, image: "https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=640&q=80" },
  { id: "shirt-basic", name: "Shirt (Basic)", price: 750, image: "https://images.unsplash.com/photo-1596755094514-f87e34085b2c?auto=format&fit=crop&w=640&q=80" },
  { id: "iron-press", name: "Iron (press)", price: 800, image: "https://images.unsplash.com/photo-1594555153990-57e7f0babc0b?auto=format&fit=crop&w=640&q=80" },
  { id: "silver-earring", name: "Silver Earring", price: 2300, image: "https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=640&q=80" },
  { id: "cricket-ball", name: "Cricket Ball", price: 70, image: "https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?auto=format&fit=crop&w=640&q=80" },
  { id: "carom-coins-basic", name: "Carom Coins (basic set)", price: 80, image: "https://images.unsplash.com/photo-1610890716171-6b1bb98ffd09?auto=format&fit=crop&w=640&q=80" },
  { id: "table-tennis-ball", name: "Table Tennis Ball", price: 90, image: "https://images.unsplash.com/photo-1592903297149-37fb25202dfa?auto=format&fit=crop&w=640&q=80" },
  { id: "swimming-cap", name: "Swimming Cap", price: 95, image: "https://images.unsplash.com/photo-1592656094267-764a45160876?auto=format&fit=crop&w=640&q=80" },
  { id: "table-tennis-racket", name: "Table Tennis Racket", price: 250, image: "https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&w=640&q=80" },
  { id: "swimming-goggles", name: "Swimming Goggles", price: 260, image: "https://images.unsplash.com/photo-1517959105821-eaf2591984f5?auto=format&fit=crop&w=640&q=80" },
  { id: "skipping-rope", name: "Skipping Rope", price: 330, image: "https://images.unsplash.com/photo-1599058917765-a780eda07a3e?auto=format&fit=crop&w=640&q=80" },
  { id: "frisbee", name: "Frisbee", price: 550, image: "https://images.unsplash.com/photo-1526506118085-60ce8714f8c5?auto=format&fit=crop&w=640&q=80" },
  { id: "badminton-racket", name: "Badminton Racket", price: 700, image: "https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=640&q=80" },
  { id: "volleyball", name: "Volleyball", price: 980, image: "https://images.unsplash.com/photo-1612872087720-bb876e2e67d1?auto=format&fit=crop&w=640&q=80" },
  { id: "coffee-sachet", name: "Coffee (sachet)", price: 10, image: "https://images.unsplash.com/photo-1442512595331-e89e73853f31?auto=format&fit=crop&w=640&q=80" },
  { id: "french-fries", name: "French Fries", price: 115, image: "https://images.unsplash.com/photo-1576107232684-1279f390859f?auto=format&fit=crop&w=640&q=80" },
  { id: "noodles", name: "Noodles", price: 180, image: "https://images.unsplash.com/photo-1612929633738-8fe44f7ec841?auto=format&fit=crop&w=640&q=80" },
  { id: "wagh-bakri-tea-packet", name: "Wagh Bakri Tea Packet", price: 350, image: "https://images.unsplash.com/photo-1523920290228-4f321a939b4c?auto=format&fit=crop&w=640&q=80" },
  { id: "t-shirt-basic", name: "T-Shirt (Basic)", price: 400, image: "https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=640&q=80" },
  { id: "pant-shirt-basic", name: "Pant Shirt (Basic)", price: 950, image: "https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=640&q=80" },
  { id: "carom-board", name: "Carom Board", price: 1000, image: "https://images.unsplash.com/photo-1610890716171-6b1bb98ffd09?auto=format&fit=crop&w=640&q=80" },
  { id: "swimming-costume", name: "Swimming Costume", price: 1100, image: "https://images.unsplash.com/photo-1560090995-01632a28895b?auto=format&fit=crop&w=640&q=80" },
  { id: "cricket-bat-branded", name: "Cricket Bat Branded", price: 1700, image: "https://images.unsplash.com/photo-1624880357913-a8539238245b?auto=format&fit=crop&w=640&q=80" },
  { id: "cricket-gloves", name: "Cricket Gloves", price: 2000, image: "https://images.unsplash.com/photo-1549060279-7e168fcee0c2?auto=format&fit=crop&w=640&q=80" },
  { id: "paneer-basic", name: "Paneer (Basic)", price: 40, image: "https://images.unsplash.com/photo-1631452180519-c014fe946bc7?auto=format&fit=crop&w=640&q=80" },
  { id: "ketchup-basic", name: "Ketchup (Basic)", price: 60, image: "https://images.unsplash.com/photo-1609151142831-66b34b2f2f7f?auto=format&fit=crop&w=640&q=80" },
  { id: "butter-basic", name: "Butter (Basic)", price: 75, image: "https://images.unsplash.com/photo-1589985270826-4b7bb135bc9d?auto=format&fit=crop&w=640&q=80" },
  { id: "soap", name: "Soap", price: 85, image: "https://images.unsplash.com/photo-1584305574647-acf8069a2afb?auto=format&fit=crop&w=640&q=80" },
  { id: "hair-oil", name: "Hair Oil", price: 115, image: "https://images.unsplash.com/photo-1631730359585-38a493f6ef43?auto=format&fit=crop&w=640&q=80" },
  { id: "cheese", name: "Cheese", price: 130, image: "https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?auto=format&fit=crop&w=640&q=80" },
  { id: "deodorant", name: "Deodorant", price: 200, image: "https://images.unsplash.com/photo-1619451334792-150fd785ee74?auto=format&fit=crop&w=640&q=80" },
  { id: "ghee", name: "Ghee", price: 250, image: "https://images.unsplash.com/photo-1604908812315-99f2d5f3c370?auto=format&fit=crop&w=640&q=80" },
  { id: "shampoo", name: "Shampoo", price: 270, image: "https://images.unsplash.com/photo-1526947425960-945c6e72858f?auto=format&fit=crop&w=640&q=80" },
  { id: "oil", name: "Oil", price: 2200, image: "https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=640&q=80" }
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
const START_WALLET = 8000;
const START_TIME_SECONDS = 120;
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
  timerDisplay: document.getElementById("timerDisplay"),
  hintText: document.getElementById("hintText"),
  taskItem: document.getElementById("taskItem"),
  storeGrid: document.getElementById("storeGrid"),
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
  confettiCanvas: document.getElementById("confettiCanvas")
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
  const primary = task.requiredItems[0];
  const total = taskRequiredTotal(task);
  const lines = task.requiredItems.map((item) => `<div>${item.name} x${item.requiredQty}</div>`).join("");

  el.taskItem.innerHTML = `
    <img src="${primary.image}" alt="${primary.name}" onerror="this.src='${PRODUCT_FALLBACK}'" />
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
        <img src="${item.image}" alt="${item.name}" onerror="this.src='${PRODUCT_FALLBACK}'" />
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
        <img src="${item.image}" alt="${item.name}" onerror="this.src='${PRODUCT_FALLBACK}'" />
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
      <img src="${item.image}" alt="${item.label}" />
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
      showToast("Time is up. Level restarted.", "error");
      playTone("error");
      speak("Time is up. Try this shopping task again.");
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
    showToast("Add items to cart first.", "error");
    speak("Please add items to your cart first.");
    return;
  }

  const task = currentTask();
  if (!validateTaskRequirements(task)) {
    showToast("Cart is missing required level items/quantities.", "error");
    speak("Please add the required products and quantities.");
    return;
  }

  const required = cartTotal();
  const paid = paidTotal();

  if (paid !== required) {
    el.paymentDropZone.classList.add("shake");
    setTimeout(() => el.paymentDropZone.classList.remove("shake"), 350);
    playTone("error");
    showToast(`Try again. Paid ₹${paid}, needed ₹${required}.`, "error");
    speak("Try again. Use exact amount.");
    return;
  }

  if (state.wallet < paid) {
    showToast("Not enough wallet balance.", "error");
    speak("Not enough wallet balance.");
    return;
  }

  state.wallet -= paid;
  state.stars += STARS_PER_LEVEL;
  state.xp += XP_PER_LEVEL;

  renderStats();
  fireConfetti();
  playTone("success");
  showToast(`Payment successful. +${STARS_PER_LEVEL} Stars, +${XP_PER_LEVEL} XP`, "success");
  speak("Payment successful");
  renderReceiptFromCart(required, paid);

  advanceLevel();
}

// DRAG DROP
function renderMoneyGrid() {
  el.moneyGrid.innerHTML = MONEY_ITEMS.map((item) => `
    <article class="money-card" draggable="true" data-money-id="${item.id}" aria-label="Drag ${item.label}">
      <img src="${item.image}" alt="${item.label}" />
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

    card.addEventListener("click", () => {
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

  window.speechSynthesis.cancel();
  const utterance = new SpeechSynthesisUtterance(text);
  const voices = window.speechSynthesis.getVoices();
  const preferredVoice = voices.find((voice) =>
    voice.name.includes("Google") || voice.name.includes("Microsoft") || voice.lang.startsWith("en")
  );

  if (preferredVoice) utterance.voice = preferredVoice;
  utterance.rate = 0.9;
  utterance.pitch = 1;
  utterance.volume = 1;
  utterance.lang = "en-IN";
  window.speechSynthesis.speak(utterance);
}

function playTone(kind) {
  const audioContext = new (window.AudioContext || window.webkitAudioContext)();
  const oscillator = audioContext.createOscillator();
  const gainNode = audioContext.createGain();

  oscillator.connect(gainNode);
  gainNode.connect(audioContext.destination);
  oscillator.frequency.value = kind === "success" ? 740 : 220;
  oscillator.type = kind === "success" ? "triangle" : "sawtooth";

  gainNode.gain.setValueAtTime(0.001, audioContext.currentTime);
  gainNode.gain.exponentialRampToValueAtTime(0.2, audioContext.currentTime + 0.02);
  gainNode.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.25);

  oscillator.start();
  oscillator.stop(audioContext.currentTime + 0.26);
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
  el.payNowBtn.addEventListener("click", validatePayment);

  el.hintBtn.addEventListener("click", () => {
    const task = currentTask();
    const taskText = task.requiredItems.map((item) => `${item.name} x${item.requiredQty}`).join(", ");
    const hint = `Hint: buy ${taskText} and pay exact total.`;
    showToast(hint);
    speak(hint);
  });

  el.resetBtn.addEventListener("click", () => {
    resetCartAndPayment();
    showToast("Cart and payment reset.");
  });

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
