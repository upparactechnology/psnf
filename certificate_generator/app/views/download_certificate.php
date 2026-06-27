<?php /** @var array $certificateTypes */
/** @var array $conferences */
/** @var int $conference_id */
/** @var array $pageAlerts */
/** @var string $contactName */
/** @var string $contactEmail */
/** @var string $contactMobileNumber */
/** @var string $contactCategory */
/** @var string $contactMessage */ ?>
<section class="public-portal">
    <section class="public-hero">
        <h1>Download Certificate</h1>
        <p>Search generated certificates and download your verified PDF instantly.</p>
    </section>

    <?php foreach (($pageAlerts ?? []) as $alert): ?>
        <div class="flash flash-<?php echo e((string) ($alert['type'] ?? 'info')); ?>"><?php echo e((string) ($alert['message'] ?? '')); ?></div>
    <?php endforeach; ?>

    <section class="portal-card">
        <form method="get" class="form-grid conference-picker">
            <input type="hidden" name="page" value="download-certificate">
            <label>
                Conference
                <select name="conference_id" onchange="this.form.submit()">
                    <?php foreach ($conferences as $c): ?>
                        <option value="<?php echo (int) $c['id']; ?>" <?php echo (int) $c['id'] === (int) $conference_id ? 'selected' : ''; ?>>
                            <?php echo e((string) $c['name'] . ' - ' . (string) $c['year']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </form>
    </section>

    <section class="portal-grid">
        <article class="portal-card">
            <h2>Find Your Certificate</h2>
            <div class="top-gap-sm">
                <h3>How To Download Certificate</h3>
                <ol class="small">
                    <li>Select the conference from the dropdown.</li>
                    <li>Optionally select certificate category/type.</li>
                    <li>Type participant name or title in search box.</li>
                    <li>Click the correct result from suggestions.</li>
                    <li>Click <strong>Download PDF</strong>.</li>
                </ol>
            </div>
            <form id="download-form" method="post" target="certificate-download-frame">
                <?php echo csrf_input(); ?>
                <input type="hidden" name="action" value="download">
                <input type="hidden" name="conference_id" value="<?php echo (int) $conference_id; ?>">
                <input type="hidden" name="participant_id" id="participant_id" value="">

                <div class="form-group">
                    <label>Category / Certificate Type (optional)</label>
                    <select id="type_id" name="type_id">
                        <option value="">-- any --</option>
                        <?php foreach ($certificateTypes as $ct): ?>
                            <option value="<?php echo (int) $ct['id']; ?>"><?php echo e((string) $ct['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group search-group">
                    <label>Search Generated Certificate</label>
                    <input type="text" id="q" autocomplete="off" placeholder="Type participant name or title...">
                    <div id="suggestions" class="suggestions"></div>
                </div>

                <div id="selected-info" hidden>
                    <div><strong id="sel-name"></strong> (<span id="sel-category"></span>)</div>
                    <div id="sel-title" class="small"></div>
                    <div class="top-gap-sm">
                        <button id="download-button" type="submit" class="button" disabled>Download PDF</button>
                    </div>
                </div>
            </form>
            <iframe name="certificate-download-frame" id="certificate-download-frame" style="position: absolute; width: 1px; height: 1px; opacity: 0.01; pointer-events: none; border: none;" aria-hidden="true"></iframe>
        </article>

        <article id="contact-form" class="portal-card contact-panel" hidden>
            <h2>Need Help? Contact Us</h2>
            <form method="post" class="form-grid">
                <?php echo csrf_input(); ?>
                <input type="hidden" name="action" value="contact_submit">
                <input type="hidden" name="conference_id" value="<?php echo (int) $conference_id; ?>">

                <label>
                    Name *
                    <input type="text" name="contact_name" value="<?php echo e($contactName ?? ''); ?>" maxlength="120" required>
                </label>

                <label>
                    Email *
                    <input type="email" name="contact_email" value="<?php echo e($contactEmail ?? ''); ?>" maxlength="190" placeholder="name@example.com" required>
                </label>

                <label>
                    Mobile Number *
                    <input type="tel" name="mobile_number" value="<?php echo e($contactMobileNumber ?? ''); ?>" inputmode="numeric" pattern="[0-9+\-\s()]{7,20}" maxlength="20" placeholder="Enter your mobile number" required>
                </label>

                <label>
                    Category *
                    <input type="text" name="contact_category" value="<?php echo e($contactCategory ?? ''); ?>" maxlength="120" placeholder="Example: Paper, Certificate Download" required>
                </label>

                <label>
                    Message *
                    <textarea name="contact_message" rows="5" minlength="5" required><?php echo e($contactMessage ?? ''); ?></textarea>
                </label>

                <div>
                    <button type="submit">Send Message</button>
                </div>
            </form>
        </article>
    </section>

    <p class="contact-link-wrap">
        <a href="#contact-form" id="contact-toggle-link">Contact Us</a>
    </p>
    <p class="dev-credit">developed by <a href="https://www.upparactechnology.com" target="_blank" rel="noopener noreferrer">www.upparactechnology.com</a></p>
</section>

<script>
(function () {
    var downloadForm = document.getElementById("download-form");
    var q = document.getElementById("q");
    var suggestions = document.getElementById("suggestions");
    var typeSel = document.getElementById("type_id");
    var participantIdInput = document.getElementById("participant_id");
    var selInfo = document.getElementById("selected-info");
    var selName = document.getElementById("sel-name");
    var selTitle = document.getElementById("sel-title");
    var selCategory = document.getElementById("sel-category");
    var downloadButton = document.getElementById("download-button");
    var contactForm = document.getElementById("contact-form");
    var contactToggleLink = document.getElementById("contact-toggle-link");
    var redirectUrl = "https://www.upparactechnology.com";
    var redirectTimer = null;

    if (!downloadForm || !q || !suggestions || !typeSel || !participantIdInput || !selInfo || !selName || !selTitle || !selCategory || !downloadButton) {
        return;
    }

    var timer = null;

    function ajaxSearch(query) {
        var typeId = typeSel.value;
        var conferenceId = <?php echo (int) $conference_id; ?>;
        var params = new URLSearchParams({
            page: "download-certificate",
            ajax: "1",
            q: query,
            type_id: typeId,
            conference_id: String(conferenceId)
        });

        fetch("index.php?" + params.toString())
            .then(function (r) {
                if (!r.ok) {
                    return r.text().then(function (text) {
                        throw new Error("HTTP " + r.status + ": " + text);
                    });
                }
                return r.json();
            })
            .then(function (data) {
                if (data && data.error) {
                    suggestions.innerHTML = "";
                    return;
                }
                renderSuggestions((data && data.results) ? data.results : []);
            })
            .catch(function () {
                suggestions.innerHTML = "";
            });
    }

    function renderSuggestions(items) {
        suggestions.innerHTML = "";
        if (!items || items.length === 0) {
            return;
        }

        items.forEach(function (it) {
            var div = document.createElement("div");
            div.className = "suggestion-item";
            var namesText = (it.names_display && it.names_display.trim() !== "")
                ? it.names_display
                : ((it.name && it.name.trim() !== "") ? it.name : it.title);
            div.textContent = namesText + " - " + (it.category || "");
            div.addEventListener("click", function () {
                selectItem(it);
            });
            suggestions.appendChild(div);
        });
    }

    function selectItem(item) {
        participantIdInput.value = String(item.id || "");
        selInfo.hidden = false;
        selName.textContent = (item.names_display && item.names_display.trim() !== "")
            ? item.names_display
            : (item.name || item.title || ("#" + item.id));
        selTitle.textContent = item.title || "";
        selCategory.textContent = item.category || "";
        downloadButton.disabled = !participantIdInput.value;
        suggestions.innerHTML = "";
    }

    q.addEventListener("input", function () {
        var val = this.value.trim();
        participantIdInput.value = "";
        selInfo.hidden = true;
        downloadButton.disabled = true;

        if (timer) {
            clearTimeout(timer);
        }

        if (val.length < 2) {
            suggestions.innerHTML = "";
            return;
        }

        timer = setTimeout(function () {
            ajaxSearch(val);
        }, 250);
    });

    downloadForm.addEventListener("submit", function () {
        if (!participantIdInput.value) {
            return;
        }

        if (redirectTimer) {
            clearTimeout(redirectTimer);
        }

        redirectTimer = setTimeout(function () {
            window.location.replace(redirectUrl);
        }, 3500);
    });

    if (window.history && typeof window.history.pushState === "function") {
        window.history.pushState({ downloadPortal: true }, "", window.location.href);
        window.addEventListener("popstate", function () {
            window.location.replace(redirectUrl);
        });
    }

    if (contactForm && contactToggleLink) {
        contactToggleLink.addEventListener("click", function (event) {
            event.preventDefault();
            contactForm.hidden = false;
            contactForm.scrollIntoView({ behavior: "smooth", block: "start" });
        });
    }
})();
</script>
