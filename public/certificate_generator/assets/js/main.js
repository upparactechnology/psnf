(function () {
    "use strict";

    var confirmButtons = document.querySelectorAll("[data-confirm]:not(form)");
    confirmButtons.forEach(function (button) {
        button.addEventListener("click", function (event) {
            var message = button.getAttribute("data-confirm") || "Are you sure?";
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    var confirmForms = document.querySelectorAll("form[data-confirm]");
    confirmForms.forEach(function (form) {
        form.addEventListener("submit", function (event) {
            var message = form.getAttribute("data-confirm") || "Are you sure?";
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    var progressBars = document.querySelectorAll("[data-progress]");
    progressBars.forEach(function (element) {
        var value = Number(element.getAttribute("data-progress"));
        if (Number.isNaN(value)) {
            return;
        }

        var clamped = Math.max(0, Math.min(100, value));
        element.style.width = clamped + "%";
    });

    var sidebar = document.getElementById("appSidebar");
    var sidebarOverlay = document.getElementById("sidebarOverlay");
    var sidebarToggles = document.querySelectorAll("[data-sidebar-toggle]");

    var setSidebarState = function (isOpen) {
        if (!sidebar || !sidebarOverlay) {
            return;
        }

        sidebar.classList.toggle("is-open", isOpen);
        sidebarOverlay.hidden = !isOpen;

        sidebarToggles.forEach(function (button) {
            button.setAttribute("aria-expanded", isOpen ? "true" : "false");
        });
    };

    sidebarToggles.forEach(function (button) {
        button.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (!sidebar) {
                return;
            }

            setSidebarState(!sidebar.classList.contains("is-open"));
        });
    });

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener("click", function () {
            setSidebarState(false);
        });
    }

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            setSidebarState(false);
        }
    });

    window.addEventListener("resize", function () {
        if (window.innerWidth > 1160) {
            setSidebarState(false);
        }
    });

    var dropdowns = document.querySelectorAll("[data-dropdown]");
    var clearTemplateMenuState = function () {
        var openTemplateCards = document.querySelectorAll("[data-template-card].is-menu-open");
        openTemplateCards.forEach(function (card) {
            card.classList.remove("is-menu-open");
        });
    };

    dropdowns.forEach(function (dropdown) {
        var trigger = dropdown.querySelector("[data-dropdown-trigger]");
        var menu = dropdown.querySelector("[data-dropdown-menu]");

        if (!trigger || !menu) {
            return;
        }

        trigger.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();

            dropdowns.forEach(function (item) {
                if (item !== dropdown) {
                    item.classList.remove("open");

                    var otherTemplateCard = item.closest("[data-template-card]");
                    if (otherTemplateCard) {
                        otherTemplateCard.classList.remove("is-menu-open");
                    }
                }
            });

            dropdown.classList.toggle("open");

            var templateCard = dropdown.closest("[data-template-card]");
            if (templateCard) {
                templateCard.classList.toggle("is-menu-open", dropdown.classList.contains("open"));
            }
        });
    });

    document.addEventListener("click", function () {
        dropdowns.forEach(function (dropdown) {
            dropdown.classList.remove("open");
        });

        clearTemplateMenuState();
    });

    var templateShells = document.querySelectorAll("[data-template-shell]");
    templateShells.forEach(function (shell) {
        var cards = shell.querySelectorAll("[data-template-card]");
        var searchInput = shell.querySelector("[data-template-search]");
        var filterButtons = shell.querySelectorAll("[data-template-filter]");
        var noResults = shell.querySelector("[data-template-no-results]");
        var activeFilter = "all";

        if (cards.length === 0) {
            return;
        }

        var applyTemplateFilters = function () {
            var keyword = "";
            var visibleCount = 0;

            if (searchInput) {
                keyword = (searchInput.value || "").trim().toLowerCase();
            }

            cards.forEach(function (card) {
                var templateName = (card.getAttribute("data-template-name") || "").toLowerCase();
                var category = (card.getAttribute("data-template-category") || "general").toLowerCase();
                var matchesSearch = keyword === "" || templateName.indexOf(keyword) !== -1;
                var matchesCategory = activeFilter === "all" || category === activeFilter;
                var visible = matchesSearch && matchesCategory;

                card.hidden = !visible;
                if (visible) {
                    visibleCount++;
                }
            });

            if (noResults) {
                noResults.hidden = visibleCount > 0;
            }
        };

        if (searchInput) {
            searchInput.addEventListener("input", applyTemplateFilters);
        }

        filterButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                activeFilter = button.getAttribute("data-template-filter") || "all";

                filterButtons.forEach(function (pill) {
                    var isActive = pill === button;
                    pill.classList.toggle("is-active", isActive);
                    pill.setAttribute("aria-selected", isActive ? "true" : "false");
                });

                applyTemplateFilters();
            });
        });

        applyTemplateFilters();
    });

    var recipientBulkForm = document.getElementById("recipientBulkActionForm");
    if (recipientBulkForm) {
        var selectAllRecipients = document.getElementById("recipientSelectAll");
        var recipientCheckboxes = Array.prototype.slice.call(document.querySelectorAll("[data-recipient-checkbox]"));
        var recipientBulkBar = document.getElementById("recipientBulkBar");
        var recipientBulkCount = document.getElementById("recipientBulkCount");
        var recipientBulkActionInput = document.getElementById("recipientBulkActionInput");
        var recipientBulkButtons = recipientBulkBar ? recipientBulkBar.querySelectorAll("[data-recipient-bulk-action]") : [];
        var recipientBulkClear = recipientBulkBar ? recipientBulkBar.querySelector("[data-recipient-bulk-clear]") : null;

        var getSelectedRecipientIds = function () {
            return recipientCheckboxes
                .filter(function (checkbox) {
                    return checkbox.checked;
                })
                .map(function (checkbox) {
                    return checkbox.value;
                });
        };

        var syncRecipientBulkState = function () {
            var selectedIds = getSelectedRecipientIds();
            var selectedCount = selectedIds.length;

            if (selectAllRecipients) {
                selectAllRecipients.checked = selectedCount > 0 && selectedCount === recipientCheckboxes.length;
                selectAllRecipients.indeterminate = selectedCount > 0 && selectedCount < recipientCheckboxes.length;
            }

            if (recipientBulkBar) {
                recipientBulkBar.hidden = selectedCount === 0;
            }

            if (recipientBulkCount) {
                recipientBulkCount.textContent = String(selectedCount);
            }
        };

        var setRecipientHiddenIds = function (ids) {
            var existing = recipientBulkForm.querySelectorAll("input[name='participant_ids[]']");
            existing.forEach(function (input) {
                input.remove();
            });

            ids.forEach(function (id) {
                var hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "participant_ids[]";
                hiddenInput.value = id;
                recipientBulkForm.appendChild(hiddenInput);
            });
        };

        recipientCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener("change", syncRecipientBulkState);
        });

        if (selectAllRecipients) {
            selectAllRecipients.addEventListener("change", function () {
                recipientCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = selectAllRecipients.checked;
                });

                syncRecipientBulkState();
            });
        }

        if (recipientBulkClear) {
            recipientBulkClear.addEventListener("click", function () {
                recipientCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = false;
                });

                if (selectAllRecipients) {
                    selectAllRecipients.checked = false;
                    selectAllRecipients.indeterminate = false;
                }

                syncRecipientBulkState();
            });
        }

        recipientBulkButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                var action = button.getAttribute("data-recipient-bulk-action") || "";
                var selectedIds = getSelectedRecipientIds();

                if (!action || selectedIds.length === 0 || !recipientBulkActionInput) {
                    return;
                }

                if (action === "delete_selected" && !window.confirm("Delete selected recipients?")) {
                    return;
                }

                setRecipientHiddenIds(selectedIds);
                recipientBulkActionInput.value = action;
                recipientBulkForm.submit();
            });
        });

        syncRecipientBulkState();
    }

    var aiManualRoot = document.querySelector("[data-ai-manual]");
    if (aiManualRoot) {
        var aiSteps = [
            {
                id: "conference",
                page: "conferences",
                title: "Create Conference",
                description: "Set conference name and year to start a workspace."
            },
            {
                id: "template",
                page: "certificate-types",
                title: "Build Template",
                description: "Create certificate template and upload design file."
            },
            {
                id: "editor",
                page: "field-editor",
                title: "Map Fields",
                description: "Place name, title, date and verify code positions."
            },
            {
                id: "import",
                page: "participants-import",
                title: "Import Recipients",
                description: "Upload CSV and map columns before saving data."
            },
            {
                id: "generate",
                page: "generate",
                title: "Generate Certificates",
                description: "Run generation and confirm files are created."
            },
            {
                id: "emails",
                page: "emails",
                title: "Send Emails",
                description: "Send delivery emails and track failures."
            },
            {
                id: "analytics",
                page: "downloads",
                title: "Review Analytics",
                description: "Check trends, downloads and delivery summary."
            },
            {
                id: "verify",
                page: "verify",
                title: "Verify Publicly",
                description: "Use verify page to validate issued certificate codes."
            }
        ];
        var aiStorageKey = "certificate.aiManual.v1";
        var aiCurrentPage = (aiManualRoot.getAttribute("data-current-page") || "dashboard").toLowerCase();

        var aiToggle = aiManualRoot.querySelector("[data-ai-manual-toggle]");
        var aiPanel = aiManualRoot.querySelector("[data-ai-manual-panel]");
        var aiClose = aiManualRoot.querySelector("[data-ai-manual-close]");
        var aiCurrentStepCard = aiManualRoot.querySelector("[data-ai-manual-current-step]");
        var aiStepBadge = aiManualRoot.querySelector("[data-ai-manual-step-badge]");
        var aiStepStatus = aiManualRoot.querySelector("[data-ai-manual-step-status]");
        var aiStepTitle = aiManualRoot.querySelector("[data-ai-manual-step-title]");
        var aiStepDescription = aiManualRoot.querySelector("[data-ai-manual-step-description]");
        var aiProgressLabel = aiManualRoot.querySelector("[data-ai-manual-progress-label]");
        var aiProgressFill = aiManualRoot.querySelector("[data-ai-manual-progress-fill]");
        var aiPrev = aiManualRoot.querySelector("[data-ai-manual-prev]");
        var aiNext = aiManualRoot.querySelector("[data-ai-manual-next]");
        var aiComplete = aiManualRoot.querySelector("[data-ai-manual-complete]");
        var aiOpen = aiManualRoot.querySelector("[data-ai-manual-open]");

        var aiState = {
            currentIndex: 0,
            completedIds: []
        };

        var aiSaveState = function () {
            try {
                window.localStorage.setItem(aiStorageKey, JSON.stringify(aiState));
            } catch (error) {
                // Ignore localStorage issues and keep runtime state only.
            }
        };

        var aiLoadState = function () {
            try {
                var raw = window.localStorage.getItem(aiStorageKey);
                if (!raw) {
                    return;
                }

                var parsed = JSON.parse(raw);
                if (typeof parsed !== "object" || parsed === null) {
                    return;
                }

                if (typeof parsed.currentIndex === "number") {
                    aiState.currentIndex = parsed.currentIndex;
                }

                if (Array.isArray(parsed.completedIds)) {
                    aiState.completedIds = parsed.completedIds.filter(function (id) {
                        return typeof id === "string";
                    });
                }
            } catch (error) {
                aiState = {
                    currentIndex: 0,
                    completedIds: []
                };
            }
        };

        var aiIsCompleted = function (stepId) {
            return aiState.completedIds.indexOf(stepId) !== -1;
        };

        var aiMarkCompleted = function (stepId) {
            if (!aiIsCompleted(stepId)) {
                aiState.completedIds.push(stepId);
            }
        };

        var aiClampIndex = function () {
            if (aiState.currentIndex < 0) {
                aiState.currentIndex = 0;
            }

            if (aiState.currentIndex >= aiSteps.length) {
                aiState.currentIndex = aiSteps.length - 1;
            }
        };

        var aiSetOpen = function (isOpen) {
            if (!aiPanel || !aiToggle) {
                return;
            }

            aiPanel.hidden = !isOpen;
            aiToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
            aiManualRoot.classList.toggle("is-open", isOpen);
        };

        var aiIsOpen = function () {
            return Boolean(aiPanel) && aiPanel.hidden === false;
        };

        var aiTogglePanel = function () {
            aiSetOpen(!aiIsOpen());
        };

        var aiMoveToCurrentPageStep = function () {
            var matchedIndex = -1;

            aiSteps.forEach(function (step, index) {
                if (step.page === aiCurrentPage) {
                    matchedIndex = index;
                }
            });

            if (matchedIndex >= 0 && !aiIsCompleted(aiSteps[matchedIndex].id)) {
                aiState.currentIndex = matchedIndex;
            }
        };

        var aiRender = function () {
            if (!aiProgressLabel || !aiProgressFill || !aiOpen || !aiStepTitle || !aiStepDescription) {
                return;
            }

            aiClampIndex();

            var completedCount = aiState.completedIds.length;
            var progressPercent = aiSteps.length > 0 ? Math.round((completedCount / aiSteps.length) * 100) : 0;
            aiProgressLabel.textContent = String(completedCount) + " / " + String(aiSteps.length) + " completed";
            aiProgressFill.style.width = String(progressPercent) + "%";

            var currentStep = aiSteps[aiState.currentIndex];
            var isCurrentDone = aiIsCompleted(currentStep.id);

            aiStepTitle.textContent = currentStep.title;
            aiStepDescription.textContent = currentStep.description;
            if (aiStepBadge) {
                aiStepBadge.textContent = "Step " + String(aiState.currentIndex + 1) + " of " + String(aiSteps.length);
            }

            if (aiStepStatus) {
                aiStepStatus.textContent = isCurrentDone ? "Done" : "Pending";
                aiStepStatus.classList.toggle("is-done", isCurrentDone);
            }

            if (aiCurrentStepCard) {
                aiCurrentStepCard.classList.toggle("is-done", isCurrentDone);
            }

            aiOpen.setAttribute("href", "index.php?page=" + encodeURIComponent(currentStep.page));
            aiOpen.textContent = "Open: " + currentStep.title;

            if (aiPrev) {
                aiPrev.disabled = aiState.currentIndex === 0;
            }

            if (aiNext) {
                aiNext.disabled = aiState.currentIndex >= aiSteps.length - 1;
            }

            if (aiComplete) {
                aiComplete.disabled = isCurrentDone;
                aiComplete.textContent = isCurrentDone ? "Completed" : "Mark Done";
            }
        };

        aiLoadState();
        aiMoveToCurrentPageStep();
        aiClampIndex();
        aiSaveState();
        aiRender();

        if (aiToggle) {
            aiToggle.addEventListener("click", function (event) {
                event.preventDefault();
                event.stopPropagation();
                aiTogglePanel();
            });
        }

        if (aiClose) {
            aiClose.addEventListener("click", function (event) {
                event.preventDefault();
                event.stopPropagation();
                aiSetOpen(false);
            });
        }

        if (aiPrev) {
            aiPrev.addEventListener("click", function () {
                aiState.currentIndex -= 1;
                aiClampIndex();
                aiSaveState();
                aiRender();
            });
        }

        if (aiNext) {
            aiNext.addEventListener("click", function () {
                aiState.currentIndex += 1;
                aiClampIndex();
                aiSaveState();
                aiRender();
            });
        }

        if (aiComplete) {
            aiComplete.addEventListener("click", function () {
                var currentStep = aiSteps[aiState.currentIndex];
                aiMarkCompleted(currentStep.id);

                if (aiState.currentIndex < aiSteps.length - 1) {
                    aiState.currentIndex += 1;
                }

                aiSaveState();
                aiRender();
            });
        }

        document.addEventListener("click", function (event) {
            if (!aiManualRoot.classList.contains("is-open")) {
                return;
            }

            if (event.target instanceof Node && !aiManualRoot.contains(event.target)) {
                aiSetOpen(false);
            }
        });
    }

    var toasts = document.querySelectorAll("[data-toast]");
    toasts.forEach(function (toast) {
        var closeButton = toast.querySelector("[data-toast-close]");

        if (closeButton) {
            closeButton.addEventListener("click", function () {
                toast.remove();
            });
        }

        window.setTimeout(function () {
            if (!toast.isConnected) {
                return;
            }

            toast.style.opacity = "0";
            toast.style.transform = "translateY(-4px)";
            toast.style.transition = "opacity 0.2s ease, transform 0.2s ease";

            window.setTimeout(function () {
                if (toast.isConnected) {
                    toast.remove();
                }
            }, 220);
        }, 4500);
    });
})();
