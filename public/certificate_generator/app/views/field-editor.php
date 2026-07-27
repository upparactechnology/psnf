<?php
$mappedFieldCount = count($fieldMappings ?? []);
$templateDisplayName = (string) ($selectedType['name'] ?? 'Template');
$conferenceDisplayName = (string) ($conference['name'] ?? 'Conference');
$conferenceYear = (string) ($conference['year'] ?? '');
?>

<!-- Header Top Bar -->
<section class="editor-studio-header-v3">
    <div class="editor-v3-topbar">
        <div class="editor-v3-left">
            <div class="editor-v3-logo-group">
                <div class="editor-v3-logo">C</div>
                <div class="editor-v3-title-area">
                    <input type="text" class="editor-v3-title-input" value="<?= e($templateDisplayName) ?>" placeholder="Untitled Template">
                    <span class="editor-v3-save-badge" id="editorSaveState">Saved</span>
                </div>
            </div>
        </div>

        <div class="editor-v3-center">
            <button type="button" class="editor-v3-icon-btn" id="undoMappingButton" title="Undo (Ctrl+Z)" <?= $templateExists ? '' : 'disabled' ?>>
                <!-- Undo SVG -->
                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v6h6"></path><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"></path></svg>
            </button>
            <button type="button" class="editor-v3-icon-btn" id="redoMappingButton" title="Redo (Ctrl+Y)" <?= $templateExists ? '' : 'disabled' ?>>
                <!-- Redo SVG -->
                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M21 7v6h-6"></path><path d="M3 17a9 9 0 0 1 9-9 9 9 0 0 1 6 2.3l3 2.7"></path></svg>
            </button>
            <div class="editor-v3-divider"></div>
            <button type="button" class="editor-v3-icon-btn" id="zoomOutButton" title="Zoom Out" <?= $templateExists ? '' : 'disabled' ?>>-</button>
            <span class="editor-v3-zoom-badge" id="zoomLevelLabel">100%</span>
            <button type="button" class="editor-v3-icon-btn" id="zoomInButton" title="Zoom In" <?= $templateExists ? '' : 'disabled' ?>>+</button>
            <div class="editor-v3-divider"></div>
            <button type="button" class="editor-v3-icon-btn" id="gridToggleButton" title="Toggle Guides (Ctrl+')" <?= $templateExists ? '' : 'disabled' ?>>
                <!-- Grid SVG -->
                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line><line x1="15" y1="3" x2="15" y2="21"></line><line x1="3" y1="9" x2="21" y2="9"></line><line x1="3" y1="15" x2="21" y2="15"></line></svg>
            </button>
            <button type="button" class="editor-v3-icon-btn" id="zoomResetButton" title="Reset Zoom" <?= $templateExists ? '' : 'disabled' ?>>
                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"></path><path d="M9 21H3v-6"></path><path d="M21 3l-7 7"></path><path d="M3 21l7-7"></path></svg>
            </button>
        </div>

        <div class="editor-v3-right">
            <div class="editor-v3-view-modes">
                <button type="button" class="editor-v3-view-btn active" data-view-mode="desktop">Desktop</button>
                <button type="button" class="editor-v3-view-btn" data-view-mode="mobile">Mobile</button>
                <button type="button" class="editor-v3-view-btn" data-view-mode="print">Print</button>
            </div>
            <a class="btn-muted-action" href="<?= e(url('generate', ['conference_id' => (int) $selectedConferenceId, 'certificate_type_id' => (int) $selectedTypeId, 'step' => 3])) ?>">Preview</a>
            <button type="submit" class="btn-muted-action" form="mappingForm" <?= $templateExists ? '' : 'disabled' ?>>Save Settings</button>
            <a class="btn-premium-action" href="<?= e(url('generate', ['conference_id' => (int) $selectedConferenceId, 'certificate_type_id' => (int) $selectedTypeId, 'step' => 4])) ?>">Generate Certificates</a>
        </div>
    </div>
</section>

