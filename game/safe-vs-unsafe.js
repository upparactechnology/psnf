// DATA
const RELATIONSHIP_PEOPLE = [
  {
    id: "mother",
    name: "Mother",
    zone: "safe",
    hint: "A mother cares for you.",
    image:
      "images/safeunsafe/mother.png",
  },
  {
    id: "father",
    name: "Father",
    zone: "safe",
    hint: "A father is a trusted family member.",
    image:
      "images/safeunsafe/father.png",
  },
  {
    id: "brother",
    name: "Brother",
    zone: "safe",
    hint: "A brother is part of your family.",
    image:
      "images/safeunsafe/brother.png",
  },
  {
    id: "sister",
    name: "Sister",
    zone: "safe",
    hint: "A sister can help and support you.",
    image:
      "images/safeunsafe/sister.png",
  },
  {
    id: "teacher",
    name: "Teacher",
    zone: "safe",
    hint: "A teacher helps children at school.",
    image:
      "images/safeunsafe/teacher.png",
  },
  {
    id: "doctor",
    name: "Doctor",
    zone: "safe",
    hint: "A doctor helps when someone is sick.",
    image:
      "images/safeunsafe/doctor.png",
  },
  {
    id: "therapist",
    name: "Therapist",
    zone: "safe",
    hint: "A therapist helps children learn and grow.",
    image:
      "images/safeunsafe/therapist.png",
  },
  {
    id: "police",
    name: "Police",
    zone: "safe",
    hint: "Police officers help keep people safe.",
    image:
      "images/safeunsafe/police.png",
  },
  {
    id: "neighbor-friend",
    name: "Friendly Neighbour",
    zone: "careful",
    hint: "A neighbour is familiar, but still be careful.",
    image:
      "images/safeunsafe/neighbor.png",
  },
  {
    id: "shopkeeper",
    name: "Shopkeeper",
    zone: "careful",
    hint: "A shopkeeper can help, but ask a trusted adult if unsure.",
    image:
      "images/safeunsafe/shopkeeper.png",
  },
  {
    id: "bus-driver",
    name: "Bus Driver",
    zone: "careful",
    hint: "A bus driver is familiar, but check with trusted adults.",
    image:
      "images/safeunsafe/bus-driver.png",
  },
  {
    id: "security-guard",
    name: "Security Guard",
    zone: "careful",
    hint: "A security guard helps keep places safe.",
    image:
      "images/safeunsafe/security-guard.png",
  },
  {
    id: "stranger",
    name: "Stranger",
    zone: "unsafe",
    hint: "A stranger is someone you do not know.",
    image:
      "images/safeunsafe/stranger.png",
  },
  {
    id: "unknown-adult",
    name: "Unknown Adult",
    zone: "unsafe",
    hint: "Do not go with people you do not know.",
    image:
      "images/safeunsafe/unknown-adult.png",
  },
  {
    id: "candy-stranger",
    name: "Candy Stranger",
    zone: "unsafe",
    hint: "Never go with someone offering gifts.",
    image:
      "images/safeunsafe/candy-stranger.png",
  },
  {
    id: "angry-stranger",
    name: "Angry Stranger",
    zone: "unsafe",
    hint: "Stay away from unsafe or angry strangers.",
    image:
      "images/safeunsafe/angry-stranger.png",
  },
  {
    id: "person-asking",
    name: "Person Asking to Go",
    zone: "unsafe",
    hint: "Do not go with someone who asks you to leave.",
    image:
      "images/safeunsafe/person-asking.png",
  },
  {
    id: "grandmother",
    name: "Grandmother",
    zone: "safe",
    hint: "A grandmother is a trusted family member.",
    image: "images/safeunsafe/grandmother.png",
  },
  {
    id: "grandfather",
    name: "Grandfather",
    zone: "safe",
    hint: "A grandfather helps and protects family.",
    image: "images/safeunsafe/grandfather.png",
  },
  {
    id: "aunt",
    name: "Aunt",
    zone: "safe",
    hint: "An aunt can be a trusted adult.",
    image: "images/safeunsafe/aunt.png",
  },
  {
    id: "uncle",
    name: "Uncle",
    zone: "safe",
    hint: "An uncle may be a trusted helper.",
    image: "images/safeunsafe/uncle.png",
  },
  {
    id: "principal",
    name: "School Principal",
    zone: "safe",
    hint: "The principal helps keep students safe.",
    image: "images/safeunsafe/principal.png",
  },
  {
    id: "nurse",
    name: "Nurse",
    zone: "safe",
    hint: "Nurses help people stay healthy.",
    image: "images/safeunsafe/nurse.png",
  },
  {
    id: "firefighter",
    name: "Firefighter",
    zone: "safe",
    hint: "Firefighters help during emergencies.",
    image: "images/safeunsafe/firefighter.png",
  },
  {
    id: "school-counsellor",
    name: "School Counsellor",
    zone: "safe",
    hint: "A counsellor helps children solve problems.",
    image: "images/safeunsafe/school-counsellor.png",
  },

  {
    id: "coach",
    name: "Sports Coach",
    zone: "careful",
    hint: "A coach can help but follow safety rules.",
    image: "images/safeunsafe/coach.png",
  },
  {
    id: "librarian",
    name: "Librarian",
    zone: "careful",
    hint: "A librarian helps with books.",
    image: "images/safeunsafe/librarian.png",
  },
  {
    id: "delivery-person",
    name: "Delivery Person",
    zone: "careful",
    hint: "Be polite but stay careful.",
    image: "images/safeunsafe/delivery-person.png",
  },
  {
    id: "taxi-driver",
    name: "Taxi Driver",
    zone: "careful",
    hint: "Stay with trusted adults.",
    image: "images/safeunsafe/taxi-driver.png",
  },
  {
    id: "receptionist",
    name: "Receptionist",
    zone: "careful",
    hint: "A receptionist helps visitors.",
    image: "images/safeunsafe/receptionist.png",
  },
  {
    id: "janitor",
    name: "School Janitor",
    zone: "careful",
    hint: "A familiar helper at school.",
    image: "images/safeunsafe/janitor.png",
  },

  {
    id: "online-stranger",
    name: "Online Stranger",
    zone: "unsafe",
    hint: "Never share information online.",
    image: "images/safeunsafe/online-stranger.png",
  },
  {
    id: "gift-offerer",
    name: "Person Offering Gifts",
    zone: "unsafe",
    hint: "Do not accept gifts from strangers.",
    image: "images/safeunsafe/gift-offerer.png",
  },
  {
    id: "secret-keeper",
    name: "Person Asking for Secrets",
    zone: "unsafe",
    hint: "Tell trusted adults about unsafe secrets.",
    image: "images/safeunsafe/secret-keeper.png",
  },
  {
    id: "fake-police",
    name: "Pretending Police Officer",
    zone: "unsafe",
    hint: "Always verify with trusted adults.",
    image: "images/safeunsafe/fake-police.png",
  },
  {
    id: "internet-friend",
    name: "Unknown Internet Friend",
    zone: "unsafe",
    hint: "Online friends may not be real.",
    image: "images/safeunsafe/internet-frined.png",
  },
];

