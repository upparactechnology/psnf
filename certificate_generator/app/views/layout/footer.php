<?php if (is_logged_in()): ?>
        </section>
    </main>
</div>

<div class="ai-manual-widget" data-ai-manual data-current-page="<?= e((string) ($currentPage ?? 'dashboard')) ?>">
    <button type="button" class="ai-manual-toggle" data-ai-manual-toggle aria-expanded="false" aria-controls="aiManualPanel">
        <span class="ai-manual-toggle-dot" aria-hidden="true"></span>
        <span>AI Manual</span>
    </button>

    <section class="ai-manual-panel" id="aiManualPanel" data-ai-manual-panel hidden>
        <div class="ai-manual-panel-head">
            <strong>AI Workflow Assistant</strong>
            <button type="button" class="ai-manual-close" data-ai-manual-close aria-label="Close assistant">&times;</button>
        </div>

        <p class="ai-manual-subtitle">Track your setup step by step and continue where you left off.</p>

        <div class="ai-manual-progress">
            <span data-ai-manual-progress-label>0 / 8 completed</span>
            <div class="ai-manual-progress-track">
                <span class="ai-manual-progress-fill" data-ai-manual-progress-fill></span>
            </div>
        </div>

        <article class="ai-manual-current-step" data-ai-manual-current-step>
            <div class="ai-manual-current-step-top">
                <span class="ai-manual-current-step-badge" data-ai-manual-step-badge>Step 1 of 8</span>
                <span class="ai-manual-current-step-status" data-ai-manual-step-status>Pending</span>
            </div>
            <h4 data-ai-manual-step-title>Create Conference</h4>
            <p data-ai-manual-step-description>Set conference name and year to start a workspace.</p>
        </article>

        <div class="ai-manual-actions">
            <button type="button" class="button button-muted" data-ai-manual-prev>Previous</button>
            <button type="button" class="button button-muted" data-ai-manual-next>Next</button>
            <button type="button" class="button" data-ai-manual-complete>Mark Done</button>
        </div>

        <a class="button ai-manual-open-link" data-ai-manual-open href="<?= e(url('dashboard')) ?>">Open Current Step</a>
    </section>
</div>
<?php else: ?>
</div>
<?php endif; ?>

<script src="assets/js/main.js?v=2.5.1"></script>
<?php if (($currentPage ?? '') === 'field-editor'): ?>
    <script src="assets/js/field-editor.js?v=2.3.0"></script>
<?php endif; ?>
</body>
</html>
