// Voice settings controller for PSNF Interactive Learning Games
// Persists speed (rate) and pitch in localStorage and exposes settings globally

(function () {
  'use strict';

  // Helper to load settings from localStorage
  const DB_NAME = 'PSNFDatabase';
  const DB_VERSION = 1;
  const STORE_NAME = 'settingsStore';

  // Initialize IndexedDB and load settings into window.voiceSettingsCache
  function initIndexedDB(callback) {
    if (!window.indexedDB) {
      if (callback) callback();
      return;
    }

    try {
      const request = window.indexedDB.open(DB_NAME, DB_VERSION);

      request.onerror = function (event) {
        console.error("IndexedDB error:", event.target.errorCode);
        if (callback) callback();
      };

      request.onsuccess = function (event) {
        const db = event.target.result;
        try {
          const transaction = db.transaction([STORE_NAME], "readonly");
          const store = transaction.objectStore(STORE_NAME);
          const getRequest = store.get('voiceSettings');

          getRequest.onsuccess = function () {
            if (getRequest.result) {
              const settings = getRequest.result.value;
              if (settings && typeof settings.rate === 'number' && typeof settings.pitch === 'number') {
                if (!settings.gender) settings.gender = 'female';
                window.voiceSettingsCache = settings;
                // Keep localStorage synchronized
                localStorage.setItem('voiceSettings', JSON.stringify(settings));
              }
            }
            if (callback) callback();
          };

          getRequest.onerror = function () {
            if (callback) callback();
          };
        } catch (e) {
          console.error("Error reading from IndexedDB settingsStore:", e);
          if (callback) callback();
        }
      };

      request.onupgradeneeded = function (event) {
        const db = event.target.result;
        if (!db.objectStoreNames.contains(STORE_NAME)) {
          db.createObjectStore(STORE_NAME, { keyPath: 'id' });
        }
      };
    } catch (e) {
      console.error("Failed to initialize IndexedDB:", e);
      if (callback) callback();
    }
  }

  // Save settings object back to IndexedDB database
  function saveVoiceSettingsToDB(settings) {
    if (!window.indexedDB) return;

    try {
      const request = window.indexedDB.open(DB_NAME, DB_VERSION);

      request.onsuccess = function (event) {
        const db = event.target.result;
        try {
          const transaction = db.transaction([STORE_NAME], "readwrite");
          const store = transaction.objectStore(STORE_NAME);
          store.put({ id: 'voiceSettings', value: settings });
        } catch (e) {
          console.error("Error writing to IndexedDB settingsStore:", e);
        }
      };
    } catch (e) {
      console.error("Failed to open IndexedDB for saving:", e);
    }
  }

  // Helper to load settings from cache, localStorage, or defaults
  window.getVoiceSettings = function () {
    if (window.voiceSettingsCache) {
      return window.voiceSettingsCache;
    }
    const saved = localStorage.getItem('voiceSettings');
    if (saved) {
      try {
        const parsed = JSON.parse(saved);
        if (typeof parsed.rate === 'number' && typeof parsed.pitch === 'number') {
          if (!parsed.gender) parsed.gender = 'female';
          window.voiceSettingsCache = parsed;
          return parsed;
        }
      } catch (e) {
        // Fallback
      }
    }
    const defaults = { rate: 0.85, pitch: 1.0, gender: 'female' };
    window.voiceSettingsCache = defaults;
    return defaults;
  };

  // Helper to save settings globally
  function saveVoiceSettings(rate, pitch, gender) {
    const settings = { rate, pitch, gender };
    window.voiceSettingsCache = settings;
    localStorage.setItem('voiceSettings', JSON.stringify(settings));
    saveVoiceSettingsToDB(settings);
  }

  // Text normalization to make all words clearly voicable like Google Translate
  window.normalizeSpeechText = function (text) {
    if (!text) return "";
    let clean = text;

    // 1. Convert "OwO!" to "Oh woah!"
    if (clean.includes("OwO!")) {
      clean = clean.replace(/OwO!/g, "Oh woah!");
    }

    // 2. Replace Indian rupee symbol ₹ followed by digits with digits + " rupees"
    clean = clean.replace(/₹\s*(\d+)/g, "$1 rupees");

    // 3. Replace quantity markers like "x2" or "x 2" with "quantity 2" for clear pronunciation
    clean = clean.replace(/\bx\s*(\d+)\b/gi, ", quantity $1");

    // 4. Replace plus signs with "and"
    clean = clean.replace(/\+/g, " and ");

    // 5. Remove parentheses but keep content, e.g. "Amul Milk (Shakti-1)" -> "Amul Milk Shakti 1"
    clean = clean.replace(/\(([^)]+)\)/g, " $1 ");

    // 6. Replace forward slash / with " or "
    clean = clean.replace(/\//g, " or ");

    // 7. Clean up hyphens, underscores, and extra spaces
    clean = clean.replace(/[-_]/g, " ");
    clean = clean.replace(/\s+/g, " ").trim();

    return clean;
  };

  // Helper to determine gender score or classification of a voice
  function classifyVoiceGender(voice) {
    const name = voice.name.toLowerCase();

    // Keywords for male voices
    const maleKeywords = [
      'david', 'mark', 'george', 'paul', 'ravi', 'rishi', 'prabhat', 'hemant', 'andrew', 'james',
      'sean', 'richard', 'daniel', 'alex', 'fred', 'male', 'guy', 'boy', 'russell'
    ];

    // Keywords for female voices
    const femaleKeywords = [
      'zira', 'hazel', 'susan', 'haruka', 'veena', 'moira', 'tessa', 'samantha', 'karen',
      'fiona', 'victoria', 'female', 'girl', 'lady', 'heera', 'google'
    ];

    // Check male keywords
    for (const keyword of maleKeywords) {
      if (name.includes(keyword)) return 'male';
    }

    // Check female keywords
    for (const keyword of femaleKeywords) {
      if (name.includes(keyword)) return 'female';
    }

    // Fallback: If it has Google in the name, it's typically female unless it contains male keywords
    if (name.includes('google')) {
      return 'female';
    }

    // Default to female for other voices
    return 'female';
  }

  // Selector for the absolute best English voice available matching preferred gender
  window.getBestVoice = function (voices) {
    if (!voices || voices.length === 0) return null;

    const settings = window.getVoiceSettings();
    const targetGender = settings.gender || 'female';

    // 1. Filter Indian English voices first
    const inVoices = voices.filter(v => 
      v.lang.toLowerCase().replace('_', '-').startsWith("en-in") || 
      v.name.toLowerCase().includes("india")
    );

    // Filter matching target gender in Indian English voices
    let genderMatches = inVoices.filter(v => classifyVoiceGender(v) === targetGender);
    if (genderMatches.length > 0) {
      // Priority 1: Google voice matching target gender (usually high quality on Android)
      let best = genderMatches.find(v => v.name.includes("Google"));
      if (best) return best;

      // Priority 2: Microsoft voice matching target gender (e.g. Microsoft Ravi / Prabhat)
      best = genderMatches.find(v => v.name.includes("Microsoft"));
      if (best) return best;

      // Priority 3: First voice matching target gender
      return genderMatches[0];
    }

    // If no exact gender match in Indian English, but we have Indian English voices
    if (inVoices.length > 0) {
      let best = inVoices.find(v => v.name.includes("Google"));
      if (best) return best;
      best = inVoices.find(v => v.name.includes("Microsoft"));
      if (best) return best;
      return inVoices[0];
    }

    // 2. Fallback to general English voices
    const englishVoices = voices.filter(v => v.lang.startsWith("en"));
    if (englishVoices.length > 0) {
      genderMatches = englishVoices.filter(v => classifyVoiceGender(v) === targetGender);
      if (genderMatches.length > 0) {
        let best = genderMatches.find(v => v.name.includes("Google"));
        if (best) return best;
        best = genderMatches.find(v => v.name.includes("Microsoft"));
        if (best) return best;
        return genderMatches[0];
      }
      let best = englishVoices.find(v => v.name.includes("Google"));
      if (best) return best;
      best = englishVoices.find(v => v.name.includes("Microsoft"));
      if (best) return best;
      return englishVoices[0];
    }

    // 3. Fallback to first voice
    return voices[0];
  };

  // Pre-fetch voices to handle async SpeechSynthesis load
  let cachedVoices = [];
  function loadVoices() {
    if (window.speechSynthesis) {
      cachedVoices = window.speechSynthesis.getVoices();
    }
  }
  if (window.speechSynthesis) {
    loadVoices();
    if (window.speechSynthesis.onvoiceschanged !== undefined) {
      window.speechSynthesis.onvoiceschanged = loadVoices;
    }
  }

  // Create the floating settings button and modal container
  function initVoiceSettings() {
    // Add floating button
    if (!document.getElementById('voiceSettingsBtn')) {
      const btn = document.createElement('button');
      btn.id = 'voiceSettingsBtn';
      btn.className = 'voice-settings-btn';
      btn.innerHTML = '⚙️';
      btn.title = 'Voice Settings';
      btn.setAttribute('aria-label', 'Open voice settings');
      btn.type = 'button';
      document.body.appendChild(btn);

      btn.addEventListener('click', openVoiceSettingsModal);
    }

    // Add modal structure
    if (!document.getElementById('voiceSettingsModal')) {
      const backdrop = document.createElement('div');
      backdrop.id = 'voiceSettingsModal';
      backdrop.className = 'voice-settings-backdrop';
      backdrop.innerHTML = `
        <div class="voice-settings-modal glass-card">
          <header class="modal-header">
            <h3>⚙️ Voice Settings</h3>
            <button id="closeVoiceSettingsX" class="close-x-btn" type="button" aria-label="Close settings">&times;</button>
          </header>
          <main class="modal-body">
            <p>Customize the narrator's voice speed, pitch, and gender to your comfort.</p>
            
            <div class="setting-group">
              <div class="setting-label-row">
                <label for="voiceRateSlider">Voice Speed</label>
                <span id="voiceRateVal">0.85x</span>
              </div>
              <input type="range" id="voiceRateSlider" min="0.5" max="2.0" step="0.05" value="0.85" aria-valuemin="0.5" aria-valuemax="2.0">
              <div class="setting-labels">
                <span>Slow</span>
                <span>Normal</span>
                <span>Fast</span>
              </div>
            </div>

            <div class="setting-group">
              <div class="setting-label-row">
                <label for="voicePitchSlider">Voice Pitch</label>
                <span id="voicePitchVal">1.0</span>
              </div>
              <input type="range" id="voicePitchSlider" min="0.5" max="2.0" step="0.05" value="1.0" aria-valuemin="0.5" aria-valuemax="2.0">
              <div class="setting-labels">
                <span>Low</span>
                <span>Normal</span>
                <span>High</span>
              </div>
            </div>

            <div class="setting-group">
              <div class="setting-label-row">
                <label>Voice Gender</label>
              </div>
              <div class="voice-gender-selector" role="radiogroup" aria-label="Voice gender">
                <button type="button" class="gender-btn" id="genderFemaleBtn" data-gender="female" role="radio" aria-checked="false">
                  <span class="gender-icon">👩</span> Female
                </button>
                <button type="button" class="gender-btn" id="genderMaleBtn" data-gender="male" role="radio" aria-checked="false">
                  <span class="gender-icon">👨</span> Male
                </button>
              </div>
            </div>
            
            <div class="modal-actions">
              <button id="testVoiceBtn" class="test-voice-btn" type="button">🔊 Test Voice</button>
              <button id="closeVoiceSettingsBtn" class="close-voice-btn" type="button">Save & Close</button>
            </div>
          </main>
        </div>
      `;
      document.body.appendChild(backdrop);

      // Event Listeners
      const rateSlider = document.getElementById('voiceRateSlider');
      const pitchSlider = document.getElementById('voicePitchSlider');
      const rateVal = document.getElementById('voiceRateVal');
      const pitchVal = document.getElementById('voicePitchVal');
      const testBtn = document.getElementById('testVoiceBtn');
      const closeBtn = document.getElementById('closeVoiceSettingsBtn');
      const closeX = document.getElementById('closeVoiceSettingsX');
      const femaleBtn = document.getElementById('genderFemaleBtn');
      const maleBtn = document.getElementById('genderMaleBtn');

      // Load initial values
      const current = window.getVoiceSettings();
      rateSlider.value = current.rate;
      pitchSlider.value = current.pitch;
      rateVal.textContent = `${current.rate.toFixed(2)}x`;
      pitchVal.textContent = current.pitch.toFixed(2);

      // Helper to update active class in UI
      function updateGenderUI(gender) {
        if (gender === 'male') {
          maleBtn.classList.add('active');
          maleBtn.setAttribute('aria-checked', 'true');
          femaleBtn.classList.remove('active');
          femaleBtn.setAttribute('aria-checked', 'false');
        } else {
          femaleBtn.classList.add('active');
          femaleBtn.setAttribute('aria-checked', 'true');
          maleBtn.classList.remove('active');
          maleBtn.setAttribute('aria-checked', 'false');
        }
      }

      updateGenderUI(current.gender || 'female');

      // Update gender on click
      femaleBtn.addEventListener('click', () => {
        const settings = window.getVoiceSettings();
        updateGenderUI('female');
        saveVoiceSettings(settings.rate, settings.pitch, 'female');
      });

      maleBtn.addEventListener('click', () => {
        const settings = window.getVoiceSettings();
        updateGenderUI('male');
        saveVoiceSettings(settings.rate, settings.pitch, 'male');
      });

      // Update rate on input
      rateSlider.addEventListener('input', (e) => {
        const val = parseFloat(e.target.value);
        rateVal.textContent = `${val.toFixed(2)}x`;
        const activeGender = maleBtn.classList.contains('active') ? 'male' : 'female';
        saveVoiceSettings(val, parseFloat(pitchSlider.value), activeGender);
      });

      // Update pitch on input
      pitchSlider.addEventListener('input', (e) => {
        const val = parseFloat(e.target.value);
        pitchVal.textContent = val.toFixed(2);
        const activeGender = maleBtn.classList.contains('active') ? 'male' : 'female';
        saveVoiceSettings(parseFloat(rateSlider.value), val, activeGender);
      });

      // Test voice function
      testBtn.addEventListener('click', () => {
        if (!window.speechSynthesis) {
          alert('Text-to-speech is not supported in this browser.');
          return;
        }
        window.speechSynthesis.cancel();

        const testText = "Hello! This is a test of the narration voice.";
        const normalized = window.normalizeSpeechText(testText);
        const utter = new SpeechSynthesisUtterance(normalized);
        const settings = window.getVoiceSettings();

        const voices = window.speechSynthesis.getVoices();
        const preferredVoice = window.getBestVoice(voices);
        if (preferredVoice) {
          utter.voice = preferredVoice;
        }

        utter.rate = settings.rate;
        utter.pitch = settings.pitch;
        utter.volume = 1;
        utter.lang = "en-IN";

        window.speechSynthesis.speak(utter);
      });

      // Close events
      closeBtn.addEventListener('click', closeVoiceSettingsModal);
      closeX.addEventListener('click', closeVoiceSettingsModal);
      backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop) {
          closeVoiceSettingsModal();
        }
      });
    }
  }

  function openVoiceSettingsModal() {
    const modal = document.getElementById('voiceSettingsModal');
    if (modal) {
      const current = window.getVoiceSettings();
      const rateSlider = document.getElementById('voiceRateSlider');
      const pitchSlider = document.getElementById('voicePitchSlider');
      const rateVal = document.getElementById('voiceRateVal');
      const pitchVal = document.getElementById('voicePitchVal');
      const femaleBtn = document.getElementById('genderFemaleBtn');
      const maleBtn = document.getElementById('genderMaleBtn');

      if (rateSlider && pitchSlider && rateVal && pitchVal) {
        rateSlider.value = current.rate;
        pitchSlider.value = current.pitch;
        rateVal.textContent = `${current.rate.toFixed(2)}x`;
        pitchVal.textContent = current.pitch.toFixed(2);
      }

      if (femaleBtn && maleBtn) {
        const gender = current.gender || 'female';
        if (gender === 'male') {
          maleBtn.classList.add('active');
          maleBtn.setAttribute('aria-checked', 'true');
          femaleBtn.classList.remove('active');
          femaleBtn.setAttribute('aria-checked', 'false');
        } else {
          femaleBtn.classList.add('active');
          femaleBtn.setAttribute('aria-checked', 'true');
          maleBtn.classList.remove('active');
          maleBtn.setAttribute('aria-checked', 'false');
        }
      }

      modal.classList.add('active');
    }
  }

  function closeVoiceSettingsModal() {
    const modal = document.getElementById('voiceSettingsModal');
    if (modal) {
      modal.classList.remove('active');
      if (window.speechSynthesis) {
        window.speechSynthesis.cancel();
      }
    }
  }

  // Auto initialize when DOM is ready, loading settings from IndexedDB first
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      initIndexedDB(() => {
        initVoiceSettings();
      });
    });
  } else {
    initIndexedDB(() => {
      initVoiceSettings();
    });
  }

})();