const SAFETY_SITUATIONS = [
  {
    id: "s1",
    text: "A stranger offers chocolate.",
    answer: "unsafe",
    hint: "Do not accept gifts from strangers.",
    explanation: "A stranger offering sweets is not safe.",
  },
  {
    id: "s2",
    text: "Teacher asks you to sit in class.",
    answer: "safe",
    hint: "Teachers help children learn.",
    explanation: "A teacher is trusted at school.",
  },
  {
    id: "s3",
    text: "Unknown person says come with me.",
    answer: "unsafe",
    hint: "Never go with someone you do not know.",
    explanation: "Unknown adults are not safe.",
  },
  {
    id: "s4",
    text: "Shopkeeper gives your change.",
    answer: "careful",
    hint: "Ask a trusted adult if you feel unsure.",
    explanation: "A shopkeeper is familiar, but be careful.",
  },
  {
    id: "s5",
    text: "Neighbour asks your phone number.",
    answer: "careful",
    hint: "Share personal details only with trusted adults.",
    explanation: "A neighbour is familiar, but stay careful.",
  },
  {
    id: "s6",
    text: "Bus driver drops you at school.",
    answer: "careful",
    hint: "Familiar people can help, but stay alert.",
    explanation:
      "A bus driver is familiar and should be trusted with guidance.",
  },
  {
    id: "s7",
    text: "Someone asks you to keep a secret.",
    answer: "unsafe",
    hint: "Tell a trusted adult if someone asks for secrets.",
    explanation: "Unsafe people may ask for secrets.",
  },
  {
    id: "s8",
    text: "Angry stranger shouts at you.",
    answer: "unsafe",
    hint: "Move away and get help.",
    explanation: "An angry stranger is not safe.",
  },
  {
    id: "s9",
    text: "Teacher helps you with homework.",
    answer: "safe",
    hint: "Teachers are there to help you.",
    explanation: "A teacher helping you is safe.",
  },
  {
    id: "s10",
    text: "Stranger offers a gift.",
    answer: "unsafe",
    hint: "Never accept gifts from strangers.",
    explanation: "A stranger offering a gift is unsafe.",
  },
  {
    id: "s11",
    text: "A friend asks you to hide something from parents.",
    answer: "unsafe",
    hint: "Tell a trusted adult about secrets that worry you.",
    explanation: "Keeping worrying secrets is unsafe; tell a trusted adult.",
  },
  {
    id: "s12",
    text: "A neighbour helps find your lost toy.",
    answer: "careful",
    hint: "Ask a trusted adult if unsure before going with someone.",
    explanation: "A neighbour is familiar, but check with a trusted adult.",
  },
  {
    id: "s13",
    text: "A teacher asks you to line up for school assembly.",
    answer: "safe",
    hint: "Teachers help organize and keep children safe.",
    explanation: "A teacher giving instructions at school is safe.",
  },
  {
    id: "s14",
    text: "A doctor checks your temperature at the clinic.",
    answer: "safe",
    hint: "Doctors help keep you healthy.",
    explanation: "A doctor helping you at a clinic is safe.",
  },
  {
    id: "s15",
    text: "A bus driver tells you to wait for your turn.",
    answer: "careful",
    hint: "Follow instructions and stay with trusted adults.",
    explanation: "A bus driver is familiar, but still be careful.",
  },
  {
    id: "s16",
    text: "Someone you do not know asks for your address.",
    answer: "unsafe",
    hint: "Keep personal information private.",
    explanation: "Sharing private details with strangers is not safe.",
  },
  {
    id: "s17",
    text: "A shopkeeper returns your change correctly.",
    answer: "careful",
    hint: "Familiar helpers can be okay, but be alert.",
    explanation: "A shopkeeper is familiar, so be careful.",
  },
  {
    id: "s18",
    text: "A police officer helps you find your parent.",
    answer: "safe",
    hint: "Police officers are trusted helpers.",
    explanation: "A police officer helping you is safe.",
  },
  {
    id: "s19",
    text: "A stranger asks where you live.",
    answer: "unsafe",
    hint: "Keep personal information private.",
    explanation: "Do not share your address.",
  },
  {
    id: "s20",
    text: "A police officer helps you find your parents.",
    answer: "safe",
    hint: "Police officers help keep people safe.",
    explanation: "A police officer helping you is safe.",
  },
  {
    id: "s21",
    text: "Someone online asks for your photo.",
    answer: "unsafe",
    hint: "Never share photos with strangers online.",
    explanation: "Online strangers should not get personal photos.",
  },
  {
    id: "s22",
    text: "Your teacher helps you after class.",
    answer: "safe",
    hint: "Teachers help students.",
    explanation: "A trusted teacher helping you is safe.",
  },
  {
    id: "s23",
    text: "A stranger asks you to get into a car.",
    answer: "unsafe",
    hint: "Never get into a stranger's car.",
    explanation: "Move away and find help.",
  },
  {
    id: "s24",
    text: "A librarian helps you find a book.",
    answer: "careful",
    hint: "Helpful adults can assist you.",
    explanation: "A librarian is a familiar helper.",
  },
  {
    id: "s25",
    text: "A neighbour wants to take you somewhere without telling parents.",
    answer: "unsafe",
    hint: "Always tell trusted adults.",
    explanation: "Never go without permission.",
  },
  {
    id: "s26",
    text: "A doctor checks your ears during an appointment.",
    answer: "safe",
    hint: "Doctors help keep you healthy.",
    explanation: "A doctor helping you is safe.",
  },
  {
    id: "s27",
    text: "Someone online asks for your password.",
    answer: "unsafe",
    hint: "Passwords are private.",
    explanation: "Never share passwords.",
  },
  {
    id: "s28",
    text: "A bus driver tells everyone to stay seated.",
    answer: "careful",
    hint: "Follow safety instructions.",
    explanation: "The driver is helping keep everyone safe.",
  },
  {
    id: "s29",
    text: "A firefighter helps during an emergency.",
    answer: "safe",
    hint: "Firefighters are trusted helpers.",
    explanation: "A firefighter helping you is safe.",
  },
  {
    id: "s30",
    text: "A stranger says your parents sent them.",
    answer: "unsafe",
    hint: "Check with trusted adults first.",
    explanation: "Never leave with unknown people.",
  },
];

