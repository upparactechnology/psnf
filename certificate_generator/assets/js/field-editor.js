(function () {
    "use strict";

    var root = document.getElementById("fieldEditorRoot");
    if (!root) {
        return;
    }

    var image = document.getElementById("templateImage");
    var canvas = document.getElementById("templateCanvas");
    var markersLayer = document.getElementById("markersLayer");

    var addFieldButton = document.getElementById("addFieldButton");
    var newFieldKey = document.getElementById("newFieldKey");
    var newFieldLabel = document.getElementById("newFieldLabel");
    var addTextElementButton = document.getElementById("addTextElementButton");
    var uploadTemplateImageButton = document.getElementById("uploadTemplateImageButton");
    var templateUploadInput = document.getElementById("templateUploadInput");
    var templateUploadForm = document.getElementById("templateUploadForm");
    var dynamicFieldButtons = document.querySelectorAll(".dynamic-field-button");
    var newTemplateContent = document.getElementById("newTemplateContent");
    var addTemplateBoxButton = document.getElementById("addTemplateBoxButton");
    var placeholderButtons = document.querySelectorAll(".placeholder-token");
    var inlineStyleButtons = document.querySelectorAll(".inline-style-token");
    var editorStatus = document.getElementById("editorStatus");
    var editorSaveState = document.getElementById("editorSaveState");
    var mappingsJson = document.getElementById("mappingsJson");
    var mappingForm = document.getElementById("mappingForm");

    var fieldKeyInput = document.getElementById("fieldKeyInput");
    var fieldLabelInput = document.getElementById("fieldLabelInput");
    var fontSizeInput = document.getElementById("fontSizeInput");
    var alignInput = document.getElementById("alignInput");
    var fontFamilyInput = document.getElementById("fontFamilyInput");
    var fontStyleInput = document.getElementById("fontStyleInput");
    var colorInput = document.getElementById("colorInput");
    var maxWidthInput = document.getElementById("maxWidthInput");
    var xPosInput = document.getElementById("xPosInput");
    var yPosInput = document.getElementById("yPosInput");
    var lineHeightInput = document.getElementById("lineHeightInput");
    var lineSpacingInput = document.getElementById("lineSpacingInput");
    var wordSpacingInput = document.getElementById("wordSpacingInput");
    var underlineInput = document.getElementById("underlineInput");
    var removeFieldButton = document.getElementById("removeFieldButton");
    var selectedFieldPanel = document.getElementById("selectedFieldPanel");
    var propertyEmptyState = document.getElementById("propertyEmptyState");

    var toggleBoldButton = document.getElementById("toggleBoldButton");
    var toggleItalicButton = document.getElementById("toggleItalicButton");
    var toggleUnderlineButton = document.getElementById("toggleUnderlineButton");
    var alignLeftButton = document.getElementById("alignLeftButton");
    var alignCenterButton = document.getElementById("alignCenterButton");
    var alignRightButton = document.getElementById("alignRightButton");
    var alignJustifyButton = document.getElementById("alignJustifyButton");

    var quickFontFamilyInput = document.getElementById("quickFontFamilyInput");
    var quickFontSizeInput = document.getElementById("quickFontSizeInput");
    var quickColorInput = document.getElementById("quickColorInput");
    var quickBoldButton = document.getElementById("quickBoldButton");
    var quickItalicButton = document.getElementById("quickItalicButton");
    var quickAlignLeftButton = document.getElementById("quickAlignLeftButton");
    var quickAlignCenterButton = document.getElementById("quickAlignCenterButton");
    var quickAlignRightButton = document.getElementById("quickAlignRightButton");
    var quickAlignJustifyButton = document.getElementById("quickAlignJustifyButton");

    var quickInlineBoldButton = document.getElementById("quickInlineBoldButton");
    var quickInlineItalicButton = document.getElementById("quickInlineItalicButton");
    var quickInlineBoldItalicButton = document.getElementById("quickInlineBoldItalicButton");
    var quickInlineFontFamilyInput = document.getElementById("quickInlineFontFamilyInput");
    var quickInlineFontApplyButton = document.getElementById("quickInlineFontApplyButton");
    var undoMappingButton = document.getElementById("undoMappingButton");
    var redoMappingButton = document.getElementById("redoMappingButton");
    var zoomOutButton = document.getElementById("zoomOutButton");
    var zoomInButton = document.getElementById("zoomInButton");
    var zoomResetButton = document.getElementById("zoomResetButton");
    var zoomLevelLabel = document.getElementById("zoomLevelLabel");
    var gridToggleButton = document.getElementById("gridToggleButton");
    var canvaTopToolbar = document.getElementById("canvaTopToolbar");
    var floatingInlineToolbar = document.getElementById("floatingInlineToolbar");
    var editorTabButtons = root.querySelectorAll("[data-editor-tab]");
    var editorPanes = root.querySelectorAll("[data-editor-pane]");
    var editorActionButtons = root.querySelectorAll("[data-editor-action]");

    var previewMeasureCanvas = document.createElement("canvas");
    var previewMeasureContext = previewMeasureCanvas.getContext("2d");

    var initial = [];
    try {
        initial = JSON.parse(root.getAttribute("data-initial") || "[]");
    } catch (error) {
        initial = [];
    }

    var defaultFontOptions = [
        {
            value: "default",
            label: "Default",
            preview_family: "Segoe UI, Trebuchet MS, sans-serif",
            canvas_family: "Segoe UI",
            preview_url: ""
        },
        {
            value: "arial",
            label: "Arial",
            preview_family: "Arial, sans-serif",
            canvas_family: "Arial",
            preview_url: ""
        },
        {
            value: "times",
            label: "Times New Roman",
            preview_family: "Times New Roman, serif",
            canvas_family: "Times New Roman",
            preview_url: ""
        },
        {
            value: "georgia",
            label: "Georgia",
            preview_family: "Georgia, serif",
            canvas_family: "Georgia",
            preview_url: ""
        },
        {
            value: "calibri",
            label: "Calibri",
            preview_family: "Calibri, Arial, sans-serif",
            canvas_family: "Calibri",
            preview_url: ""
        }
    ];

    var fontOptions = [];
    try {
        fontOptions = JSON.parse(root.getAttribute("data-font-options") || "[]");
    } catch (error) {
        fontOptions = [];
    }

    if (!Array.isArray(fontOptions) || fontOptions.length === 0) {
        fontOptions = defaultFontOptions.slice();
    }

    var fontOptionMap = {};
    var allowedFontFamilies = { default: true };

    fontOptions.forEach(function (font) {
        if (!font || typeof font !== "object") {
            return;
        }

        var key = String(font.value || "").trim().toLowerCase();
        if (!key) {
            return;
        }

        var fallback = defaultFontOptions[0];
        fontOptionMap[key] = {
            value: key,
            label: String(font.label || key),
            preview_family: String(font.preview_family || fallback.preview_family),
            canvas_family: String(font.canvas_family || fallback.canvas_family),
            preview_url: String(font.preview_url || "")
        };
        allowedFontFamilies[key] = true;
    });

    if (!fontOptionMap.default) {
        var defaultEntry = defaultFontOptions[0];
        fontOptionMap.default = {
            value: "default",
            label: defaultEntry.label,
            preview_family: defaultEntry.preview_family,
            canvas_family: defaultEntry.canvas_family,
            preview_url: ""
        };
        allowedFontFamilies.default = true;
    }

    var activeTextEditor = null;
    var editingState = null;
    var pendingOutsideExitTimer = null;

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function activateEditorPane(tabKey) {
        if (!tabKey) {
            return;
        }

        editorTabButtons.forEach(function (button) {
            var key = button.getAttribute("data-editor-tab") || "";
            var isActive = key === tabKey;
            button.classList.toggle("is-active", isActive);
            button.setAttribute("aria-selected", isActive ? "true" : "false");
        });

        editorPanes.forEach(function (pane) {
            var key = pane.getAttribute("data-editor-pane") || "";
            var isActive = key === tabKey;
            pane.classList.toggle("is-active", isActive);
            pane.hidden = !isActive;
        });
    }

    function clampNumber(value, fallback, min, max) {
        var numeric = Number(value);
        if (Number.isNaN(numeric)) {
            numeric = fallback;
        }
        return clamp(numeric, min, max);
    }

    function parseRawNumber(value, fallback) {
        var numeric = Number(value);
        if (Number.isNaN(numeric)) {
            return fallback;
        }
        return numeric;
    }

    function sanitizeColor(value) {
        if (!/^#[0-9A-Fa-f]{6}$/.test(value)) {
            return "#000000";
        }
        return value.toUpperCase();
    }

    function installCustomFontFaces() {
        var rules = [];

        Object.keys(fontOptionMap).forEach(function (key) {
            var option = fontOptionMap[key];
            if (!option || typeof option !== "object") {
                return;
            }

            if (key.indexOf("custom:") !== 0 || !option.preview_url) {
                return;
            }

            var familyName = String(option.canvas_family || "").replace(/"/g, "");
            if (!familyName) {
                return;
            }

            rules.push("@font-face { font-family: \"" + familyName + "\"; src: url(\"" + option.preview_url + "\") format(\"truetype\"); font-display: swap; }");
        });

        if (rules.length === 0) {
            return;
        }

        var styleTag = document.createElement("style");
        styleTag.id = "customFontFaces";
        styleTag.textContent = rules.join("\n");
        document.head.appendChild(styleTag);
    }

    installCustomFontFaces();

    if (editorTabButtons.length > 0 && editorPanes.length > 0) {
        editorTabButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                var key = button.getAttribute("data-editor-tab") || "text";
                activateEditorPane(key);
            });
        });

        var defaultButton = root.querySelector("[data-editor-tab].is-active") || editorTabButtons[0];
        if (defaultButton) {
            activateEditorPane(defaultButton.getAttribute("data-editor-tab") || "text");
        }
    }

    function parseRowOptions(row) {
        var raw = row.options_json;
        var parsed = {};

        if (raw && typeof raw === "object") {
            parsed = raw;
        } else if (typeof raw === "string" && raw.trim() !== "") {
            try {
                parsed = JSON.parse(raw);
            } catch (error) {
                parsed = {};
            }
        }

        var textAlign = String(parsed.text_align || row.align || "left").toLowerCase();
        if (["left", "center", "right", "justify"].indexOf(textAlign) === -1) {
            textAlign = "left";
        }

        var fontStyle = String(parsed.font_style || "regular").toLowerCase();
        if (["regular", "bold", "italic", "bold_italic"].indexOf(fontStyle) === -1) {
            fontStyle = "regular";
        }

        var fontFamily = String(parsed.font_family || "default").toLowerCase();
        if (!allowedFontFamilies[fontFamily]) {
            fontFamily = "default";
        }

        return {
            text_align: textAlign,
            font_style: fontStyle,
            font_family: fontFamily,
            line_spacing: clampNumber(parsed.line_spacing, 1.25, 1, 3),
            word_spacing: clampNumber(parsed.word_spacing, 0, 0, 30)
        };
    }

    var mappings = initial.map(function (row, index) {
        return {
            id: "m" + index + "_" + Date.now(),
            field_key: String(row.field_key || ""),
            label: String(row.label || row.field_key || "Field"),
            x_pos: Number(row.x_pos || 120),
            y_pos: Number(row.y_pos || 120),
            font_size: Number(row.font_size || 32),
            align: row.align === "center" ? "center" : "left",
            color_hex: String(row.color_hex || "#000000"),
            max_width: Number(row.max_width || 900),
            line_height: Number(row.line_height || 42),
            underline: Number(row.underline || 0) === 1,
            options: parseRowOptions(row)
        };
    });

    var historyStack = [];
    var historyIndex = -1;
    var historyCommitTimer = null;
    var historyMuted = false;
    var zoomLevel = 1;

    var selectedId = null;
    var pendingNewField = null;
    var dragState = null;
    var resizeState = null;
    var dragDirty = false;
    var resizeDirty = false;

    function setStatus(text) {
        if (editorStatus) {
            editorStatus.textContent = text;
        }
    }

    function setSaveState(text) {
        if (editorSaveState) {
            editorSaveState.textContent = text;
        }
    }

    function applyCanvasZoom() {
        var zoomPercent = Math.round(zoomLevel * 100);
        image.style.maxWidth = "none";
        image.style.width = zoomPercent + "%";

        if (zoomLevelLabel) {
            zoomLevelLabel.textContent = zoomPercent + "%";
        }
    }

    function setZoom(nextZoom) {
        var clamped = clampNumber(nextZoom, 1, 0.5, 2);
        zoomLevel = Math.round(clamped * 100) / 100;
        applyCanvasZoom();
        renderMarkers();
    }

    function cloneMappingRow(row) {
        var options = row.options && typeof row.options === "object" ? row.options : {};
        return {
            id: String(row.id || ""),
            field_key: String(row.field_key || ""),
            label: String(row.label || ""),
            x_pos: Number(row.x_pos || 0),
            y_pos: Number(row.y_pos || 0),
            font_size: Number(row.font_size || 32),
            align: String(row.align || "left"),
            color_hex: sanitizeColor(String(row.color_hex || "#000000")),
            max_width: Number(row.max_width || 900),
            line_height: Number(row.line_height || 42),
            underline: !!row.underline,
            options: {
                text_align: String(options.text_align || row.align || "left"),
                font_style: String(options.font_style || "regular"),
                font_family: normalizeFontToken(options.font_family || "default"),
                line_spacing: clampNumber(options.line_spacing, 1.25, 1, 3),
                word_spacing: clampNumber(options.word_spacing, 0, 0, 30)
            }
        };
    }

    function snapshotEditorState() {
        return {
            selectedId: selectedId,
            mappings: mappings.map(function (row) {
                return cloneMappingRow(row);
            })
        };
    }

    function applyEditorSnapshot(snapshot) {
        if (!snapshot || !Array.isArray(snapshot.mappings)) {
            return;
        }

        historyMuted = true;
        mappings = snapshot.mappings.map(function (row) {
            return cloneMappingRow(row);
        });
        selectedId = snapshot.selectedId || null;
        renderMarkers();
        syncSelectedPanel();
        savePayloadToInput();
        historyMuted = false;
    }

    function updateHistoryControls() {
        if (undoMappingButton) {
            undoMappingButton.disabled = historyIndex <= 0;
        }

        if (redoMappingButton) {
            redoMappingButton.disabled = historyIndex >= historyStack.length - 1;
        }
    }

    function commitHistory() {
        if (historyMuted) {
            return;
        }

        var snapshot = snapshotEditorState();
        var encoded = JSON.stringify(snapshot);
        var current = historyStack[historyIndex];

        if (current && current.encoded === encoded) {
            updateHistoryControls();
            return;
        }

        historyStack = historyStack.slice(0, historyIndex + 1);
        historyStack.push({
            encoded: encoded,
            snapshot: snapshot
        });

        if (historyStack.length > 120) {
            historyStack.shift();
        }

        historyIndex = historyStack.length - 1;
        updateHistoryControls();
    }

    function scheduleHistoryCommit() {
        if (historyMuted) {
            return;
        }

        setSaveState("Unsaved changes");

        if (historyCommitTimer !== null) {
            window.clearTimeout(historyCommitTimer);
        }

        historyCommitTimer = window.setTimeout(function () {
            historyCommitTimer = null;
            commitHistory();
        }, 220);
    }

    function isToolbarTarget(target) {
        return !!(
            (canvaTopToolbar && target && canvaTopToolbar.contains(target)) ||
            (floatingInlineToolbar && target && floatingInlineToolbar.contains(target))
        );
    }

    function clearPendingOutsideExit() {
        if (pendingOutsideExitTimer !== null) {
            window.clearTimeout(pendingOutsideExitTimer);
            pendingOutsideExitTimer = null;
        }
    }

    function normalizeEditedText(rawText) {
        return String(rawText || "")
            .replace(/\r/g, "")
            .replace(/\u00A0/g, " ")
            .replace(/[ \t]+\n/g, "\n")
            .replace(/\n{3,}/g, "\n\n")
            .trim();
    }

    function exitEditingMode() {
        if (!editingState || !editingState.markerMain) {
            return;
        }

        var state = editingState;
        editingState = null;
        clearPendingOutsideExit();

        var marker = state.marker;
        var markerMain = state.markerMain;
        var row = state.row;
        var templateMode = !!state.templateMode;

        markerMain.contentEditable = "false";
        marker.classList.remove("editing");

        if (activeTextEditor === markerMain) {
            activeTextEditor = null;
        }

        var updatedText = templateMode
            ? editableTemplateHtmlToTaggedText(markerMain)
            : String(markerMain.textContent || "");

        updatedText = normalizeEditedText(updatedText);
        row.label = updatedText || row.field_key || "Text";

        if (selectedId === row.id) {
            syncSelectedPanel();
        }

        setStatus(templateMode
            ? "Template content updated from preview box."
            : "Field label updated.");
        renderMarkers();
        savePayloadToInput();
        scheduleHistoryCommit();
        updateFloatingToolbarVisibility();
    }

    function scheduleOutsideExit() {
        if (!editingState || pendingOutsideExitTimer !== null) {
            return;
        }

        pendingOutsideExitTimer = window.setTimeout(function () {
            pendingOutsideExitTimer = null;
            exitEditingMode();
        }, 0);
    }

    function getScale() {
        var naturalWidth = image.naturalWidth || image.width || 1;
        var naturalHeight = image.naturalHeight || image.height || 1;
        var displayWidth = image.clientWidth || 1;
        var displayHeight = image.clientHeight || 1;

        return {
            x: displayWidth / naturalWidth,
            y: displayHeight / naturalHeight,
            naturalWidth: naturalWidth,
            naturalHeight: naturalHeight,
            displayWidth: displayWidth,
            displayHeight: displayHeight
        };
    }

    function toDisplayX(actualX) {
        return actualX * getScale().x;
    }

    function toDisplayY(actualY) {
        return actualY * getScale().y;
    }

    function toActualX(displayX) {
        return Math.round(displayX / getScale().x);
    }

    function toActualY(displayY) {
        return Math.round(displayY / getScale().y);
    }

    function toActualWidth(displayWidth) {
        return Math.round(displayWidth / getScale().x);
    }

    function toActualHeight(displayHeight) {
        return Math.round(displayHeight / getScale().y);
    }

    function findMapping(id) {
        return mappings.find(function (row) {
            return row.id === id;
        }) || null;
    }

    function isTemplateMapping(row) {
        var key = String(row.field_key || "");
        var label = String(row.label || "");
        return /^tpl_/i.test(key) || /\{[a-zA-Z0-9_]+\}/.test(label);
    }

    function summarizeTextBoxLabel(row) {
        var text = String(row.label || row.field_key || "Text").replace(/\s+/g, " ").trim();
        if (text.length <= 42) {
            return text;
        }
        return text.slice(0, 39) + "...";
    }

    function generateTemplateFieldKey() {
        var index = 1;
        while (mappings.some(function (row) {
            return String(row.field_key || "") === "tpl_content_" + index;
        })) {
            index += 1;
        }
        return "tpl_content_" + index;
    }

    function insertTokenAtCaret(element, token) {
        if (!element) {
            return;
        }

        var value = element.value || "";
        var start = Number.isInteger(element.selectionStart) ? element.selectionStart : value.length;
        var end = Number.isInteger(element.selectionEnd) ? element.selectionEnd : value.length;

        element.value = value.slice(0, start) + token + value.slice(end);

        var next = start + token.length;
        element.focus();
        if (typeof element.setSelectionRange === "function") {
            element.setSelectionRange(next, next);
        }
    }

    function wrapSelectionWithTags(element, openTag, closeTag) {
        if (!element) {
            return;
        }

        var value = element.value || "";
        var start = Number.isInteger(element.selectionStart) ? element.selectionStart : value.length;
        var end = Number.isInteger(element.selectionEnd) ? element.selectionEnd : value.length;

        var selected = value.slice(start, end);
        var wrapped = openTag + selected + closeTag;

        element.value = value.slice(0, start) + wrapped + value.slice(end);
        element.focus();

        if (typeof element.setSelectionRange === "function") {
            if (selected.length === 0) {
                element.setSelectionRange(start + openTag.length, start + openTag.length);
            } else {
                element.setSelectionRange(start + openTag.length, start + openTag.length + selected.length);
            }
        }

        element.dispatchEvent(new Event("input", { bubbles: true }));
    }

    function normalizeFontToken(fontToken) {
        var token = String(fontToken || "default").trim().toLowerCase();
        if (allowedFontFamilies[token]) {
            return token;
        }

        if (/^[a-z0-9_-]+$/.test(token) && allowedFontFamilies["custom:" + token]) {
            return "custom:" + token;
        }

        return "default";
    }

    function getSelectionRangeInElement(element) {
        var selection = window.getSelection();
        if (!selection || selection.rangeCount === 0) {
            return null;
        }

        var range = selection.getRangeAt(0);
        if (!element.contains(range.commonAncestorContainer)) {
            return null;
        }

        return range;
    }

    function createInlineStyledSpan(tagName, fontValue) {
        var span = document.createElement("span");
        span.setAttribute("data-inline-tag", tagName);

        if (tagName === "b") {
            span.style.fontWeight = "700";
            return span;
        }

        if (tagName === "i") {
            span.style.fontStyle = "italic";
            return span;
        }

        if (tagName === "bi") {
            span.style.fontWeight = "700";
            span.style.fontStyle = "italic";
            return span;
        }

        if (tagName === "font") {
            var normalized = normalizeFontToken(fontValue);
            span.setAttribute("data-font-family", normalized);
            span.style.fontFamily = previewFontFamilyCss(normalized);
            return span;
        }

        if (tagName === "left" || tagName === "center" || tagName === "right" || tagName === "justify") {
            span.style.display = "block";
            span.style.textAlign = tagName;
            return span;
        }

        return span;
    }

    function parseInlineTagDescriptor(openTag) {
        var tagMatch = String(openTag || "").match(/^\[(b|i|bi|left|center|right|justify)\]$/i);
        if (tagMatch) {
            return { tag: tagMatch[1].toLowerCase(), fontFamily: null };
        }

        var fontMatch = String(openTag || "").match(/^\[font=([^\]]+)\]$/i);
        if (fontMatch) {
            return {
                tag: "font",
                fontFamily: normalizeFontToken(fontMatch[1])
            };
        }

        return null;
    }

    function wrapSelectionInContentEditable(element, openTag) {
        if (!element || !element.isContentEditable) {
            return false;
        }

        var descriptor = parseInlineTagDescriptor(openTag);
        if (!descriptor) {
            return false;
        }

        var range = getSelectionRangeInElement(element);
        if (!range || range.collapsed) {
            return false;
        }

        var span = createInlineStyledSpan(descriptor.tag, descriptor.fontFamily);

        try {
            range.surroundContents(span);
        } catch (error) {
            var extracted = range.extractContents();
            span.appendChild(extracted);
            range.insertNode(span);
        }

        var selection = window.getSelection();
        if (selection) {
            var nextRange = document.createRange();
            nextRange.selectNodeContents(span);
            selection.removeAllRanges();
            selection.addRange(nextRange);
        }

        element.dispatchEvent(new Event("input", { bubbles: true }));
        return true;
    }

    function getInlineFormattingTarget() {
        var active = document.activeElement;

        if (active === fieldLabelInput || active === newTemplateContent) {
            return active;
        }

        if (activeTextEditor && activeTextEditor.isContentEditable) {
            return activeTextEditor;
        }

        return fieldLabelInput || newTemplateContent || null;
    }

    function applyInlineTagToActiveTarget(openTag, closeTag) {
        var target = getInlineFormattingTarget();
        if (!target) {
            return;
        }

        if (target.isContentEditable) {
            if (!wrapSelectionInContentEditable(target, openTag)) {
                setStatus("Select text in the template box first to apply word style.");
                return;
            }
            setStatus("Word style applied.");
            return;
        }

        wrapSelectionWithTags(target, openTag, closeTag);
        setStatus("Word style tag inserted.");
    }

    function keepInlineSelectionOnMouseDown(control) {
        if (!control) {
            return;
        }

        control.addEventListener("mousedown", function (event) {
            if (activeTextEditor && activeTextEditor.isContentEditable) {
                event.preventDefault();
            }
        });
    }

    function escapeHtml(value) {
        return String(value || "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function styleOpenTagToSpanHtml(tagName, fontToken, forEditor) {
        if (tagName === "b") {
            return forEditor
                ? "<span data-inline-tag=\"b\" style=\"font-weight: 700;\">"
                : "<span style=\"font-weight: 700;\">";
        }

        if (tagName === "i") {
            return forEditor
                ? "<span data-inline-tag=\"i\" style=\"font-style: italic;\">"
                : "<span style=\"font-style: italic;\">";
        }

        if (tagName === "bi") {
            return forEditor
                ? "<span data-inline-tag=\"bi\" style=\"font-style: italic; font-weight: 700;\">"
                : "<span style=\"font-style: italic; font-weight: 700;\">";
        }

        if (tagName === "font") {
            var fontFamily = normalizeFontToken(fontToken);
            var cssFamily = previewFontFamilyCss(fontFamily);
            return forEditor
                ? "<span data-inline-tag=\"font\" data-font-family=\"" + escapeHtml(fontFamily) + "\" style=\"font-family: " + cssFamily + ";\">"
                : "<span style=\"font-family: " + cssFamily + ";\">";
        }

        if (tagName === "left" || tagName === "center" || tagName === "right" || tagName === "justify") {
            return forEditor
                ? "<span data-inline-tag=\"" + tagName + "\" style=\"display: block; text-align: " + tagName + ";\">"
                : "<span style=\"display: block; text-align: " + tagName + ";\">";
        }

        return "";
    }

    function templateTaggedTextToHtml(rawText, forEditor) {
        var text = String(rawText || "").replace(/\r/g, "");
        var html = "";
        var stack = [];
        var pattern = /\[(\/?)(b|i|bi|font|left|center|right|justify)(?:=([^\]]+))?\]/gi;
        var cursor = 0;
        var match = null;

        while ((match = pattern.exec(text)) !== null) {
            if (match.index > cursor) {
                html += escapeHtml(text.slice(cursor, match.index));
            }

            var isClosing = match[1] === "/";
            var tagName = String(match[2] || "").toLowerCase();

            if (!isClosing) {
                html += styleOpenTagToSpanHtml(tagName, match[3] || "", forEditor);
                stack.push(tagName);
            } else {
                for (var idx = stack.length - 1; idx >= 0; idx -= 1) {
                    html += "</span>";
                    var popped = stack.pop();
                    if (popped === tagName) {
                        break;
                    }
                }
            }

            cursor = pattern.lastIndex;
        }

        if (cursor < text.length) {
            html += escapeHtml(text.slice(cursor));
        }

        while (stack.length > 0) {
            html += "</span>";
            stack.pop();
        }

        return html.replace(/\n/g, "<br>");
    }

    function templatePreviewHtml(rawText) {
        return templateTaggedTextToHtml(rawText, false);
    }

    function templateEditorHtml(rawText) {
        return templateTaggedTextToHtml(rawText, true);
    }

    function editableTemplateHtmlToTaggedText(element) {
        function walk(node) {
            if (!node) {
                return "";
            }

            if (node.nodeType === Node.TEXT_NODE) {
                return String(node.nodeValue || "").replace(/\u00A0/g, " ");
            }

            if (node.nodeType !== Node.ELEMENT_NODE) {
                return "";
            }

            var el = node;
            var tagName = (el.tagName || "").toLowerCase();

            if (tagName === "br") {
                return "\n";
            }

            var inner = "";
            Array.prototype.forEach.call(el.childNodes || [], function (child) {
                inner += walk(child);
            });

            var inlineTag = String(el.getAttribute("data-inline-tag") || "").toLowerCase();
            if (inlineTag === "b" || inlineTag === "i" || inlineTag === "bi") {
                return "[" + inlineTag + "]" + inner + "[/" + inlineTag + "]";
            }

            if (inlineTag === "font") {
                var family = normalizeFontToken(el.getAttribute("data-font-family") || "default");
                return "[font=" + family + "]" + inner + "[/font]";
            }

            if (inlineTag === "left" || inlineTag === "center" || inlineTag === "right" || inlineTag === "justify") {
                return "[" + inlineTag + "]" + inner + "[/" + inlineTag + "]";
            }

            if (tagName === "div" || tagName === "p") {
                return inner + "\n";
            }

            return inner;
        }

        var text = "";
        Array.prototype.forEach.call(element.childNodes || [], function (child) {
            text += walk(child);
        });

        return text;
    }

    function insertLineBreakInEditable(element) {
        if (!element || !element.isContentEditable) {
            return;
        }

        var range = getSelectionRangeInElement(element);
        if (!range) {
            return;
        }

        range.deleteContents();
        var br = document.createElement("br");
        range.insertNode(br);
        range.setStartAfter(br);
        range.collapse(true);

        var selection = window.getSelection();
        if (selection) {
            selection.removeAllRanges();
            selection.addRange(range);
        }

        element.dispatchEvent(new Event("input", { bubbles: true }));
    }

    function styleHasBold(fontStyle) {
        return fontStyle === "bold" || fontStyle === "bold_italic";
    }

    function styleHasItalic(fontStyle) {
        return fontStyle === "italic" || fontStyle === "bold_italic";
    }

    function composeFontStyle(isBold, isItalic) {
        if (isBold && isItalic) {
            return "bold_italic";
        }
        if (isBold) {
            return "bold";
        }
        if (isItalic) {
            return "italic";
        }
        return "regular";
    }

    function previewFontFamilyCss(fontFamily) {
        var key = String(fontFamily || "default").toLowerCase();
        if (!fontOptionMap[key]) {
            key = "default";
        }

        return String(fontOptionMap[key].preview_family || "Segoe UI, Trebuchet MS, sans-serif");
    }

    function previewFontFamilyCanvas(fontFamily) {
        var key = String(fontFamily || "default").toLowerCase();
        if (!fontOptionMap[key]) {
            key = "default";
        }

        return String(fontOptionMap[key].canvas_family || "Segoe UI");
    }

    function measureTextWidthWithOptions(text, fontPx, options) {
        if (!previewMeasureContext) {
            return (String(text || "").length * fontPx) / 1.8;
        }

        var fontStyle = options.font_style || "regular";
        var fontWeight = styleHasBold(fontStyle) ? "700" : "500";
        var fontItalic = styleHasItalic(fontStyle) ? "italic " : "";
        previewMeasureContext.font = fontItalic + fontWeight + " " + fontPx + "px " + previewFontFamilyCanvas(options.font_family || "default");

        var raw = String(text || "");
        var width = previewMeasureContext.measureText(raw).width;
        var spaces = (raw.match(/ /g) || []).length;
        var wordSpacing = clampNumber(options.word_spacing, 0, 0, 30);

        return width + (spaces * wordSpacing);
    }

    function measureWrappedLineCount(text, fontPx, maxWidth, options) {
        var content = String(text || "").replace(/\r/g, "");
        if (content === "") {
            return 1;
        }

        var lines = 0;
        var paragraphs = content.split("\n");

        paragraphs.forEach(function (paragraph) {
            if (paragraph.trim() === "") {
                lines += 1;
                return;
            }

            var words = paragraph.split(/\s+/).filter(function (word) {
                return word !== "";
            });

            var current = "";
            words.forEach(function (word) {
                var candidate = current ? (current + " " + word) : word;
                if (measureTextWidthWithOptions(candidate, fontPx, options) <= maxWidth) {
                    current = candidate;
                    return;
                }

                if (current) {
                    lines += 1;
                    current = "";
                }

                if (measureTextWidthWithOptions(word, fontPx, options) <= maxWidth) {
                    current = word;
                    return;
                }

                var piece = "";
                word.split("").forEach(function (ch) {
                    var chunkCandidate = piece + ch;
                    if (piece && measureTextWidthWithOptions(chunkCandidate, fontPx, options) > maxWidth) {
                        lines += 1;
                        piece = ch;
                    } else {
                        piece = chunkCandidate;
                    }
                });
                current = piece;
            });

            if (current) {
                lines += 1;
            }
        });

        return Math.max(1, lines);
    }

    function fitTemplatePreviewFontSize(text, preferredFontPx, displayWidth, displayHeight, options) {
        var minPx = 8;
        var maxWidth = Math.max(40, displayWidth - 12);
        var maxHeight = Math.max(20, displayHeight - 20);
        var start = Math.max(minPx, Math.min(72, Math.round(preferredFontPx)));

        for (var px = start; px >= minPx; px -= 1) {
            var lineCount = measureWrappedLineCount(text, px, maxWidth, options);
            var lineSpacing = clampNumber(options.line_spacing, 1.25, 1, 3);
            var lineHeightPx = Math.max(10, Math.round(px * lineSpacing));
            if ((lineCount * lineHeightPx) <= maxHeight) {
                return px;
            }
        }

        return minPx;
    }

    function getRowOptions(row) {
        if (!row.options || typeof row.options !== "object") {
            row.options = {
                text_align: row.align === "center" ? "center" : "left",
                font_style: "regular",
                font_family: "default",
                line_spacing: 1.25,
                word_spacing: 0
            };
        }
        return row.options;
    }

    function updateToolbarState(row) {
        [
            toggleBoldButton,
            toggleItalicButton,
            toggleUnderlineButton,
            alignLeftButton,
            alignCenterButton,
            alignRightButton,
            alignJustifyButton,
            quickBoldButton,
            quickItalicButton,
            quickAlignLeftButton,
            quickAlignCenterButton,
            quickAlignRightButton,
            quickAlignJustifyButton
        ].forEach(function (button) {
            if (button) {
                button.classList.remove("active");
            }
        });

        if (!row) {
            if (quickFontFamilyInput) {
                quickFontFamilyInput.value = "default";
            }
            if (quickFontSizeInput) {
                quickFontSizeInput.value = "32";
            }
            if (quickColorInput) {
                quickColorInput.value = "#000000";
            }
            return;
        }

        var options = getRowOptions(row);
        var fontStyle = options.font_style || "regular";
        var textAlign = options.text_align || "left";

        if (quickFontFamilyInput) {
            quickFontFamilyInput.value = options.font_family || "default";
        }
        if (quickFontSizeInput) {
            quickFontSizeInput.value = String(row.font_size || 32);
        }
        if (quickColorInput) {
            quickColorInput.value = sanitizeColor(row.color_hex || "#000000");
        }

        if (toggleUnderlineButton && !!row.underline) {
            toggleUnderlineButton.classList.add("active");
        }

        if (toggleBoldButton && styleHasBold(fontStyle)) {
            toggleBoldButton.classList.add("active");
        }
        if (quickBoldButton && styleHasBold(fontStyle)) {
            quickBoldButton.classList.add("active");
        }
        if (toggleItalicButton && styleHasItalic(fontStyle)) {
            toggleItalicButton.classList.add("active");
        }
        if (quickItalicButton && styleHasItalic(fontStyle)) {
            quickItalicButton.classList.add("active");
        }
        if (alignLeftButton && textAlign === "left") {
            alignLeftButton.classList.add("active");
        }
        if (quickAlignLeftButton && textAlign === "left") {
            quickAlignLeftButton.classList.add("active");
        }
        if (alignCenterButton && textAlign === "center") {
            alignCenterButton.classList.add("active");
        }
        if (quickAlignCenterButton && textAlign === "center") {
            quickAlignCenterButton.classList.add("active");
        }
        if (alignRightButton && textAlign === "right") {
            alignRightButton.classList.add("active");
        }
        if (quickAlignRightButton && textAlign === "right") {
            quickAlignRightButton.classList.add("active");
        }
        if (alignJustifyButton && textAlign === "justify") {
            alignJustifyButton.classList.add("active");
        }
        if (quickAlignJustifyButton && textAlign === "justify") {
            quickAlignJustifyButton.classList.add("active");
        }
    }

    function applyTemplatePreviewStyle(markerMain, row, displayWidth, displayHeight, scale) {
        var options = getRowOptions(row);
        var previewFontPx = fitTemplatePreviewFontSize(
            row.label || "",
            row.font_size * scale.y,
            displayWidth,
            displayHeight,
            options
        );

        markerMain.style.fontSize = previewFontPx + "px";
        markerMain.style.lineHeight = String(clampNumber(options.line_spacing, 1.25, 1, 3));
        markerMain.style.wordSpacing = Math.round(clampNumber(options.word_spacing, 0, 0, 30)) + "px";
        markerMain.style.textAlign = options.text_align === "justify" ? "justify" : options.text_align;
        markerMain.style.fontFamily = previewFontFamilyCss(options.font_family);
        markerMain.style.fontWeight = styleHasBold(options.font_style) ? "700" : "500";
        markerMain.style.fontStyle = styleHasItalic(options.font_style) ? "italic" : "normal";
        markerMain.style.textDecoration = row.underline ? "underline" : "none";
    }

    function updateActiveMarkerVisual() {
        var markers = markersLayer.querySelectorAll(".mapping-marker");
        markers.forEach(function (node) {
            if ((node.getAttribute("data-id") || "") === selectedId) {
                node.classList.add("active");
            } else {
                node.classList.remove("active");
            }
        });
    }

    function hasSelectionInActiveEditor() {
        if (!editingState || !editingState.markerMain || !editingState.markerMain.isContentEditable) {
            return false;
        }

        var range = getSelectionRangeInElement(editingState.markerMain);
        return !!(range && !range.collapsed);
    }

    function updateFloatingToolbarPosition() {
        if (!floatingInlineToolbar || floatingInlineToolbar.hidden || !selectedId) {
            return;
        }

        var marker = markersLayer.querySelector('.mapping-marker[data-id="' + selectedId + '"]');
        if (!marker) {
            return;
        }

        var panel = floatingInlineToolbar.parentElement;
        if (!panel) {
            return;
        }

        var markerRect = marker.getBoundingClientRect();
        var panelRect = panel.getBoundingClientRect();

        var toolbarWidth = floatingInlineToolbar.offsetWidth;
        var toolbarHeight = floatingInlineToolbar.offsetHeight;

        var left = markerRect.left - panelRect.left + (markerRect.width / 2) - (toolbarWidth / 2);
        var top = markerRect.top - panelRect.top - toolbarHeight - 8;

        if (top < 8) {
            top = markerRect.bottom - panelRect.top + 8;
        }

        left = clamp(left, 8, Math.max(8, panel.clientWidth - toolbarWidth - 8));
        top = clamp(top, 8, Math.max(8, panel.clientHeight - toolbarHeight - 8));

        floatingInlineToolbar.style.left = left + "px";
        floatingInlineToolbar.style.top = top + "px";
    }

    function updateFloatingToolbarVisibility() {
        if (!floatingInlineToolbar) {
            return;
        }

        var shouldShow = hasSelectionInActiveEditor();
        floatingInlineToolbar.hidden = !shouldShow;

        if (shouldShow) {
            updateFloatingToolbarPosition();
        }
    }

    function renderMarkers() {
        markersLayer.innerHTML = "";

        var scale = getScale();
        var imageRect = image.getBoundingClientRect();
        var canvasRect = canvas.getBoundingClientRect();
        var offsetLeft = imageRect.left - canvasRect.left + canvas.scrollLeft;
        var offsetTop = imageRect.top - canvasRect.top + canvas.scrollTop;

        markersLayer.style.position = "absolute";
        markersLayer.style.left = "0";
        markersLayer.style.top = "0";
        markersLayer.style.width = "100%";
        markersLayer.style.height = "100%";

        mappings.forEach(function (row) {
            var templateMode = isTemplateMapping(row);
            var marker = document.createElement("div");
            marker.className = "mapping-marker" + (row.id === selectedId ? " active" : "");
            if (templateMode) {
                marker.classList.add("template-marker");
            }

            marker.dataset.id = row.id;
            marker.style.left = offsetLeft + toDisplayX(row.x_pos) + "px";
            marker.style.top = offsetTop + toDisplayY(row.y_pos) + "px";

            var displayWidth = Math.max(130, toDisplayX(row.max_width));
            var displayHeight = Math.max(34, toDisplayY(Math.max(row.line_height, row.font_size + 8)));
            marker.style.width = displayWidth + "px";
            marker.style.minHeight = displayHeight + "px";

            var markerMain = document.createElement("div");
            markerMain.className = "marker-main";
            if (templateMode) {
                markerMain.innerHTML = templatePreviewHtml(row.label || row.field_key || "Text");
            } else {
                markerMain.textContent = summarizeTextBoxLabel(row);
            }

            if (templateMode) {
                applyTemplatePreviewStyle(markerMain, row, displayWidth, displayHeight, scale);
            } else {
                markerMain.style.fontSize = Math.max(10, Math.min(34, row.font_size * scale.y)) + "px";
                markerMain.style.wordSpacing = "normal";
                markerMain.style.fontStyle = "normal";
                markerMain.style.fontWeight = "500";
                markerMain.style.fontFamily = "Segoe UI, Trebuchet MS, sans-serif";
                markerMain.style.textAlign = "left";
                markerMain.style.textDecoration = row.underline ? "underline" : "none";
            }

            var markerMeta = document.createElement("div");
            markerMeta.className = "marker-meta";
            markerMeta.textContent = (templateMode ? "Template" : (row.field_key || "Field")) + " | " + row.x_pos + ", " + row.y_pos;

            var resizeHandle = document.createElement("div");
            resizeHandle.className = "mapping-resize-handle";
            resizeHandle.title = "Resize text box";

            marker.appendChild(markerMain);
            marker.appendChild(markerMeta);
            marker.appendChild(resizeHandle);

            marker.addEventListener("mousedown", function (event) {
                if (markerMain.isContentEditable) {
                    event.stopPropagation();
                    return;
                }

                selectedId = row.id;
                syncSelectedPanel();
                updateActiveMarkerVisual();
                updateFloatingToolbarVisibility();

                dragState = {
                    id: row.id,
                    startX: event.clientX,
                    startY: event.clientY,
                    originX: row.x_pos,
                    originY: row.y_pos
                };
                dragDirty = false;

                event.preventDefault();
            });

            marker.addEventListener("click", function (event) {
                if (markerMain.isContentEditable) {
                    event.stopPropagation();
                    return;
                }

                selectedId = row.id;
                syncSelectedPanel();
                updateActiveMarkerVisual();
                updateFloatingToolbarVisibility();
                event.stopPropagation();
            });

            markerMain.addEventListener("mousedown", function (event) {
                if (markerMain.isContentEditable) {
                    event.stopPropagation();
                }
            });

            markerMain.addEventListener("click", function (event) {
                if (markerMain.isContentEditable) {
                    event.stopPropagation();
                }
            });

            resizeHandle.addEventListener("mousedown", function (event) {
                selectedId = row.id;
                syncSelectedPanel();
                updateActiveMarkerVisual();

                resizeState = {
                    id: row.id,
                    startX: event.clientX,
                    startY: event.clientY,
                    originWidth: row.max_width,
                    originLineHeight: row.line_height,
                    originFontSize: row.font_size
                };
                resizeDirty = false;

                setStatus("Resizing text box...");
                event.preventDefault();
                event.stopPropagation();
            });

            markerMain.addEventListener("dblclick", function (event) {
                event.stopPropagation();
                selectedId = row.id;

                if (editingState && editingState.markerMain !== markerMain) {
                    exitEditingMode();
                }

                activeTextEditor = markerMain;
                updateActiveMarkerVisual();

                if (templateMode) {
                    markerMain.innerHTML = templateEditorHtml(row.label || row.field_key || "Text");
                }

                marker.classList.add("editing");
                markerMain.contentEditable = "true";
                editingState = {
                    marker: marker,
                    markerMain: markerMain,
                    row: row,
                    templateMode: templateMode,
                };
                clearPendingOutsideExit();
                markerMain.focus();
                setStatus("Edit mode active: select words inside this box, then use top toolbar.");

                var selection = window.getSelection();
                if (selection) {
                    var range = document.createRange();
                    range.selectNodeContents(markerMain);
                    range.collapse(false);
                    selection.removeAllRanges();
                    selection.addRange(range);
                }

                updateFloatingToolbarVisibility();
            });

            markerMain.addEventListener("focus", function () {
                activeTextEditor = markerMain;
                if (editingState && editingState.markerMain === markerMain) {
                    clearPendingOutsideExit();
                }
            });

            markerMain.addEventListener("keydown", function (event) {
                if (!templateMode && event.key === "Enter") {
                    event.preventDefault();
                    markerMain.blur();
                }

                if (templateMode && event.key === "Enter") {
                    event.preventDefault();
                    insertLineBreakInEditable(markerMain);
                }

                if (templateMode && event.key === "Escape") {
                    event.preventDefault();
                    exitEditingMode();
                }
            });

            markerMain.addEventListener("input", function () {
                if (!templateMode) {
                    return;
                }
                applyTemplatePreviewStyle(markerMain, row, displayWidth, displayHeight, scale);
            });

            markerMain.addEventListener("blur", function () {
                if (!editingState || editingState.markerMain !== markerMain) {
                    return;
                }

                var related = document.activeElement;
                if ((related && marker.contains(related)) || isToolbarTarget(related)) {
                    markerMain.focus();
                    return;
                }

                exitEditingMode();
            });

            markersLayer.appendChild(marker);
        });

        updateFloatingToolbarPosition();
        if (typeof renderLayers === "function") {
            renderLayers();
        }
    }

    function syncSelectedPanel() {
        var selected = findMapping(selectedId);
        if (!selected) {
            if (selectedFieldPanel) {
                selectedFieldPanel.classList.add("is-hidden");
            }
            if (propertyEmptyState) {
                propertyEmptyState.classList.remove("hidden");
            }

            fieldKeyInput.value = "";
            fieldLabelInput.value = "";
            fontSizeInput.value = "";
            alignInput.value = "left";
            if (fontFamilyInput) {
                fontFamilyInput.value = "default";
            }
            if (fontStyleInput) {
                fontStyleInput.value = "regular";
            }
            colorInput.value = "#000000";
            maxWidthInput.value = "900";
            if (xPosInput) {
                xPosInput.value = "";
            }
            if (yPosInput) {
                yPosInput.value = "";
            }
            lineHeightInput.value = "42";
            if (lineSpacingInput) {
                lineSpacingInput.value = "1.25";
            }
            if (wordSpacingInput) {
                wordSpacingInput.value = "0";
            }
            underlineInput.checked = false;
            updateToolbarState(null);
            updateFloatingToolbarVisibility();
            return;
        }

        if (selectedFieldPanel) {
            selectedFieldPanel.classList.remove("is-hidden");
        }
        if (propertyEmptyState) {
            propertyEmptyState.classList.add("hidden");
        }

        var options = getRowOptions(selected);

        fieldKeyInput.value = selected.field_key;
        fieldLabelInput.value = selected.label;
        fontSizeInput.value = String(selected.font_size);
        alignInput.value = options.text_align || "left";
        if (fontFamilyInput) {
            fontFamilyInput.value = options.font_family || "default";
        }
        if (fontStyleInput) {
            fontStyleInput.value = options.font_style || "regular";
        }
        colorInput.value = sanitizeColor(selected.color_hex);
        maxWidthInput.value = String(selected.max_width);
        if (xPosInput) {
            xPosInput.value = String(selected.x_pos);
        }
        if (yPosInput) {
            yPosInput.value = String(selected.y_pos);
        }
        lineHeightInput.value = String(selected.line_height);
        if (lineSpacingInput) {
            lineSpacingInput.value = String(clampNumber(options.line_spacing, 1.25, 1, 3));
        }
        if (wordSpacingInput) {
            wordSpacingInput.value = String(Math.round(clampNumber(options.word_spacing, 0, 0, 30)));
        }
        underlineInput.checked = !!selected.underline;
        updateToolbarState(selected);
        updateFloatingToolbarVisibility();
    }

    function savePayloadToInput() {
        var payload = mappings.map(function (row, index) {
            var options = getRowOptions(row);
            return {
                field_key: row.field_key,
                label: row.label,
                options_json: JSON.stringify({
                    text_align: options.text_align,
                    font_style: options.font_style,
                    font_family: options.font_family,
                    line_spacing: clampNumber(options.line_spacing, 1.25, 1, 3),
                    word_spacing: Math.round(clampNumber(options.word_spacing, 0, 0, 30))
                }),
                x_pos: Math.round(row.x_pos),
                y_pos: Math.round(row.y_pos),
                font_size: Math.round(row.font_size),
                align: row.align,
                color_hex: sanitizeColor(row.color_hex),
                max_width: Math.round(row.max_width),
                line_height: Math.round(row.line_height),
                underline: row.underline ? 1 : 0,
                sort_order: index
            };
        });

        mappingsJson.value = JSON.stringify(payload);
    }

    function applyPanelChange() {
        var selected = findMapping(selectedId);
        if (!selected) {
            return;
        }

        var options = getRowOptions(selected);

        selected.field_key = fieldKeyInput.value.trim();
        selected.label = fieldLabelInput.value.trim() || selected.field_key || "Field";
        selected.font_size = clampNumber(fontSizeInput.value, 32, 8, 180);
        selected.color_hex = sanitizeColor(colorInput.value || "#000000");
        selected.max_width = parseRawNumber(maxWidthInput.value, 900);
        if (xPosInput) {
            selected.x_pos = Math.round(clamp(parseRawNumber(xPosInput.value, selected.x_pos), 0, getScale().naturalWidth));
        }
        if (yPosInput) {
            selected.y_pos = Math.round(clamp(parseRawNumber(yPosInput.value, selected.y_pos), 0, getScale().naturalHeight));
        }
        selected.line_height = parseRawNumber(lineHeightInput.value, 42);
        selected.underline = !!underlineInput.checked;

        options.text_align = ["left", "center", "right", "justify"].indexOf(alignInput.value) >= 0
            ? alignInput.value
            : "left";
        options.font_style = ["regular", "bold", "italic", "bold_italic"].indexOf(fontStyleInput.value) >= 0
            ? fontStyleInput.value
            : "regular";
        options.font_family = allowedFontFamilies[String(fontFamilyInput.value || "").toLowerCase()]
            ? fontFamilyInput.value
            : "default";
        options.line_spacing = clampNumber(lineSpacingInput.value, 1.25, 1, 3);
        options.word_spacing = clampNumber(wordSpacingInput.value, 0, 0, 30);

        selected.align = options.text_align === "center" ? "center" : "left";

        updateToolbarState(selected);
        renderMarkers();
        updateFloatingToolbarPosition();
        savePayloadToInput();
        scheduleHistoryCommit();
    }

    [
        fieldKeyInput,
        fieldLabelInput,
        fontSizeInput,
        alignInput,
        fontFamilyInput,
        fontStyleInput,
        colorInput,
        maxWidthInput,
        xPosInput,
        yPosInput,
        lineHeightInput,
        lineSpacingInput,
        wordSpacingInput,
        underlineInput
    ].forEach(function (control) {
        if (!control) {
            return;
        }
        control.addEventListener("input", applyPanelChange);
        control.addEventListener("change", applyPanelChange);
    });

    [fieldLabelInput, newTemplateContent].forEach(function (editor) {
        if (!editor) {
            return;
        }

        editor.addEventListener("focus", function () {
            activeTextEditor = editor;
        });
    });

    function setSelectedAlign(value) {
        if (activeTextEditor && activeTextEditor.isContentEditable) {
            var range = getSelectionRangeInElement(activeTextEditor);
            if (range && !range.collapsed) {
                if (wrapSelectionInContentEditable(activeTextEditor, "[" + value + "]")) {
                    setStatus("Word alignment applied: " + value + ".");
                    return;
                }
            }
        }

        var selected = findMapping(selectedId);
        if (!selected) {
            return;
        }
        alignInput.value = value;
        applyPanelChange();
    }

    function toggleSelectedBoldStyle() {
        var selected = findMapping(selectedId);
        if (!selected) {
            return;
        }

        var options = getRowOptions(selected);
        var bold = styleHasBold(options.font_style);
        var italic = styleHasItalic(options.font_style);
        options.font_style = composeFontStyle(!bold, italic);
        fontStyleInput.value = options.font_style;
        applyPanelChange();
    }

    function toggleSelectedItalicStyle() {
        var selected = findMapping(selectedId);
        if (!selected) {
            return;
        }

        var options = getRowOptions(selected);
        var bold = styleHasBold(options.font_style);
        var italic = styleHasItalic(options.font_style);
        options.font_style = composeFontStyle(bold, !italic);
        fontStyleInput.value = options.font_style;
        applyPanelChange();
    }

    if (toggleBoldButton) {
        toggleBoldButton.addEventListener("click", toggleSelectedBoldStyle);
    }

    if (toggleItalicButton) {
        toggleItalicButton.addEventListener("click", toggleSelectedItalicStyle);
    }

    if (quickBoldButton) {
        quickBoldButton.addEventListener("click", toggleSelectedBoldStyle);
    }

    if (quickItalicButton) {
        quickItalicButton.addEventListener("click", toggleSelectedItalicStyle);
    }

    if (toggleUnderlineButton) {
        toggleUnderlineButton.addEventListener("click", function () {
            var selected = findMapping(selectedId);
            if (!selected) {
                return;
            }

            selected.underline = !selected.underline;
            underlineInput.checked = !!selected.underline;
            applyPanelChange();
        });
    }

    if (alignLeftButton) {
        alignLeftButton.addEventListener("click", function () {
            setSelectedAlign("left");
        });
    }
    if (alignCenterButton) {
        alignCenterButton.addEventListener("click", function () {
            setSelectedAlign("center");
        });
    }
    if (alignRightButton) {
        alignRightButton.addEventListener("click", function () {
            setSelectedAlign("right");
        });
    }
    if (alignJustifyButton) {
        alignJustifyButton.addEventListener("click", function () {
            setSelectedAlign("justify");
        });
    }

    if (quickAlignLeftButton) {
        quickAlignLeftButton.addEventListener("click", function () {
            setSelectedAlign("left");
        });
    }
    if (quickAlignCenterButton) {
        quickAlignCenterButton.addEventListener("click", function () {
            setSelectedAlign("center");
        });
    }
    if (quickAlignRightButton) {
        quickAlignRightButton.addEventListener("click", function () {
            setSelectedAlign("right");
        });
    }
    if (quickAlignJustifyButton) {
        quickAlignJustifyButton.addEventListener("click", function () {
            setSelectedAlign("justify");
        });
    }

    if (quickFontFamilyInput) {
        quickFontFamilyInput.addEventListener("change", function () {
            if (!fontFamilyInput) {
                return;
            }
            fontFamilyInput.value = quickFontFamilyInput.value;
            applyPanelChange();
        });
    }

    if (quickFontSizeInput) {
        quickFontSizeInput.addEventListener("input", function () {
            if (!fontSizeInput) {
                return;
            }
            fontSizeInput.value = quickFontSizeInput.value;
            applyPanelChange();
        });
    }

    if (quickColorInput) {
        quickColorInput.addEventListener("input", function () {
            if (!colorInput) {
                return;
            }
            colorInput.value = quickColorInput.value;
            applyPanelChange();
        });
    }

    placeholderButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            var token = button.getAttribute("data-token") || "";
            if (!token) {
                return;
            }

            var active = document.activeElement;
            if (active === fieldLabelInput || active === newTemplateContent) {
                insertTokenAtCaret(active, token);
            } else {
                insertTokenAtCaret(newTemplateContent, token);
            }
        });
    });

    inlineStyleButtons.forEach(function (button) {
        keepInlineSelectionOnMouseDown(button);
        button.addEventListener("click", function () {
            var openTag = button.getAttribute("data-open") || "";
            var closeTag = button.getAttribute("data-close") || "";
            if (!openTag || !closeTag) {
                return;
            }

            applyInlineTagToActiveTarget(openTag, closeTag);
        });
    });

    if (quickInlineBoldButton) {
        keepInlineSelectionOnMouseDown(quickInlineBoldButton);
        quickInlineBoldButton.addEventListener("click", function () {
            applyInlineTagToActiveTarget("[b]", "[/b]");
        });
    }

    if (quickInlineItalicButton) {
        keepInlineSelectionOnMouseDown(quickInlineItalicButton);
        quickInlineItalicButton.addEventListener("click", function () {
            applyInlineTagToActiveTarget("[i]", "[/i]");
        });
    }

    if (quickInlineBoldItalicButton) {
        keepInlineSelectionOnMouseDown(quickInlineBoldItalicButton);
        quickInlineBoldItalicButton.addEventListener("click", function () {
            applyInlineTagToActiveTarget("[bi]", "[/bi]");
        });
    }

    if (quickInlineFontApplyButton) {
        keepInlineSelectionOnMouseDown(quickInlineFontApplyButton);
        quickInlineFontApplyButton.addEventListener("click", function () {
            var fontValue = quickInlineFontFamilyInput ? String(quickInlineFontFamilyInput.value || "default") : "default";
            if (!allowedFontFamilies[String(fontValue).toLowerCase()]) {
                fontValue = "default";
            }

            applyInlineTagToActiveTarget("[font=" + fontValue + "]", "[/font]");
        });
    }

    if (quickInlineFontFamilyInput) {
        quickInlineFontFamilyInput.addEventListener("change", function () {
            if (activeTextEditor && activeTextEditor.isContentEditable) {
                activeTextEditor.focus();
            }
        });
    }

    if (undoMappingButton) {
        undoMappingButton.addEventListener("click", function () {
            if (historyIndex <= 0) {
                return;
            }

            historyIndex -= 1;
            applyEditorSnapshot(historyStack[historyIndex].snapshot);
            updateHistoryControls();
            setSaveState("Unsaved changes");
            setStatus("Undo applied.");
        });
    }

    if (redoMappingButton) {
        redoMappingButton.addEventListener("click", function () {
            if (historyIndex >= historyStack.length - 1) {
                return;
            }

            historyIndex += 1;
            applyEditorSnapshot(historyStack[historyIndex].snapshot);
            updateHistoryControls();
            setSaveState("Unsaved changes");
            setStatus("Redo applied.");
        });
    }

    if (zoomOutButton) {
        zoomOutButton.addEventListener("click", function () {
            setZoom(zoomLevel - 0.1);
        });
    }

    if (zoomInButton) {
        zoomInButton.addEventListener("click", function () {
            setZoom(zoomLevel + 0.1);
        });
    }

    if (zoomResetButton) {
        zoomResetButton.addEventListener("click", function () {
            setZoom(1);
        });
    }

    if (gridToggleButton) {
        gridToggleButton.addEventListener("click", function () {
            canvas.classList.toggle("grid-enabled");
            gridToggleButton.classList.toggle("active");
        });
    }

    [quickAlignLeftButton, quickAlignCenterButton, quickAlignRightButton, quickAlignJustifyButton].forEach(function (button) {
        keepInlineSelectionOnMouseDown(button);
    });

    function placeElementImmediately(pendingField) {
        var scale = getScale();
        var width = pendingField.max_width || 900;
        var height = pendingField.line_height || 120;
        
        var actualX = Math.round(scale.naturalWidth / 2 - width / 2);
        var actualY = Math.round(scale.naturalHeight / 2 - height / 2);
        
        if (actualX < 0) actualX = 50;
        if (actualY < 0) actualY = 150;
        
        while (mappings.some(function(m) { return Math.abs(m.x_pos - actualX) < 15 && Math.abs(m.y_pos - actualY) < 15; })) {
            actualX += 20;
            actualY += 20;
        }

        var created = {
            id: "m" + Date.now() + "_" + Math.random().toString(36).slice(2, 8),
            field_key: pendingField.field_key,
            label: pendingField.label,
            x_pos: actualX,
            y_pos: actualY,
            font_size: pendingField.font_size,
            align: pendingField.align,
            color_hex: pendingField.color_hex,
            max_width: pendingField.max_width,
            line_height: pendingField.line_height,
            underline: pendingField.underline,
            options: pendingField.options
        };

        mappings.push(created);
        selectedId = created.id;
        pendingNewField = null;
        setStatus("Added field: " + (created.field_key || "Text"));

        renderMarkers();
        syncSelectedPanel();
        savePayloadToInput();
        setSaveState("Unsaved changes");
        commitHistory();
    }

    function armPlacement(textValue, optionsOverride) {
        var options = {
            text_align: "left",
            font_style: "regular",
            font_family: "default",
            line_spacing: 1.25,
            word_spacing: 0
        };

        if (optionsOverride && typeof optionsOverride === "object") {
            Object.keys(optionsOverride).forEach(function (key) {
                options[key] = optionsOverride[key];
            });
        }

        var isDynamic = String(textValue).indexOf('{') === 0;

        var newField = {
            field_key: isDynamic ? String(textValue).replace(/[{}]/g, "") : generateTemplateFieldKey(),
            label: String(textValue || "New text"),
            font_size: 32,
            align: "left",
            color_hex: "#000000",
            max_width: 900,
            line_height: 120,
            underline: false,
            options: options
        };

        placeElementImmediately(newField);
    }

    if (addTextElementButton) {
        addTextElementButton.addEventListener("click", function () {
            armPlacement("New text");
        });
    }

    if (uploadTemplateImageButton && templateUploadInput) {
        uploadTemplateImageButton.addEventListener("click", function () {
            templateUploadInput.click();
        });
    }

    if (templateUploadInput && templateUploadForm) {
        templateUploadInput.addEventListener("change", function () {
            if (!templateUploadInput.files || templateUploadInput.files.length === 0) {
                return;
            }

            var saveStateText = editorSaveState ? String(editorSaveState.textContent || "") : "";
            if (saveStateText.toLowerCase().indexOf("unsaved") !== -1) {
                var shouldContinue = window.confirm("You have unsaved mapping changes. Uploading a new template will reload the editor. Continue?");
                if (!shouldContinue) {
                    templateUploadInput.value = "";
                    return;
                }
            }

            templateUploadForm.submit();
        });
    }

    dynamicFieldButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            var token = String(button.getAttribute("data-token") || "{name}").trim();
            if (!token) {
                token = "{name}";
            }

            armPlacement(token, {
                font_style: "bold"
            });
        });
    });

    editorActionButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            var action = button.getAttribute("data-editor-action") || "";

            if (action === "addText" && addTextElementButton) {
                addTextElementButton.click();
                return;
            }

            if (action === "toggleGrid" && gridToggleButton) {
                gridToggleButton.click();
                return;
            }

            if (action === "zoomReset" && zoomResetButton) {
                zoomResetButton.click();
                return;
            }

            if (action === "addCustomText" && addTemplateBoxButton) {
                if (newTemplateContent && String(newTemplateContent.value || "").trim() === "") {
                    newTemplateContent.value = "This certificate confirms {name} - {verify_code}";
                }
                addTemplateBoxButton.click();
                return;
            }
        });
    });

    if (addFieldButton && newFieldKey && newFieldLabel) {
        addFieldButton.addEventListener("click", function () {
            var key = (newFieldKey.value || "").trim();
            var label = (newFieldLabel.value || "").trim();

            if (!key) {
                window.alert("Enter a field key first, for example: name or title");
                return;
            }

            var newField = {
                field_key: key,
                label: label || key,
                font_size: 32,
                align: "left",
                color_hex: "#000000",
                max_width: 900,
                line_height: 42,
                underline: false,
                options: {
                    text_align: "left",
                    font_style: "regular",
                    font_family: "default",
                    line_spacing: 1.25,
                    word_spacing: 0
                }
            };

            placeElementImmediately(newField);
        });
    }

    if (addTemplateBoxButton && newTemplateContent) {
        addTemplateBoxButton.addEventListener("click", function () {
            var template = (newTemplateContent.value || "").trim();
            if (!template) {
                window.alert("Enter template text first, then click Add Content Text Box.");
                return;
            }

            var newField = {
                field_key: generateTemplateFieldKey(),
                label: template,
                font_size: 32,
                align: "left",
                color_hex: "#000000",
                max_width: 900,
                line_height: 120,
                underline: false,
                options: {
                    text_align: "left",
                    font_style: "bold",
                    font_family: "default",
                    line_spacing: 1.25,
                    word_spacing: 0
                }
            };

            placeElementImmediately(newField);
        });
    }

    canvas.addEventListener("click", function (event) {
        if (!pendingNewField) {
            return;
        }

        if (event.target && event.target.closest(".mapping-marker")) {
            return;
        }

        var imageRect = image.getBoundingClientRect();
        var clickedX = event.clientX - imageRect.left;
        var clickedY = event.clientY - imageRect.top;

        if (clickedX < 0 || clickedY < 0 || clickedX > imageRect.width || clickedY > imageRect.height) {
            setStatus("Click inside the template image to place field.");
            return;
        }

        var created = {
            id: "m" + Date.now() + "_" + Math.random().toString(36).slice(2, 8),
            field_key: pendingNewField.field_key,
            label: pendingNewField.label,
            x_pos: toActualX(clickedX),
            y_pos: toActualY(clickedY),
            font_size: pendingNewField.font_size,
            align: pendingNewField.align,
            color_hex: pendingNewField.color_hex,
            max_width: pendingNewField.max_width,
            line_height: pendingNewField.line_height,
            underline: pendingNewField.underline,
            options: pendingNewField.options
        };

        mappings.push(created);
        selectedId = created.id;
        pendingNewField = null;
        setStatus("Field placed. Drag to adjust position.");

        renderMarkers();
        syncSelectedPanel();
        savePayloadToInput();
        setSaveState("Unsaved changes");
        commitHistory();
    });

    if (removeFieldButton) {
        removeFieldButton.addEventListener("click", function () {
            if (!selectedId) {
                return;
            }

            mappings = mappings.filter(function (row) {
                return row.id !== selectedId;
            });

            selectedId = null;
            renderMarkers();
            syncSelectedPanel();
            savePayloadToInput();
            setStatus("Selected field removed.");
            setSaveState("Unsaved changes");
            commitHistory();
        });
    }

    window.addEventListener("mousemove", function (event) {
        if (resizeState) {
            var resizeTarget = findMapping(resizeState.id);
            if (!resizeTarget) {
                resizeState = null;
                return;
            }

            var deltaDisplayWidth = event.clientX - resizeState.startX;
            var deltaDisplayHeight = event.clientY - resizeState.startY;

            var nextWidth = Math.round(resizeState.originWidth + toActualWidth(deltaDisplayWidth));
            var nextLineHeight = Math.round(resizeState.originLineHeight + toActualHeight(deltaDisplayHeight));

            resizeTarget.max_width = nextWidth;

            var widthRatio = nextWidth / Math.max(1, resizeState.originWidth);
            var heightRatio = nextLineHeight / Math.max(1, resizeState.originLineHeight);
            var ratio = (widthRatio + heightRatio) / 2;
            resizeTarget.font_size = Math.round(clamp(resizeState.originFontSize * ratio, 8, 180));
            resizeTarget.line_height = Math.max(nextLineHeight, resizeTarget.font_size + 8);
            resizeDirty = true;

            renderMarkers();
            syncSelectedPanel();
            savePayloadToInput();
            return;
        }

        if (!dragState) {
            return;
        }

        var selected = findMapping(dragState.id);
        if (!selected) {
            dragState = null;
            return;
        }

        var deltaDisplayX = event.clientX - dragState.startX;
        var deltaDisplayY = event.clientY - dragState.startY;

        var scale = getScale();
        var deltaActualX = deltaDisplayX / scale.x;
        var deltaActualY = deltaDisplayY / scale.y;

        selected.x_pos = Math.round(clamp(dragState.originX + deltaActualX, 0, scale.naturalWidth));
        selected.y_pos = Math.round(clamp(dragState.originY + deltaActualY, 0, scale.naturalHeight));
        dragDirty = true;

        renderMarkers();
        syncSelectedPanel();
        savePayloadToInput();
    });

    window.addEventListener("mouseup", function () {
        var shouldCommit = dragDirty || resizeDirty;

        if (resizeState) {
            setStatus("Text box resized. Font size auto-adjusted.");
        }

        dragState = null;
        resizeState = null;
        dragDirty = false;
        resizeDirty = false;

        if (shouldCommit) {
            setSaveState("Unsaved changes");
            commitHistory();
        }
    });

    window.addEventListener("resize", function () {
        renderMarkers();
        updateFloatingToolbarPosition();
    });

    document.addEventListener("keydown", function (event) {
        var target = event.target;
        var isTyping = !!(
            target && (
                target.tagName === "INPUT" ||
                target.tagName === "TEXTAREA" ||
                target.tagName === "SELECT" ||
                target.isContentEditable
            )
        );

        var isMod = event.ctrlKey || event.metaKey;
        var key = String(event.key || "").toLowerCase();

        if (isMod && key === "z" && !event.shiftKey) {
            event.preventDefault();
            if (undoMappingButton) {
                undoMappingButton.click();
            }
            return;
        }

        if (isMod && ((key === "z" && event.shiftKey) || key === "y")) {
            event.preventDefault();
            if (redoMappingButton) {
                redoMappingButton.click();
            }
            return;
        }

        if (!selectedId || isTyping) {
            return;
        }

        if (event.key === "Delete") {
            event.preventDefault();
            if (removeFieldButton) {
                removeFieldButton.click();
            }
            return;
        }

        if (["ArrowUp", "ArrowDown", "ArrowLeft", "ArrowRight"].indexOf(event.key) >= 0) {
            event.preventDefault();
            var selected = findMapping(selectedId);
            if (!selected) {
                return;
            }

            var step = event.shiftKey ? 10 : 1;
            if (event.key === "ArrowUp") {
                selected.y_pos -= step;
            }
            if (event.key === "ArrowDown") {
                selected.y_pos += step;
            }
            if (event.key === "ArrowLeft") {
                selected.x_pos -= step;
            }
            if (event.key === "ArrowRight") {
                selected.x_pos += step;
            }

            selected.x_pos = Math.round(clamp(selected.x_pos, 0, getScale().naturalWidth));
            selected.y_pos = Math.round(clamp(selected.y_pos, 0, getScale().naturalHeight));
            renderMarkers();
            syncSelectedPanel();
            savePayloadToInput();
            scheduleHistoryCommit();
        }
    });

    document.addEventListener("selectionchange", function () {
        updateFloatingToolbarVisibility();
    });

    document.addEventListener("mousedown", function (event) {
        if (!editingState || !editingState.markerMain || !editingState.markerMain.isContentEditable) {
            return;
        }

        var target = event.target;
        if ((editingState.marker && editingState.marker.contains(target)) || isToolbarTarget(target)) {
            clearPendingOutsideExit();
            return;
        }

        scheduleOutsideExit();
    }, true);

    image.addEventListener("load", function () {
        applyCanvasZoom();
        renderMarkers();
        syncSelectedPanel();
        savePayloadToInput();
    });

    mappingForm.addEventListener("submit", function () {
        setSaveState("Saving...");
        exitEditingMode();
        savePayloadToInput();
    });

    // 1. Right Sidebar tab switching
    var rightSidebarTabButtons = document.querySelectorAll("[data-sidebar-tab]");
    var rightSidebarPanes = document.querySelectorAll("[data-sidebar-pane]");
    if (rightSidebarTabButtons.length > 0 && rightSidebarPanes.length > 0) {
        rightSidebarTabButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                var tabKey = button.getAttribute("data-sidebar-tab");
                rightSidebarTabButtons.forEach(function (btn) {
                    btn.classList.toggle("active", btn.getAttribute("data-sidebar-tab") === tabKey);
                });
                rightSidebarPanes.forEach(function (pane) {
                    var isActive = pane.getAttribute("data-sidebar-pane") === tabKey;
                    pane.classList.toggle("is-active", isActive);
                    pane.style.display = isActive ? "block" : "none";
                });
            });
        });
    }

    // 2. Dynamic Layers panel rendering
    function renderLayers() {
        var container = document.getElementById("layersListContainer");
        if (!container) {
            return;
        }
        container.innerHTML = "";

        // Root
        var rootItem = document.createElement("div");
        rootItem.className = "layer-item active";
        rootItem.style.fontWeight = "600";
        rootItem.innerHTML = '<div class="layer-left"><svg viewBox="0 0 24 24" width="12" height="12" stroke="currentColor" stroke-width="2" fill="none" style="margin-right:6px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg><span>Certificate Document</span></div>';
        container.appendChild(rootItem);

        // Background Template
        var bgItem = document.createElement("div");
        bgItem.className = "layer-item";
        bgItem.innerHTML = '<div class="layer-left"><div class="layer-indent"></div><svg viewBox="0 0 24 24" width="12" height="12" stroke="currentColor" stroke-width="2" fill="none" style="margin-right:6px;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg><span>Template Background</span></div>';
        container.appendChild(bgItem);

        // Fields
        mappings.forEach(function (row) {
            var item = document.createElement("div");
            item.className = "layer-item" + (row.id === selectedId ? " active" : "");
            item.dataset.id = row.id;

            var label = row.field_key || "Text Box";
            if (row.label && row.label.trim()) {
                label = row.label;
                if (label.length > 20) {
                    label = label.slice(0, 18) + "...";
                }
            }

            item.innerHTML = '<div class="layer-left"><div class="layer-indent"></div><svg viewBox="0 0 24 24" width="12" height="12" stroke="currentColor" stroke-width="2" fill="none" style="margin-right:6px;"><polyline points="4 7 4 4 20 4 20 7"></polyline><line x1="9" y1="20" x2="15" y2="20"></line><line x1="12" y1="4" x2="12" y2="20"></line></svg><span>' + label + '</span></div>' +
                             '<div class="layer-right">' +
                             '<button type="button" class="layer-btn visibility-btn active" title="Toggle visibility"><svg viewBox="0 0 24 24" width="12" height="12" stroke="currentColor" stroke-width="2" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></button>' +
                             '<button type="button" class="layer-btn lock-btn" title="Lock/Unlock"><svg viewBox="0 0 24 24" width="12" height="12" stroke="currentColor" stroke-width="2" fill="none"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg></button>' +
                             '</div>';

            item.addEventListener("click", function () {
                selectedId = row.id;
                syncSelectedPanel();
                var marker = document.querySelector('.mapping-marker[data-id="' + row.id + '"]');
                if (marker) {
                    marker.click();
                }
            });

            // Visibility toggle hook
            var visBtn = item.querySelector(".visibility-btn");
            if (visBtn) {
                visBtn.addEventListener("click", function (e) {
                    e.stopPropagation();
                    var marker = document.querySelector('.mapping-marker[data-id="' + row.id + '"]');
                    if (marker) {
                        var isHidden = marker.style.opacity === "0";
                        marker.style.opacity = isHidden ? "1" : "0";
                        visBtn.classList.toggle("active", isHidden);
                        visBtn.style.color = isHidden ? "" : "#94a3b8";
                    }
                });
            }

            // Lock toggle hook
            var lockBtn = item.querySelector(".lock-btn");
            if (lockBtn) {
                lockBtn.addEventListener("click", function (e) {
                    e.stopPropagation();
                    lockBtn.classList.toggle("active");
                    var isLocked = lockBtn.classList.contains("active");
                    lockBtn.style.color = isLocked ? "var(--primary)" : "";
                    var marker = document.querySelector('.mapping-marker[data-id="' + row.id + '"]');
                    if (marker) {
                        marker.style.pointerEvents = isLocked ? "none" : "";
                        if (isLocked) {
                            marker.classList.remove("is-selected");
                        }
                    }
                    setStatus(isLocked ? "Field locked against editing." : "Field unlocked.");
                });
            }

            container.appendChild(item);
        });

        var elementsCountLabel = document.getElementById("elementsCountLabel");
        if (elementsCountLabel) {
            elementsCountLabel.textContent = mappings.length + " mapped";
        }
    }
    window.renderLayers = renderLayers;

    // 3. Grid guide keys Ctrl + '
    var guidelinesOverlay = document.getElementById("guidelinesOverlay");
    function toggleGridLines() {
        if (guidelinesOverlay) {
            var isVisible = guidelinesOverlay.style.display === "block";
            guidelinesOverlay.style.display = isVisible ? "none" : "block";
            setStatus(isVisible ? "Grid guides disabled." : "Grid guides enabled.");
        }
    }
    if (gridToggleButton) {
        gridToggleButton.addEventListener("click", function (e) {
            e.preventDefault();
            toggleGridLines();
        });
    }

    document.addEventListener("keydown", function (event) {
        var isMod = event.ctrlKey || event.metaKey;
        if (isMod && event.key === "'") {
            event.preventDefault();
            toggleGridLines();
        }
    });

    // 4. Two level Left sidebar toggling
    var editorToolDrawer = document.getElementById("editorToolDrawer");
    var lastTab = "templates";
    if (editorTabButtons.length > 0 && editorToolDrawer) {
        editorTabButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                var key = button.getAttribute("data-editor-tab") || "text";
                if (lastTab === key) {
                    editorToolDrawer.classList.toggle("collapsed");
                    button.classList.toggle("active", !editorToolDrawer.classList.contains("collapsed"));
                } else {
                    editorToolDrawer.classList.remove("collapsed");
                    editorTabButtons.forEach(function (btn) {
                        btn.classList.toggle("active", btn.getAttribute("data-editor-tab") === key);
                    });
                    activateEditorPane(key);
                    lastTab = key;
                }
            });
        });
    }

    // 5. AI Designer prompt simulation flow
    var aiGenerateBtn = document.getElementById("aiGenerateBtn");
    var aiGenProgress = document.getElementById("aiGenProgress");
    if (aiGenerateBtn && aiGenProgress) {
        aiGenerateBtn.addEventListener("click", function (e) {
            e.preventDefault();
            aiGenProgress.style.display = "block";
            aiGenerateBtn.disabled = true;

            var step1 = document.getElementById("aiStep1");
            var step2 = document.getElementById("aiStep2");
            var step3 = document.getElementById("aiStep3");
            var step4 = document.getElementById("aiStep4");

            step1.className = "ai-flow-step active";

            setTimeout(function () {
                step1.className = "ai-flow-step complete";
                step1.innerHTML = "✓ Match typography complete";
                step2.className = "ai-flow-step active";
                step2.innerHTML = '<span class="spinner-mini"></span> Configuring palette...';
            }, 1000);

            setTimeout(function () {
                step2.className = "ai-flow-step complete";
                step2.innerHTML = "✓ Palette configured (Accent: Purple)";
                step3.className = "ai-flow-step active";
                step3.innerHTML = '<span class="spinner-mini"></span> Placing dynamic fields...';
            }, 2000);

            setTimeout(function () {
                step3.className = "ai-flow-step complete";
                step3.innerHTML = "✓ Recipient Name placed dynamically";
                step4.className = "ai-flow-step complete";
                step4.innerHTML = "✓ AI Layout template generated!";

                var hasName = mappings.some(function (r) { return r.field_key === "{name}"; });
                if (!hasName) {
                    mappings.push({
                        id: "m_ai_" + Date.now(),
                        field_key: "{name}",
                        label: "Jane Doe (Recipient)",
                        x_pos: 200,
                        y_pos: 420,
                        font_size: 48,
                        align: "center",
                        color_hex: "#6D5DFC",
                        max_width: 800,
                        line_height: 60,
                        underline: false,
                        options: {
                            text_align: "center",
                            font_style: "bold",
                            font_family: "default",
                            line_spacing: 1.25,
                            word_spacing: 0
                        }
                    });
                }

                var hasTitle = mappings.some(function (r) { return r.field_key === "tpl_title"; });
                if (!hasTitle) {
                    mappings.push({
                        id: "m_ai_title_" + Date.now(),
                        field_key: "tpl_title",
                        label: "CERTIFICATE OF APPRECIATION",
                        x_pos: 100,
                        y_pos: 240,
                        font_size: 38,
                        align: "center",
                        color_hex: "#1e293b",
                        max_width: 1000,
                        line_height: 50,
                        underline: false,
                        options: {
                            text_align: "center",
                            font_style: "bold",
                            font_family: "georgia",
                            line_spacing: 1.25,
                            word_spacing: 0
                        }
                    });
                }

                renderMarkers();
                syncSelectedPanel();
                savePayloadToInput();
                commitHistory();

                setTimeout(function () {
                    aiGenProgress.style.display = "none";
                    aiGenerateBtn.disabled = false;
                    step1.innerHTML = '<span class="spinner-mini"></span> Matching fonts...';
                    step1.className = "ai-flow-step";
                    step2.innerHTML = 'Configuring palette...';
                    step2.className = "ai-flow-step";
                    step3.innerHTML = 'Placing dynamic fields...';
                    step3.className = "ai-flow-step";
                    step4.innerHTML = 'Layout template ready!';
                    step4.className = "ai-flow-step";
                }, 3000);

            }, 3000);
        });
    }

    applyCanvasZoom();
    renderMarkers();
    syncSelectedPanel();
    savePayloadToInput();
    commitHistory();
    updateHistoryControls();
    setSaveState("Ready");
})();