<?php if (!$templateExists): ?>
    <!-- Canva-style Empty State Starter View -->
    <div class="editor-empty-state-v3">
        <div class="empty-state-box">
            <h2>Design a Premium Certificate</h2>
            <p>Select a design starter or upload your custom background image to launch the Canva-style editor.</p>
            
            <div class="starter-categories">
                <div class="starter-card" onclick="document.getElementById('templateUploadInput').click();">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    <span>Blank Canvas</span>
                    <small>Upload JPG/PNG</small>
                </div>
                <div class="starter-card" onclick="document.getElementById('templateUploadInput').click();">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c0 2 2.5 3 6 3s6-1 6-3v-5"></path></svg>
                    <span>Academic Layout</span>
                    <small>Degrees & Diplomas</small>
                </div>
                <div class="starter-card" onclick="document.getElementById('templateUploadInput').click();">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>
                    <span>Employee Award</span>
                    <small>Corporate & Work</small>
                </div>
                <div class="starter-card" onclick="document.getElementById('templateUploadInput').click();">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                    <span>Participation</span>
                    <small>Events & Courses</small>
                </div>
                <div class="starter-card" onclick="document.getElementById('templateUploadInput').click();">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    <span>Government Style</span>
                    <small>Official & Secure</small>
                </div>
                <div class="starter-card" onclick="document.getElementById('templateUploadInput').click();">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                    <span>Premium Luxury</span>
                    <small>Gold & Border Accents</small>
                </div>
            </div>

            <form method="post" enctype="multipart/form-data" class="form-grid top-gap-sm" style="max-width: 320px; margin: 0 auto;">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="upload_template">
                <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
                <input type="hidden" name="certificate_type_id" value="<?= e((string) $selectedTypeId) ?>">

                <label class="button btn-premium-action" style="display: block; text-align: center; cursor: pointer;">
                    Upload JPG Template
                    <input type="file" name="template" accept="image/jpeg" onchange="this.form.submit()" style="display: none;">
                </label>
            </form>
        </div>
    </div>