const SAFE_PEOPLE_POOL = [
  {
    id: "mother",
    name: "Mother",
    safe: true,
    hint: "A mother cares for you.",
    image:
      "images/safeunsafe/mother.png",
  },
  {
    id: "father",
    name: "Father",
    safe: true,
    hint: "A father is a trusted adult.",
    image:
      "images/safeunsafe/father.png",
  },
  {
    id: "teacher",
    name: "Teacher",
    safe: true,
    hint: "A teacher helps children at school.",
    image:
      "images/safeunsafe/teacher.png",
  },
  {
    id: "doctor",
    name: "Doctor",
    safe: true,
    hint: "A doctor helps when someone is sick.",
    image:
      "images/safeunsafe/doctor.png",
  },
  {
    id: "police",
    name: "Police",
    safe: true,
    hint: "Police officers help keep people safe.",
    image:
      "images/safeunsafe/police.png",
  },
  {
    id: "therapist",
    name: "Therapist",
    safe: true,
    hint: "A therapist helps children learn and grow.",
    image:
      "images/safeunsafe/therapist.png",
  },
  {
    id: "security-guard",
    name: "Security Guard",
    safe: true,
    hint: "A security guard helps keep places safe.",
    image:
      "images/safeunsafe/security-guard.png",
  },
  {
    id: "bus-driver",
    name: "Bus Driver",
    safe: true,
    hint: "A bus driver is a trusted helper.",
    image:
      "images/safeunsafe/bus-driver.png",
  },
  {
    id: "friend",
    name: "Friend",
    safe: true,
    hint: "A trusted friend can be safe.",
    image:
      "images/safeunsafe/friend.png",
  },
  {
    id: "grandparent",
    name: "Grandparent",
    safe: true,
    hint: "Grandparents are trusted family members.",
    image:
      "images/safeunsafe/grandparent.png",
  },
  {
    id: "aunt",
    name: "Aunt",
    safe: true,
    hint: "An aunt can be a trusted family helper.",
    image:
      "images/safeunsafe/aunt.png",
  },
  {
    id: "uncle",
    name: "Uncle",
    safe: true,
    hint: "An uncle can be a trusted family helper.",
    image:
      "images/safeunsafe/uncle.png",
  },
  {
    id: "stranger",
    name: "Stranger",
    safe: false,
    hint: "A stranger is not a safe person.",
    image:
      "images/safeunsafe/stranger.png",
  },
  {
    id: "unknown-adult",
    name: "Unknown Adult",
    safe: false,
    hint: "Do not go with people you do not know.",
    image:
      "images/safeunsafe/unknown-adult.png",
  },
  {
    id: "candy-stranger",
    name: "Candy Stranger",
    safe: false,
    hint: "Never go with someone offering gifts.",
    image:
      "images/safeunsafe/candy-stranger.png",
  },
  {
    id: "angry-stranger",
    name: "Angry Stranger",
    safe: false,
    hint: "Stay away from unsafe or angry strangers.",
    image:
      "images/safeunsafe/angry-stranger.png",
  },
  {
    id: "person-asking",
    name: "Person Asking to Go",
    safe: false,
    hint: "Do not go with someone who asks you to leave.",
    image:
      "images/safeunsafe/person-asking.png",
  },
  {
    id: "unfamiliar-neighbour",
    name: "Unfamiliar Neighbour",
    safe: false,
    hint: "Even familiar-looking people can be unsafe if you do not know them well.",
    image:
      "images/safeunsafe/unfamiliar neighbour.png",
  },
  {
    id: "grandmother",
    name: "Grandmother",
    safe: true,
    hint: "A grandmother is a trusted family member.",
    image: "images/safeunsafe/grandmother.png",
  },
  {
    id: "grandfather",
    name: "Grandfather",
    safe: true,
    hint: "A grandfather is a trusted family member.",
    image: "images/safeunsafe/grandfather.png",
  },
  {
    id: "principal",
    name: "School Principal",
    safe: true,
    hint: "The principal helps keep students safe.",
    image: "images/safeunsafe/principal.png",
  },
  {
    id: "nurse",
    name: "Nurse",
    safe: true,
    hint: "A nurse helps people stay healthy.",
    image: "images/safeunsafe/nurse.png",
  },
  {
    id: "firefighter",
    name: "Firefighter",
    safe: true,
    hint: "Firefighters help in emergencies.",
    image: "images/safeunsafe/firefighter.png",
  },
  {
    id: "school-counsellor",
    name: "School Counsellor",
    safe: true,
    hint: "A counsellor helps children.",
    image: "images/safeunsafe/school-counsellor.png",
  },

  {
    id: "online-stranger",
    name: "Online Stranger",
    safe: false,
    hint: "Do not trust strangers online.",
    image: "images/safeunsafe/online-stranger.png",
  },
  {
    id: "gift-offerer",
    name: "Person Offering Gifts",
    safe: false,
    hint: "Do not accept gifts from strangers.",
    image: "images/safeunsafe/gift-offerer.png",
  },
  {
    id: "secret-keeper",
    name: "Person Asking for Secrets",
    safe: false,
    hint: "Tell trusted adults about unsafe secrets.",
    image: "images/safeunsafe/secret-keeper.png",
  },
  {
    id: "fake-helper",
    name: "Fake Helper",
    safe: false,
    hint: "Not everyone who offers help is safe.",
    image: "images/safeunsafe/fake-helper.png",
  },
  {
    id: "internet-friend",
    name: "Unknown Internet Friend",
    safe: false,
    hint: "People online may not be who they say they are.",
    image: "images/safeunsafe/internet-frined.png",
  },
];

const SAFE_MODES = {
  relationship: {
    label: "Relationship Sorting",
    task: "Drag each person to correct zone",
    hint: "Trusted people help you, familiar people need care, and strangers are unsafe.",
    guide: `
      <strong>Trusted / Safe:</strong> People who care for you and help you.<br />
      <strong>Familiar / Be Careful:</strong> People you know but must be careful.<br />
      <strong>Stranger / Unsafe:</strong> People you do not know or unsafe people.
    `,
  },
  situations: {
    label: "Safety Situations",
    task: "Choose the safest answer",
    hint: "Listen carefully to the situation and choose the safest option.",
    guide: `
      <strong>Safe:</strong> Helpful and trusted situations.<br />
      <strong>Careful:</strong> Ask a trusted adult if unsure.<br />
      <strong>Unsafe:</strong> Move away and tell a trusted adult.
    `,
  },
  safePeople: {
    label: "My Safe People",
    task: "Select your trusted people",
    hint: "Choose the people who help, care, and keep you safe.",
    guide: `
      <strong>Trusted people:</strong> Family, teachers, doctors, and helpers.<br />
      <strong>Unsafe people:</strong> People you do not know.<br />
      <strong>Tip:</strong> Ask a trusted adult if you are unsure.
    `,
  },
};

const TIME_PER_MODE = 120;
const XP_PER_CORRECT = 50;
const STARS_PER_CORRECT = 10;
const MAX_XP = 500;
const FALLBACK_IMAGE = "images/money/main.png";

// UI
const el = {
  starsCount: document.getElementById("starsCount"),
  xpCount: document.getElementById("xpCount"),
  xpFill: document.getElementById("xpFill"),
  levelCount: document.getElementById("levelCount"),
  listenBtn: document.getElementById("listenBtn"),
  taskText: document.getElementById("taskText"),
  timerText: document.getElementById("timerText"),
  hintText: document.getElementById("hintText"),
  progressText: document.getElementById("progressText"),
  guideText: document.getElementById("guideText"),
  peopleGrid: document.getElementById("peopleGrid"),
  safeZone: document.getElementById("safeZone"),
  carefulZone: document.getElementById("carefulZone"),
  unsafeZone: document.getElementById("unsafeZone"),
  safeZoneItems: document.getElementById("safeZoneItems"),
  carefulZoneItems: document.getElementById("carefulZoneItems"),
  unsafeZoneItems: document.getElementById("unsafeZoneItems"),
  safePeopleGrid: document.getElementById("safePeopleGrid"),
  safeCircleItems: document.getElementById("safeCircleItems"),
  safePeopleList: document.getElementById("safePeopleList"),
  resetTopBtn: document.getElementById("resetTopBtn"),
  audioSubtitle: document.getElementById("audioSubtitle"),
  situationText: document.getElementById("situationText"),
  situationSubText: document.getElementById("situationSubText"),
  situationGuide: document.getElementById("situationGuide"),
  modeTabs: document.querySelectorAll(".mode-tab"),
  modeViews: {
    relationship: document.getElementById("relationshipView"),
    situations: document.getElementById("situationsView"),
    safePeople: document.getElementById("safePeopleView"),
  },
  checkBtn: document.getElementById("checkBtn"),
  hintBtn: document.getElementById("hintBtn"),
  resetBtn: document.getElementById("resetBtn"),
  toast: document.getElementById("toast"),
  confettiCanvas: document.getElementById("confettiCanvas"),
  appShell: document.querySelector(".app-shell"),
};

// GAME STATE
const state = {
  mode: "relationship",
  stars: 0,
  xp: 0,
  level: 1,
  timeLeft: TIME_PER_MODE,
  timerId: null,
  completedRounds: 0,
  review: {
    active: false,
    mode: null,
    timerId: null,
    pending: false,
  },
  relationship: {
    cards: [],
    assignments: {},
    checked: {},
    selectedCardId: null,
    completed: false,
  },
  situations: {
    questions: [],
    index: 0,
    selectedAnswer: null,
    locked: false,
  },
  safePeople: {
    cards: [],
    selectedIds: [],
    locked: false,
  },
};

// HELPERS
function normalizeMode(mode) {
  return mode === "safe-people" ? "safePeople" : mode;
}

function shuffle(list) {
  const copy = [...list];
  for (let i = copy.length - 1; i > 0; i -= 1) {
    const j = Math.floor(Math.random() * (i + 1));
    [copy[i], copy[j]] = [copy[j], copy[i]];
  }
  return copy;
}

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

  // Load speed and pitch from voice settings
  const settings = window.getVoiceSettings ? window.getVoiceSettings() : { rate: 0.9, pitch: 1.0 };
  utterance.rate = settings.rate;
  utterance.pitch = settings.pitch;
  utterance.volume = 1;
  utterance.lang = "en-IN";
  window.speechSynthesis.speak(utterance);
}