<?php else: ?>
    <!-- Forms needed for action submissions -->
    <form method="post" id="templateUploadForm" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="upload_template">
        <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
        <input type="hidden" name="certificate_type_id" value="<?= e((string) $selectedTypeId) ?>">
        <input type="file" name="template" id="templateUploadInput" accept="image/jpeg" onchange="this.form.submit()" hidden>
    </form>

    <form method="post" id="mappingForm">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="save_mappings">
        <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
        <input type="hidden" name="certificate_type_id" value="<?= e((string) $selectedTypeId) ?>">
        <input type="hidden" name="mappings_json" id="mappingsJson" value="[]">

        <div id="fieldEditorRoot"
             class="editor-v3-workspace-container"
             data-template-src="<?= e($templatePath) ?>"
             data-initial='<?= e(json_encode($fieldMappings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]') ?>'
             data-font-options='<?= e(json_encode($availableFonts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]') ?>'>

            <!-- 1. Global Navigation Strip (64px) -->
            <aside class="editor-v3-global-nav">
                <button type="button" class="editor-v3-nav-item active" data-editor-tab="templates" title="Templates">
                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                    <span>Templates</span>
                </button>
                <button type="button" class="editor-v3-nav-item" data-editor-tab="elements" title="Elements">
                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                    <span>Elements</span>
                </button>
                <button type="button" class="editor-v3-nav-item" data-editor-tab="text" title="Text Fields">
                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><polyline points="4 7 4 4 20 4 20 7"></polyline><line x1="9" y1="20" x2="15" y2="20"></line><line x1="12" y1="4" x2="12" y2="20"></line></svg>
                    <span>Text</span>
                </button>
                <button type="button" class="editor-v3-nav-item" data-editor-tab="images" title="Uploads">
                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    <span>Uploads</span>
                </button>
                <button type="button" class="editor-v3-nav-item" data-editor-tab="qr" title="QR Codes">
                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    <span>QR Code</span>
                </button>
                <button type="button" class="editor-v3-nav-item" data-editor-tab="fields" title="Dynamic Fields">
                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    <span>Fields</span>
                </button>
                <button type="button" class="editor-v3-nav-item" data-editor-tab="brand" title="Brand Kit">
                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path><path d="M12 16a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"></path></svg>
                    <span>Brand Kit</span>
                </button>
                <button type="button" class="editor-v3-nav-item" data-editor-tab="ai" title="AI Designer">
                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                    <span>AI Designer</span>
                </button>
            </aside>

            <!-- 2. Tool Drawer (320px) -->
            <aside class="editor-v3-tool-drawer" id="editorToolDrawer">
                
                <!-- Drawer Section: Templates -->
                <div class="drawer-pane is-active" data-editor-pane="templates">
                    <div class="drawer-header">
                        <h3>Certificate Templates</h3>
                        <p>Change your design foundation instantly.</p>
                    </div>
                    <div class="drawer-content">
                        <div class="template-grid">
                            <div class="template-card" onclick="alert('Template starter applied! Feel free to modify text mappings.');">
                                <img src="<?= e($templatePath) ?>" alt="Academic">
                                <span>Academic Classic</span>
                            </div>
                            <div class="template-card" onclick="alert('Template starter applied! Feel free to modify text mappings.');">
                                <img src="<?= e($templatePath) ?>" alt="Modern">
                                <span>Modern Clean</span>
                            </div>
                            <div class="template-card" onclick="alert('Template starter applied! Feel free to modify text mappings.');">
                                <img src="<?= e($templatePath) ?>" alt="Corporate">
                                <span>Corporate Award</span>
                            </div>
                            <div class="template-card" onclick="alert('Template starter applied! Feel free to modify text mappings.');">
                                <img src="<?= e($templatePath) ?>" alt="Minimalist">
                                <span>Minimal Elegant</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Drawer Section: Elements -->
                <div class="drawer-pane" data-editor-pane="elements" hidden>
                    <div class="drawer-header">
                        <h3>Design Elements</h3>
                        <p>Add margins, grids, and event titles.</p>
                    </div>
                    <div class="drawer-content">
                        <div class="drawer-section">
                            <span class="drawer-section-title">Common Layouts</span>
                            <button type="button" class="button btn-muted-action top-gap-sm" style="width:100%; display:block;" data-editor-action="addText">Add text element</button>
                            <button type="button" class="button btn-muted-action top-gap-sm dynamic-field-button" style="width:100%; display:block;" data-token="{title}">Paper Title</button>
                            <button type="button" class="button btn-muted-action top-gap-sm dynamic-field-button" style="width:100%; display:block;" data-token="{institute}">Institute Name</button>
                            <button type="button" class="button btn-muted-action top-gap-sm dynamic-field-button" style="width:100%; display:block;" data-token="Presented at <?= e($conferenceDisplayName) ?> <?= e($conferenceYear) ?>">Event line</button>
                        </div>
                        <div class="drawer-section">
                            <span class="drawer-section-title">Layout Tools</span>
                            <button type="button" class="button btn-muted-action top-gap-sm" style="width:100%; display:block;" data-editor-action="toggleGrid">Toggle guidelines</button>
                            <button type="button" class="button btn-muted-action top-gap-sm" style="width:100%; display:block;" data-editor-action="zoomReset">Reset workspace zoom</button>
                        </div>
                    </div>
                </div>

                <!-- Drawer Section: Text -->
                <div class="drawer-pane" data-editor-pane="text" hidden>
                    <div class="drawer-header">
                        <h3>Typography & Text</h3>
                        <p>Insert text headers, subheadings and block copies.</p>
                    </div>
                    <div class="drawer-content">
                        <button type="button" id="addTextElementButton" class="button btn-premium-action" style="width: 100%; margin-bottom: 12px;">+ Add Heading</button>
                        <button type="button" class="button btn-muted-action dynamic-field-button top-gap-sm" style="width: 100%; margin-bottom: 8px;" data-token="Subheading text">Add Subheading</button>
                        <button type="button" class="button btn-muted-action dynamic-field-button top-gap-sm" style="width: 100%;" data-token="Body text">Add Body Copy</button>
                    </div>
                </div>

                <!-- Drawer Section: Uploads -->
                <div class="drawer-pane" data-editor-pane="images" hidden>
                    <div class="drawer-header">
                        <h3>Uploads</h3>
                        <p>Manage background files and logos.</p>
                    </div>
                    <div class="drawer-content">
                        <button type="button" class="button btn-premium-action" style="width:100%;" id="uploadTemplateImageButton">Upload New Background</button>
                        <a class="button btn-muted-action top-gap-sm" style="display:block; text-align:center; margin-top:8px;" href="<?= e(url('field-editor', ['conference_id' => (int) $selectedConferenceId, 'certificate_type_id' => (int) $selectedTypeId, 'download_template' => 1])) ?>">Download Active Image</a>
                    </div>
                </div>

                <!-- Drawer Section: QR -->
                <div class="drawer-pane" data-editor-pane="qr" hidden>
                    <div class="drawer-header">
                        <h3>QR Code Verification</h3>
                        <p>Add recipient verify triggers.</p>
                    </div>
                    <div class="drawer-content">
                        <button type="button" class="button btn-muted-action dynamic-field-button" style="width:100%; text-align:left; margin-bottom:8px;" data-token="{verify_code}">Place Verification Code</button>
                        <button type="button" class="button btn-muted-action dynamic-field-button" style="width:100%; text-align:left;" data-token="{qr_verification}">Place Verification QR Code</button>
                    </div>
                </div>

                <!-- Drawer Section: Fields -->
                <div class="drawer-pane" data-editor-pane="fields" hidden>
                    <div class="drawer-header">
                        <h3>Dynamic Fields</h3>
                        <p>Place fields that auto-inject recipient data.</p>
                    </div>
                    <div class="drawer-content">
                        <div class="chips-grid">
                            <div class="field-chip field-chip-recipient dynamic-field-button" data-token="{name}">
                                <span>Recipient Name</span>
                                <span class="chip-add-btn">+</span>
                            </div>
                            <div class="field-chip field-chip-dates dynamic-field-button" data-token="{date}">
                                <span>Issue Date</span>
                                <span class="chip-add-btn">+</span>
                            </div>
                            <div class="field-chip field-chip-ids dynamic-field-button" data-token="{verify_code}">
                                <span>Certificate ID</span>
                                <span class="chip-add-btn">+</span>
                            </div>
                            <div class="field-chip field-chip-organization dynamic-field-button" data-token="{course_name}">
                                <span>Course Name</span>
                                <span class="chip-add-btn">+</span>
                            </div>
                            <div class="field-chip field-chip-organization dynamic-field-button" data-token="{organization}">
                                <span>Organization</span>
                                <span class="chip-add-btn">+</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Drawer Section: Brand Kit -->
                <div class="drawer-pane" data-editor-pane="brand" hidden>
                    <div class="drawer-header">
                        <h3>Brand Assets</h3>
                        <p>Quick logo presets and organization palettes.</p>
                    </div>
                    <div class="drawer-content">
                        <div class="drawer-section">
                            <span class="drawer-section-title">Color Palette</span>
                            <div style="display:flex; gap:8px; margin-top:8px;">
                                <div style="width:32px; height:32px; border-radius:50%; background:#6D5DFC; border:1px solid #ddd; cursor:pointer;" onclick="document.getElementById('colorInput').value='#6D5DFC'; document.getElementById('colorInput').dispatchEvent(new Event('input'));"></div>
                                <div style="width:32px; height:32px; border-radius:50%; background:#1e293b; border:1px solid #ddd; cursor:pointer;" onclick="document.getElementById('colorInput').value='#1e293b'; document.getElementById('colorInput').dispatchEvent(new Event('input'));"></div>
                                <div style="width:32px; height:32px; border-radius:50%; background:#d97706; border:1px solid #ddd; cursor:pointer;" onclick="document.getElementById('colorInput').value='#d97706'; document.getElementById('colorInput').dispatchEvent(new Event('input'));"></div>
                                <div style="width:32px; height:32px; border-radius:50%; background:#059669; border:1px solid #ddd; cursor:pointer;" onclick="document.getElementById('colorInput').value='#059669'; document.getElementById('colorInput').dispatchEvent(new Event('input'));"></div>
                                <div style="width:32px; height:32px; border-radius:50%; background:#dc2626; border:1px solid #ddd; cursor:pointer;" onclick="document.getElementById('colorInput').value='#dc2626'; document.getElementById('colorInput').dispatchEvent(new Event('input'));"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Drawer Section: AI Designer -->
                <div class="drawer-pane" data-editor-pane="ai" hidden>
                    <div class="drawer-header">
                        <h3>AI Designer Assistance</h3>
                        <p>Generate layout & typography via prompt.</p>
                    </div>
                    <div class="drawer-content">
                        <div class="ai-designer-box">
                            <textarea class="ai-textarea" id="aiPromptInput" placeholder="Describe your certificate... e.g., 'Modern college certificate in gold theme'"></textarea>
                            <button type="button" class="ai-generate-btn" id="aiGenerateBtn">Generate Design</button>
                            
                            <!-- AI Progress Steps -->
                            <div class="ai-generation-flow" id="aiGenProgress">
                                <div class="ai-flow-step" id="aiStep1"><span class="spinner-mini" id="aiSpinner1"></span> Matching fonts...</div>
                                <div class="ai-flow-step" id="aiStep2">Configuring palette...</div>
                                <div class="ai-flow-step" id="aiStep3">Placing dynamic fields...</div>
                                <div class="ai-flow-step" id="aiStep4">Layout template ready!</div>
                            </div>

                            <div class="ai-suggestions">
                                <button type="button" class="ai-suggestion-tag" onclick="document.getElementById('aiPromptInput').value='Create a modern academic certificate in luxury dark styling';">Modern academic certificate</button>
                                <button type="button" class="ai-suggestion-tag" onclick="document.getElementById('aiPromptInput').value='Generate a clean minimal employee recognition award';">Employee recognition award</button>
                                <button type="button" class="ai-suggestion-tag" onclick="document.getElementById('aiPromptInput').value='Build an official security certificate with verification code';">Official secure certificate</button>
                            </div>
                        </div>
                    </div>
                </div>

            </aside>

            <!-- 3. Infinite Workspace Canvas -->
            <main class="editor-v3-canvas-area" id="editorWorkspaceArea">
                <div class="editor-canvas-container-v3" id="editorCanvasContainer">
                    
                    <!-- Rulers -->
                    <div class="canvas-ruler-x"></div>
                    <div class="canvas-ruler-y"></div>

                    <!-- Margin guides -->
                    <div class="canvas-safe-margin"></div>

                    <!-- Guidelines Overlay -->
                    <div class="canvas-grid-overlay" id="guidelinesOverlay"></div>

                    <!-- Main Canvas wrapper -->
                    <div class="template-canvas" id="templateCanvas">
                        <img id="templateImage" src="<?= e($templatePath) ?>?v=<?= time() ?>" alt="Certificate Canvas">
                        <div id="markersLayer"></div>
                    </div>
                </div>

                <!-- FLOATING CONTEXTUAL TOOLBAR -->
                <div class="editor-v3-floating-toolbar" id="floatingInlineToolbar" hidden>
                    <select id="quickFontFamilyInput" class="toolbar-select" title="Font Family">
                        <?php foreach ($availableFonts as $font): ?>
                            <option value="<?= e((string) ($font['value'] ?? 'default')) ?>">
                                <?= e((string) ($font['label'] ?? 'Default')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="number" id="quickFontSizeInput" class="toolbar-input-number" min="8" max="180" value="32" title="Font Size">
                    <input type="color" id="quickColorInput" class="toolbar-color-picker" value="#000000" title="Text Color">

                    <div class="editor-v3-divider"></div>

                    <button type="button" class="editor-v3-icon-btn" id="quickBoldButton" title="Bold"><strong>B</strong></button>
                    <button type="button" class="editor-v3-icon-btn" id="quickItalicButton" title="Italic"><em>I</em></button>

                    <div class="editor-v3-divider"></div>

                    <button type="button" class="editor-v3-icon-btn" id="quickAlignLeftButton" title="Align Left">L</button>
                    <button type="button" class="editor-v3-icon-btn" id="quickAlignCenterButton" title="Align Center">C</button>
                    <button type="button" class="editor-v3-icon-btn" id="quickAlignRightButton" title="Align Right">R</button>
                    
                    <!-- Inline edits hidden targets (for field-editor.js hooks compatibility) -->
                    <button type="button" id="quickAlignJustifyButton" hidden></button>
                    <button type="button" id="quickInlineBoldButton" hidden></button>
                    <button type="button" id="quickInlineItalicButton" hidden></button>
                    <button type="button" id="quickInlineBoldItalicButton" hidden></button>
                    <select id="quickInlineFontFamilyInput" hidden></select>
                    <button type="button" id="quickInlineFontApplyButton" hidden></button>
                </div>

                <!-- Floating bottom-right certificate status generation card -->
                <div class="editor-floating-generation-card">
                    <h4 class="gen-card-title">
                        <span>Generation Summary</span>
                        <span class="gen-status-indicator" id="genBadge">Ready</span>
                    </h4>
                    <div class="gen-stats-list">
                        <div class="gen-stat-row">
                            <span>Background Template</span>
                            <span style="color:#10b981;">Active</span>
                        </div>
                        <div class="gen-stat-row">
                            <span>Dynamic Elements</span>
                            <span id="elementsCountLabel">0 mapped</span>
                        </div>
                        <div class="gen-stat-row">
                            <span>Validation Status</span>
                            <span style="color:#10b981;">Passed</span>
                        </div>
                    </div>
                    <div class="gen-actions">
                        <a class="button btn-premium-action" style="display:block; text-align:center; font-size:12px; padding:6px 12px !important;" href="<?= e(url('generate', ['conference_id' => (int) $selectedConferenceId, 'certificate_type_id' => (int) $selectedTypeId, 'step' => 4])) ?>">Generate Certificates</a>
                        <button type="submit" class="button btn-muted-action" form="mappingForm" style="display:block; width:100%; font-size:12px; padding:6px 12px !important;">Save Mapping</button>
                    </div>
                </div>
            </main>

            <!-- 4. Right Properties/Layers Sidebar -->
            <aside class="editor-v3-right-sidebar">
                <nav class="right-sidebar-tabs" aria-label="Properties view mode">
                    <button type="button" class="sidebar-tab-btn active" data-sidebar-tab="properties">Properties</button>
                    <button type="button" class="sidebar-tab-btn" data-sidebar-tab="design">Design</button>
                    <button type="button" class="sidebar-tab-btn" data-sidebar-tab="layers">Layers</button>
                </nav>

                <div class="right-sidebar-content">
                    
                    <!-- TAB 1: PROPERTIES (Dynamic/selected element details) -->
                    <div class="sidebar-pane is-active" data-sidebar-pane="properties">
                        <div id="propertyEmptyState" class="property-empty-state">
                            <p>Select a text element on the canvas to configure styling and positions.</p>
                        </div>

                        <div id="selectedFieldPanel" style="display: none;">
                            <div class="sidebar-control-group">
                                <span class="sidebar-control-title">Element Label</span>
                                <div class="property-field">
                                    <label for="fieldLabelInput">Content Text</label>
                                    <textarea id="fieldLabelInput" rows="3" placeholder="Field text content"></textarea>
                                </div>
                            </div>

                            <div class="sidebar-control-group">
                                <span class="sidebar-control-title">Layout Parameters</span>
                                <div class="property-grid-2">
                                    <div class="property-field">
                                        <label for="xPosInput">X (pixels)</label>
                                        <input type="number" id="xPosInput">
                                    </div>
                                    <div class="property-field">
                                        <label for="yPosInput">Y (pixels)</label>
                                        <input type="number" id="yPosInput">
                                    </div>
                                </div>
                                <div class="property-grid-2">
                                    <div class="property-field">
                                        <label for="maxWidthInput">Max Width</label>
                                        <input type="number" id="maxWidthInput">
                                    </div>
                                    <div class="property-field">
                                        <label for="lineHeightInput">Height</label>
                                        <input type="number" id="lineHeightInput">
                                    </div>
                                </div>
                            </div>

                            <div class="sidebar-control-group">
                                <span class="sidebar-control-title">Spacing & Placement</span>
                                <div class="property-grid-2">
                                    <div class="property-field">
                                        <label for="lineSpacingInput">Line Spacing</label>
                                        <input type="number" id="lineSpacingInput" min="1" max="3" step="0.05" value="1.25">
                                    </div>
                                    <div class="property-field">
                                        <label for="wordSpacingInput">Word Spacing</label>
                                        <input type="number" id="wordSpacingInput" min="0" max="30" step="1" value="0">
                                    </div>
                                </div>
                            </div>

                            <div class="sidebar-control-group" style="border:none; padding-bottom:0;">
                                <button type="button" class="button btn-muted-action" id="removeFieldButton" style="width: 100%; color: #dc2626 !important; border-color: #fca5a5 !important;">Delete Element</button>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: DESIGN (Global controls, Fonts, Colors) -->
                    <div class="sidebar-pane" data-sidebar-pane="design">
                        <div class="sidebar-control-group">
                            <span class="sidebar-control-title">Global Typography</span>
                            <div class="property-field">
                                <label for="fontFamilyInput">Active Selection Font</label>
                                <select id="fontFamilyInput">
                                    <?php foreach ($availableFonts as $font): ?>
                                        <option value="<?= e((string) ($font['value'] ?? 'default')) ?>">
                                            <?= e((string) ($font['label'] ?? 'Default')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="property-field">
                                <label for="fontSizeInput">Font Size</label>
                                <input type="number" id="fontSizeInput" min="8" max="180" value="32">
                            </div>
                            <div class="property-field">
                                <label for="colorInput">Font Color</label>
                                <input type="color" id="colorInput" value="#000000" style="height: 36px; padding: 0; cursor: pointer;">
                            </div>
                        </div>

                        <div class="sidebar-control-group">
                            <span class="sidebar-control-title">Formatting Options</span>
                            <div style="display:flex; gap:8px;">
                                <button type="button" class="button btn-muted-action" id="toggleBoldButton" style="flex:1;">Bold</button>
                                <button type="button" class="button btn-muted-action" id="toggleItalicButton" style="flex:1;">Italic</button>
                                <button type="button" class="button btn-muted-action" id="toggleUnderlineButton" style="flex:1;">Underline</button>
                            </div>
                            <div style="display:flex; gap:8px; margin-top:8px;">
                                <button type="button" class="button btn-muted-action" id="alignLeftButton" style="flex:1;">L</button>
                                <button type="button" class="button btn-muted-action" id="alignCenterButton" style="flex:1;">C</button>
                                <button type="button" class="button btn-muted-action" id="alignRightButton" style="flex:1;">R</button>
                                <button type="button" class="button btn-muted-action" id="alignJustifyButton" style="flex:1;">J</button>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: LAYERS (Dynamic canvas layers hierarchy) -->
                    <div class="sidebar-pane" data-sidebar-pane="layers">
                        <div class="sidebar-control-group">
                            <span class="sidebar-control-title">Canvas Elements Hierarchy</span>
                            <div class="layers-list" id="layersListContainer">
                                <!-- Dynamically generated via field-editor.js -->
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Hidden inputs mapping properties for JS backwards compatibility -->
                <input type="hidden" id="fieldKeyInput">
                <input type="hidden" id="fontStyleInput" value="regular">
                <input type="hidden" id="alignInput" value="left">
                <input type="checkbox" id="underlineInput" value="1" hidden>

                <div class="editor-pro-tip" style="padding: 16px; font-size: 11px; background: var(--bg-darker); border-top: 1px solid var(--border-light);">
                    <strong>Designer Tip</strong>
                    <span>Use keyboard <code>Ctrl+'</code> to show grids. Hold <code>Shift</code> to snap element sizes.</span>
                </div>
            </aside>

        </div>
    </form>
<?php endif; ?>