function speakPromise(text) {
  return new Promise((resolve) => {
    if (!window.speechSynthesis || !text) return resolve();
    if (el.audioSubtitle) {
      el.audioSubtitle.textContent = `Audio: ${text}`;
    }

    // Use global speech normalizer
    const spokenText = window.normalizeSpeechText ? window.normalizeSpeechText(text) : text;
    const u = new SpeechSynthesisUtterance(spokenText);

    // Select the best voice using our global helper
    const voices = window.speechSynthesis.getVoices();
    const selectedVoice = window.getBestVoice ? window.getBestVoice(voices) : voices.find(v => v.lang.startsWith("en"));
    if (selectedVoice) {
      u.voice = selectedVoice;
    }

    // Load speed and pitch from settings
    const settings = window.getVoiceSettings ? window.getVoiceSettings() : { rate: 0.92, pitch: 1.0 };
    u.rate = settings.rate;
    u.pitch = settings.pitch;
    u.volume = 1;
    u.lang = "en-IN";
    u.onend = () => resolve();
    u.onerror = () => resolve();
    window.speechSynthesis.speak(u);
  });
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

function showSuccessFeedback(message, voiceText, speakFlag = true) {
  flashFeedback("success");
  playFeedback("success");
  showToast(message, "success");
  if (speakFlag) speak(`Hurray! ${voiceText || message}`);
}

function showErrorFeedback(message, voiceText, speakFlag = true) {
  flashFeedback("error");
  playFeedback("error");
  showToast(message, "error");
  if (speakFlag) speak(`OwO! ${voiceText || "Try again"}`);
}

let toastTimer = null;
function showToast(message, type = "") {
  const icon = type === "success" ? "👍" : type === "error" ? "👎" : "";
  const html = icon
    ? `<span class="icon">${icon}</span><span class="content">${message}</span>`
    : `<span class="content">${message}</span>`;
  el.toast.innerHTML = html;
  el.toast.className = `toast ${type} show`;
  window.clearTimeout(toastTimer);
  toastTimer = window.setTimeout(() => {
    el.toast.classList.remove("show");
  }, 2200);
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
    color: ["#6a4df5", "#4cc955", "#2f8fff", "#ffaa2b", "#ef476f"][
      Math.floor(Math.random() * 5)
    ],
  }));

  let frames = 0;
  (function animate() {
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
  })();
}

function updateStats() {
  el.starsCount.textContent = String(state.stars);
  el.xpCount.textContent = String(state.xp);
  el.levelCount.textContent = String(state.level);
  el.xpFill.style.width = `${Math.min(100, (state.xp / MAX_XP) * 100)}%`;
}

function updateTimer() {
  const minutes = String(Math.floor(state.timeLeft / 60)).padStart(2, "0");
  const seconds = String(state.timeLeft % 60).padStart(2, "0");
  el.timerText.textContent = `${minutes}:${seconds}`;
}

function setMode(mode) {
  state.mode = normalizeMode(mode);
  el.modeTabs.forEach((tab) => {
    tab.classList.toggle(
      "active",
      normalizeMode(tab.dataset.mode || "") === state.mode,
    );
  });
  Object.entries(el.modeViews).forEach(([key, view]) => {
    view.classList.toggle("active", key === state.mode);
  });
  resetModeState(state.mode, true);
  resetTimer();
  startTimer();
  renderMode();
}

function resetTimer() {
  window.clearInterval(state.timerId);
  state.timerId = null;
  state.timeLeft = TIME_PER_MODE;
  updateTimer();
}

function startTimer() {
  window.clearInterval(state.timerId);
  state.timerId = window.setInterval(() => {
    state.timeLeft -= 1;
    updateTimer();
    if (state.timeLeft <= 0) {
      showErrorFeedback(
        "Time up. Starting again.",
        "Time is up. Let us try again.",
      );
      resetModeState(state.mode, false);
      renderMode();
      startTimer();
    }
  }, 1000);
}

function resetModeState(mode, keepTimer = true) {
  if (!keepTimer) {
    resetTimer();
  }

  if (mode === "relationship") {
    const cards = pickRelationshipCards();
    state.relationship = {
      cards,
      assignments: {},
      checked: {},
      selectedCardId: null,
      completed: false,
    };
    state.timeLeft = TIME_PER_MODE;
    updateTimer();
  }

  if (mode === "situations") {
    state.situations = {
      questions: shuffle(SAFETY_SITUATIONS),
      index: 0,
      selectedAnswer: null,
      locked: false,
    };
    state.timeLeft = TIME_PER_MODE;
    updateTimer();
  }

  if (mode === "safePeople") {
    state.safePeople = {
      cards: shuffle(SAFE_PEOPLE_POOL),
      selectedIds: [],
      locked: false,
    };
    state.timeLeft = TIME_PER_MODE;
    updateTimer();
  }
}

function pickRelationshipCards() {
  const safe = RELATIONSHIP_PEOPLE.filter((item) => item.zone === "safe");
  const careful = RELATIONSHIP_PEOPLE.filter((item) => item.zone === "careful");
  const unsafe = RELATIONSHIP_PEOPLE.filter((item) => item.zone === "unsafe");

  const selected = [
    ...shuffle(safe).slice(0, 4),
    ...shuffle(careful).slice(0, 4),
    ...shuffle(unsafe).slice(0, 4),
  ];

  return shuffle(selected);
}

function currentRelationshipCardsLeft() {
  return state.relationship.cards.filter(
    (card) => !state.relationship.checked[card.id],
  ).length;
}

function currentSituation() {
  return state.situations.questions[state.situations.index];
}

function currentSafePeopleCount() {
  return state.safePeople.selectedIds.length;
}

function renderGuide() {
  el.guideText.innerHTML = SAFE_MODES[state.mode].guide;
  el.situationGuide.textContent = SAFE_MODES.situations.guide.replace(
    /<[^>]+>/g,
    "",
  );
}

function updateSidebar() {
  const config = SAFE_MODES[state.mode];
  el.taskText.textContent = config.task;

  if (state.mode === "relationship") {
    el.progressText.innerHTML = `Cards Left: <strong>${currentRelationshipCardsLeft()}</strong>`;
  } else if (state.mode === "situations") {
    el.progressText.innerHTML = `Question: <strong>${state.situations.index + 1} / ${state.situations.questions.length}</strong>`;
  } else {
    el.progressText.innerHTML = `Selected: <strong>${currentSafePeopleCount()} / ${SAFE_PEOPLE_POOL.filter((item) => item.safe).length}</strong>`;
  }
}

// MODE 1: RELATIONSHIP SORTING
function renderRelationshipMode() {
  const cards = state.relationship.cards;
  const isReview = state.review.active && state.review.mode === "relationship";
  const next = cards.find((card) => !state.relationship.checked[card.id]);
  el.hintText.textContent = isReview
    ? "Review: see where everyone belongs."
    : next
      ? next.hint
      : SAFE_MODES.relationship.hint;
  el.peopleGrid.innerHTML = cards
    .map((card) => {
      const hidden =
        !isReview && state.relationship.checked[card.id] ? "hidden" : "";
      const draggable = isReview ? "false" : "true";
      return `
        <article class="person-card ${hidden}" draggable="${draggable}" data-person-id="${card.id}">
          <img src="${card.image}" alt="${card.name}" loading="lazy" decoding="async" onerror="this.src='${FALLBACK_IMAGE}'" />
          <p>${card.name}</p>
        </article>
      `;
    })
    .join("");

  [el.safeZoneItems, el.carefulZoneItems, el.unsafeZoneItems].forEach(
    (node) => {
      node.innerHTML = "";
    },
  );

  const grouped = {
    safe: el.safeZoneItems,
    careful: el.carefulZoneItems,
    unsafe: el.unsafeZoneItems,
  };

  Object.entries(grouped).forEach(([zone, node]) => {
    const items = isReview
      ? cards.filter((card) => card.zone === zone)
      : cards.filter(
        (card) => state.relationship.assignments[card.id] === zone,
      );

    if (isReview) {
      node.innerHTML = items
        .map(
          (item) => `
          <div class="zone-card">
            <img src="${item.image}" alt="${item.name}" loading="lazy" decoding="async" onerror="this.src='${FALLBACK_IMAGE}'" />
            <span>${item.name}</span>
          </div>
        `,
        )
        .join("");
      return;
    }

    node.innerHTML = items
      .map(
        (item) => `
        <div class="zone-pill">
          <div class="pill-left">
            <img class="pill-thumb" src="${item.image}" alt="${item.name}" loading="lazy" decoding="async" onerror="this.src='${FALLBACK_IMAGE}'" />
            <div class="pill-meta"><strong>${item.name}</strong><small>${item.hint || ""}</small></div>
          </div>
          <button type="button" data-remove-id="${item.id}">Remove</button>
        </div>
      `,
      )
      .join("");
    node.querySelectorAll("[data-remove-id]").forEach((button) => {
      button.addEventListener("click", () => {
        delete state.relationship.assignments[button.dataset.removeId || ""];
        delete state.relationship.checked[button.dataset.removeId || ""];
        renderMode();
      });
    });
  });

  if (!isReview) {
    bindRelationshipCardEvents();
  }
}

function bindRelationshipCardEvents() {
  el.peopleGrid.querySelectorAll(".person-card").forEach((card) => {
    card.addEventListener("dragstart", (event) => {
      event.dataTransfer.effectAllowed = "move";
      event.dataTransfer.setData("text/plain", card.dataset.personId || "");
    });

    card.addEventListener("click", () => {
      state.relationship.selectedCardId = card.dataset.personId || "";
      el.peopleGrid
        .querySelectorAll(".person-card")
        .forEach((node) => node.classList.remove("selected"));
      card.classList.add("selected");
      const item = state.relationship.cards.find(
        (entry) => entry.id === state.relationship.selectedCardId,
      );
      if (item) {
        showToast(`Selected ${item.name}. Tap a zone.`, "");
        speak(`Selected ${item.name}. Tap a zone.`);
      }
    });
  });
}

function assignRelationshipCard(cardId, zone) {
  if (!cardId || !zone || state.review.active) return;
  const card = state.relationship.cards.find((item) => item.id === cardId);
  if (!card) return;

  state.relationship.assignments[cardId] = zone;

  if (card.zone === zone) {
    state.relationship.checked[cardId] = true;
    showSuccessFeedback(`${card.name} is correct.`, `${card.name} is correct.`);
  } else {
    delete state.relationship.assignments[cardId];
    delete state.relationship.checked[cardId];
    showErrorFeedback(`${card.name} goes somewhere else.`, "Try again.");
  }

  renderMode();
  completeRelationshipIfReady();
}

function completeRelationshipIfReady() {
  const allCorrect = state.relationship.cards.every(
    (card) => state.relationship.checked[card.id],
  );
  if (!allCorrect) return;

  rewardSuccess("Great job! All correct.", "Great job! All correct.");
  handleModeCompletion("relationship", () => {
    resetModeState("relationship", false);
    renderMode();
    startTimer();
  });
}

function validateRelationshipMode() {
  showToast("Place cards one by one. Each drop checks instantly.", "");
  speak("Place the cards one by one.");
}

// MODE 2: SAFETY SITUATIONS
function renderSituationsMode() {
  const situation = currentSituation();
  if (!situation) return;

  el.situationText.textContent = situation.text;
  el.situationSubText.textContent = "Is this safe?";
  el.situationGuide.textContent = situation.hint;
  el.hintText.textContent = situation.hint;

  document.querySelectorAll(".answer-btn").forEach((button) => {
    button.classList.remove("selected");
  });
}

async function submitSituationAnswer(answer) {
  const situation = currentSituation();
  if (!situation || state.situations.locked || state.review.active) return;

  state.situations.selectedAnswer = answer;

  document.querySelectorAll(".answer-btn").forEach((button) => {
    button.classList.toggle("selected", button.dataset.answer === answer);
  });

  if (answer === situation.answer) {
    state.situations.locked = true;
    // give visual/audio reward but suppress immediate speech so we can control timing
    rewardSuccess(
      `Correct. ${situation.explanation}`,
      `Correct. ${situation.explanation}`,
      false,
    );
    // speak the explanation and wait for it to finish before advancing
    await speakPromise(situation.explanation);
    state.situations.index += 1;
    state.situations.selectedAnswer = null;
    state.situations.locked = false;
    if (state.situations.index >= state.situations.questions.length) {
      handleModeCompletion("situations", () => {
        state.situations.questions = shuffle(SAFETY_SITUATIONS);
        state.situations.index = 0;
        renderMode();
        startTimer();
      });
      return;
    }
    renderMode();
    startTimer();
  } else {
    triggerError(`Try again. ${situation.hint}`, "Try again.");
    const card = document.querySelector(".situation-card");
    if (card) {
      card.classList.add("shake");
      setTimeout(() => card.classList.remove("shake"), 350);
    }
  }
}

// MODE 3: MY SAFE PEOPLE
function renderSafePeopleMode() {
  const safePeople = state.safePeople.cards;
  const isReview = state.review.active && state.review.mode === "safePeople";
  const selectedIds = isReview
    ? SAFE_PEOPLE_POOL.filter((item) => item.safe).map((item) => item.id)
    : state.safePeople.selectedIds;
  const safePeopleSet = new Set(selectedIds);
  const gridPeople = isReview ? SAFE_PEOPLE_POOL : safePeople;

  el.hintText.textContent = isReview
    ? "Review: These people are safe helpers."
    : SAFE_MODES.safePeople.hint;

  el.safePeopleGrid.innerHTML = gridPeople
    .map((person) => {
      const hidden = !isReview && safePeopleSet.has(person.id) ? "hidden" : "";
      return `
        <article class="person-card ${hidden}" data-safe-person-id="${person.id}">
          <img src="${person.image}" alt="${person.name}" loading="lazy" decoding="async" onerror="this.src='${FALLBACK_IMAGE}'" />
          <p>${person.name}</p>
        </article>
      `;
    })
    .join("");

  if (isReview) {
    el.safeCircleItems.innerHTML = selectedIds
      .map((id) => {
        const person = SAFE_PEOPLE_POOL.find((item) => item.id === id);
        return person
          ? `
            <div class="safe-circle-item">
              <img src="${person.image}" alt="${person.name}" loading="lazy" decoding="async" onerror="this.src='${FALLBACK_IMAGE}'" />
              <span>${person.name}</span>
            </div>
          `
          : "";
      })
      .join("");

    el.safePeopleList.innerHTML = selectedIds
      .map((id, index) => {
        const person = SAFE_PEOPLE_POOL.find((item) => item.id === id);
        return person ? `<li>${index + 1}. ${person.name}</li>` : "";
      })
      .join("");
    return;
  }

  el.safeCircleItems.innerHTML = selectedIds
    .map((id) => {
      const person = SAFE_PEOPLE_POOL.find((item) => item.id === id);
      return person
        ? `<span class="safe-circle-tag"><img src="${person.image}" alt="${person.name}" onerror="this.src='${FALLBACK_IMAGE}'"/> ${person.name}</span>`
        : "";
    })
    .join("");

  el.safePeopleList.innerHTML = selectedIds.length
    ? selectedIds
      .map((id, index) => {
        const person = SAFE_PEOPLE_POOL.find((item) => item.id === id);
        return person
          ? `<li>${index + 1}. ${person.name} <button type="button" data-remove-safe="${person.id}">Remove</button></li>`
          : "";
      })
      .join("")
    : "<li>No safe people selected yet.</li>";

  el.safePeopleList.querySelectorAll("[data-remove-safe]").forEach((button) => {
    button.addEventListener("click", () => {
      state.safePeople.selectedIds = state.safePeople.selectedIds.filter(
        (id) => id !== button.dataset.removeSafe,
      );
      renderMode();
    });
  });
}

function handleSafePeopleSelect(personId) {
  const person = SAFE_PEOPLE_POOL.find((item) => item.id === personId);
  if (!person || state.safePeople.locked || state.review.active) return;

  if (person.safe) {
    if (!state.safePeople.selectedIds.includes(personId)) {
      state.safePeople.selectedIds.push(personId);
      rewardSuccess(
        `${person.name} is a safe person.`,
        `${person.name} can help you.`,
      );
      speak(`${person.name} can help you`);
    }
    renderMode();

    const safeTotal = SAFE_PEOPLE_POOL.filter((item) => item.safe).length;
    if (state.safePeople.selectedIds.length >= safeTotal) {
      state.safePeople.locked = true;
      showToast("Great job! You chose all safe people.", "success");
      speak("Great job. You chose all safe people.");
      handleModeCompletion("safePeople", () => {
        state.safePeople.selectedIds = [];
        state.safePeople.locked = false;
        state.safePeople.cards = shuffle(SAFE_PEOPLE_POOL);
        renderMode();
        startTimer();
      });
    }
  } else {
    triggerError(`${person.name} is not a safe person.`, "Try again.");
    speak(`${person.name} is not a safe person.`);
  }
}

// DRAG DROP
function bindRelationshipDropZones() {
  [el.safeZone, el.carefulZone, el.unsafeZone].forEach((zone) => {
    if (!zone) return;

    zone.addEventListener("dragover", (event) => {
      event.preventDefault();
      zone.classList.add("dragover");
    });

    zone.addEventListener("dragleave", () => zone.classList.remove("dragover"));

    zone.addEventListener("drop", (event) => {
      event.preventDefault();
      zone.classList.remove("dragover");
      if (state.review.active) return;
      const cardId = event.dataTransfer.getData("text/plain");
      assignRelationshipCard(cardId, zone.dataset.zone || "");
    });

    zone.addEventListener("click", () => {
      if (state.review.active) return;
      if (state.relationship.selectedCardId) {
        assignRelationshipCard(
          state.relationship.selectedCardId,
          zone.dataset.zone || "",
        );
        state.relationship.selectedCardId = null;
      }
    });
  });
}

// REWARDS
function rewardSuccess(message, voiceText, speakFlag = true) {
  state.stars += STARS_PER_CORRECT;
  state.xp += XP_PER_CORRECT;
  state.level += 1;
  updateStats();
  fireConfetti();
  showSuccessFeedback(message, voiceText, speakFlag);
}

function triggerError(message, voiceText) {
  showErrorFeedback(message, voiceText);
}

function handleModeCompletion(mode, resetFn) {
  state.completedRounds += 1;
  const everyThird = state.completedRounds % 3 === 0;
  const reviewEligible = mode === "relationship" || mode === "safePeople";

  if ((everyThird || state.review.pending) && reviewEligible) {
    state.review.pending = false;
    startReviewMode(mode, resetFn);
    return;
  }

  if (everyThird && !reviewEligible) {
    state.review.pending = true;
  }

  resetFn();
}

function startReviewMode(mode, resetFn) {
  if (state.review.timerId) {
    window.clearTimeout(state.review.timerId);
  }

  window.clearInterval(state.timerId);
  state.review.active = true;
  state.review.mode = mode;
  renderMode();
  showToast("Review time: see all images.", "");

  state.review.timerId = window.setTimeout(() => {
    state.review.active = false;
    state.review.mode = null;
    resetFn();
  }, 3600);
}

// RENDER / MODE SWITCHER
function renderMode() {
  updateStats();
  updateTimer();
  renderGuide();

  if (state.mode === "relationship") {
    renderRelationshipMode();
  }

  if (state.mode === "situations") {
    renderSituationsMode();
  }

  if (state.mode === "safePeople") {
    renderSafePeopleMode();
  }

  updateSidebar();
}

function resetCurrentMode() {
  if (state.review.timerId) {
    window.clearTimeout(state.review.timerId);
    state.review.timerId = null;
  }
  state.review.active = false;
  state.review.mode = null;
  state.review.pending = false;
  resetModeState(state.mode, true);
  resetTimer();
  startTimer();
  renderMode();
  showToast("Mode reset.", "");
  speak("Reset");
}

function bindEvents() {
  el.modeTabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      if (state.review.active) return;
      setMode(tab.dataset.mode || "relationship");
    });
  });

  el.checkBtn.addEventListener("click", () => {
    if (state.review.active) return;
    if (state.mode === "relationship") {
      validateRelationshipMode();
      return;
    }

    if (state.mode === "situations") {
      const situation = currentSituation();
      if (!situation) return;
      if (!state.situations.selectedAnswer) {
        showToast("Choose an answer first.", "error");
        speak("Choose an answer first.");
        return;
      }
      submitSituationAnswer(state.situations.selectedAnswer);
      return;
    }

    if (state.mode === "safePeople") {
      const safeTotal = SAFE_PEOPLE_POOL.filter((item) => item.safe).length;
      if (state.safePeople.selectedIds.length === safeTotal) {
        showToast("Great work! All safe people selected.", "success");
        speak("Great work");
      } else {
        showToast("Select all safe people.", "error");
        speak("Please select all safe people.");
      }
    }
  });

  el.hintBtn.addEventListener("click", () => {
    if (state.review.active) return;
    if (state.mode === "relationship") {
      const next = state.relationship.cards.find(
        (card) => !state.relationship.checked[card.id],
      );
      const hint = next ? next.hint : SAFE_MODES.relationship.hint;
      showToast(hint, "");
      el.hintText.textContent = hint;
      speak(hint);
      return;
    }

    if (state.mode === "situations") {
      const situation = currentSituation();
      if (!situation) return;
      showToast(situation.hint, "");
      el.hintText.textContent = situation.hint;
      speak(situation.hint);
      return;
    }

    if (state.mode === "safePeople") {
      const hint = SAFE_MODES.safePeople.hint;
      showToast(hint, "");
      el.hintText.textContent = hint;
      speak(hint);
    }
  });

  if (el.resetTopBtn) {
    el.resetTopBtn.addEventListener("click", () => window.location.reload());
  }

  window.addEventListener("keydown", (e) => {
    if (e.ctrlKey && e.shiftKey && (e.key === "R" || e.key === "r")) {
      e.preventDefault();
      window.location.reload();
    }
  });

  el.listenBtn.addEventListener("click", () => {
    if (state.mode === "relationship") {
      speak("Drag each person to safe, careful, or unsafe zone.");
      return;
    }

    if (state.mode === "situations") {
      const situation = currentSituation();
      if (situation) {
        speak(`${situation.text} Is this safe?`);
      }
      return;
    }

    if (state.mode === "safePeople") {
      speak("Choose your trusted people.");
    }
  });

  el.safePeopleGrid.addEventListener("click", (event) => {
    if (state.review.active) return;
    const card = event.target.closest("[data-safe-person-id]");
    if (!card) return;
    handleSafePeopleSelect(card.dataset.safePersonId || "");
  });

  document.querySelectorAll(".answer-btn").forEach((button) => {
    button.addEventListener("click", () => {
      if (state.review.active) return;
      if (state.mode !== "situations") return;
      state.situations.selectedAnswer = button.dataset.answer || null;
      submitSituationAnswer(button.dataset.answer || "");
    });
  });

  bindRelationshipDropZones();
}

function init() {
  resetModeState("relationship", true);
  state.situations.questions = shuffle(SAFETY_SITUATIONS);
  state.safePeople.cards = shuffle(SAFE_PEOPLE_POOL);
  updateStats();
  updateTimer();
  bindEvents();
  renderMode();
  startTimer();
  speak("Drag each person to safe, careful, or unsafe zone.");
}

window.addEventListener("beforeunload", () => {
  window.clearInterval(state.timerId);
});

window.addEventListener("resize", () => {
  el.confettiCanvas.width = window.innerWidth;
  el.confettiCanvas.height = window.innerHeight;
});

init();
