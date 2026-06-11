const body = document.body;
const storageKey = 'project-control-theme';
const supportedThemes = ['industrial-clean', 'dark-steel', 'control-room', 'green-schneider'];
const themeSwitcher = document.querySelector('[data-theme-switcher]');
const initialTheme = supportedThemes.includes(localStorage.getItem(storageKey))
    ? localStorage.getItem(storageKey)
    : body.dataset.theme;

setTheme(initialTheme);

if (themeSwitcher) {
    themeSwitcher.addEventListener('change', () => {
        setTheme(themeSwitcher.value);
        localStorage.setItem(storageKey, themeSwitcher.value);
    });
}

function setTheme(theme) {
    body.dataset.theme = theme;

    if (themeSwitcher) {
        themeSwitcher.value = theme;
    }
}

document.querySelectorAll('[data-tv-auto-scroll]').forEach((container) => {
    let pauseTicks = 0;
    let isUserPaused = false;

    container.addEventListener('mouseenter', () => {
        isUserPaused = true;
    });

    container.addEventListener('mouseleave', () => {
        isUserPaused = false;
    });

    container.addEventListener('focusin', () => {
        isUserPaused = true;
    });

    container.addEventListener('focusout', () => {
        isUserPaused = container.matches(':hover');
    });

    window.setInterval(() => {
        const maxScroll = container.scrollHeight - container.clientHeight;

        if (maxScroll <= 4) {
            container.scrollTop = 0;
            return;
        }

        if (isUserPaused) {
            return;
        }

        if (pauseTicks > 0) {
            pauseTicks -= 1;
            return;
        }

        container.scrollTop += 1;

        if (container.scrollTop >= maxScroll - 1) {
            pauseTicks = 24;
            window.setTimeout(() => {
                container.scrollTop = 0;
            }, 1200);
        }
    }, 80);
});

document.querySelectorAll('[data-tv-project-url]').forEach((row) => {
    const openProject = () => {
        const url = row.dataset.tvProjectUrl;

        if (url) {
            window.location.href = url;
        }
    };

    row.addEventListener('click', openProject);
    row.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openProject();
        }
    });
});

document.querySelectorAll('.permission-select').forEach((select) => {
    select.addEventListener('change', () => {
        select.classList.toggle('is-allowed', select.value === '1');
        select.classList.toggle('is-denied', select.value !== '1');
    });
});

document.querySelectorAll('[data-checklist-field]').forEach((input) => {
    input.addEventListener('paste', (event) => {
        const text = event.clipboardData?.getData('text/plain') ?? '';

        if (!text.includes('\t') && !text.includes('\n')) {
            return;
        }

        const tbody = input.closest('tbody');
        const currentRow = input.closest('tr');

        if (!tbody || !currentRow) {
            return;
        }

        event.preventDefault();

        const rows = Array.from(tbody.querySelectorAll('tr[data-checklist-row]'));
        const startRowIndex = rows.indexOf(currentRow);
        const fieldOrder = ['document_link', 'target_start', 'target_finish'];
        const startFieldIndex = Math.max(0, fieldOrder.indexOf(input.dataset.checklistField));
        const pastedRows = text
            .replace(/\r/g, '')
            .split('\n')
            .filter((row) => row.length > 0)
            .map((row) => row.split('\t'));

        pastedRows.forEach((columns, rowOffset) => {
            const targetRow = rows[startRowIndex + rowOffset];

            if (!targetRow) {
                return;
            }

            columns.forEach((value, columnOffset) => {
                const field = fieldOrder[startFieldIndex + columnOffset];

                if (!field) {
                    return;
                }

                const targetInput = targetRow.querySelector(`[data-checklist-field="${field}"]`);

                if (!targetInput) {
                    return;
                }

                targetInput.value = field.startsWith('target_') ? normalizeSpreadsheetDate(value.trim()) : value.trim();
                targetInput.dispatchEvent(new Event('input', { bubbles: true }));
                targetInput.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    });
});

const checklistRows = Array.from(document.querySelectorAll('[data-checklist-row]'));

if (checklistRows.length > 0) {
    const collapsedChecklistIds = new Set();
    const checklistRowById = new Map(checklistRows.map((row) => [row.dataset.checklistId, row]));
    const checklistToggleButtons = Array.from(document.querySelectorAll('[data-checklist-toggle]'));

    checklistToggleButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const checklistId = button.dataset.checklistToggle;

            if (!checklistId) {
                return;
            }

            if (collapsedChecklistIds.has(checklistId)) {
                collapsedChecklistIds.delete(checklistId);
                button.setAttribute('aria-expanded', 'true');
            } else {
                collapsedChecklistIds.add(checklistId);
                button.setAttribute('aria-expanded', 'false');
            }

            syncChecklistTreeVisibility();
        });
    });

    document.querySelectorAll('[data-checklist-add-child]').forEach((button) => {
        button.addEventListener('click', () => {
            const checklistId = button.dataset.checklistAddChild;
            const formRow = document.querySelector(`[data-checklist-child-form="${checklistId}"]`);

            if (!formRow) {
                return;
            }

            formRow.hidden = !formRow.hidden;

            if (!formRow.hidden) {
                formRow.querySelector('input[name="label"]')?.focus();
            }
        });
    });

    syncChecklistTreeVisibility();

    function syncChecklistTreeVisibility() {
        checklistRows.forEach((row) => {
            const isHidden = hasCollapsedAncestor(row);
            row.hidden = isHidden;

            if (isHidden) {
                const formRow = document.querySelector(`[data-checklist-child-form="${row.dataset.checklistId}"]`);

                if (formRow) {
                    formRow.hidden = true;
                }
            }
        });
    }

    function hasCollapsedAncestor(row) {
        let parentId = row.dataset.checklistParentId;

        while (parentId) {
            if (collapsedChecklistIds.has(parentId)) {
                return true;
            }

            parentId = checklistRowById.get(parentId)?.dataset.checklistParentId;
        }

        return false;
    }
}

function normalizeSpreadsheetDate(value) {
    if (!value) {
        return '';
    }

    if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
        return value;
    }

    const match = value.match(/^(\d{1,2})[/-](\d{1,2})[/-](\d{2,4})$/);

    if (!match) {
        return value;
    }

    let [, first, second, year] = match;

    if (year.length === 2) {
        year = `20${year}`;
    }

    const firstNumber = Number(first);
    const secondNumber = Number(second);
    const isDayFirst = firstNumber > 12 && secondNumber <= 12;
    const month = (isDayFirst ? second : first).padStart(2, '0');
    const day = (isDayFirst ? first : second).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

const commentModal = document.querySelector('[data-comment-modal]');
const commentModalOpener = document.querySelector('[data-comment-modal-open]');

if (commentModal && commentModalOpener) {
    const closeButtons = Array.from(document.querySelectorAll('[data-comment-modal-close]'));

    commentModalOpener.addEventListener('click', () => {
        commentModal.hidden = false;
        document.body.style.overflow = 'hidden';
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            commentModal.hidden = true;
            document.body.style.overflow = '';
        });
    });

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !commentModal.hidden) {
            commentModal.hidden = true;
            document.body.style.overflow = '';
        }
    });
}

const scrollPreservePage = document.querySelector('[data-preserve-scroll-page]');

if (scrollPreservePage) {
    const scrollStateKey = `project-control-scroll:${window.location.pathname}${window.location.search}`;
    const scrollContainers = Array.from(scrollPreservePage.querySelectorAll('[data-preserve-scroll-container]'));
    const forms = Array.from(scrollPreservePage.querySelectorAll('form'));

    restoreScrollState();

    forms.forEach((form) => {
        form.addEventListener('submit', () => {
            storeScrollState();
        });
    });

    window.addEventListener('beforeunload', storeScrollState);
    window.projectControlStoreScrollState = storeScrollState;

    function storeScrollState() {
        const payload = {
            windowX: window.scrollX,
            windowY: window.scrollY,
            containers: scrollContainers.map((container) => ({
                id: container.dataset.preserveScrollContainer,
                left: container.scrollLeft,
                top: container.scrollTop,
            })),
        };

        sessionStorage.setItem(scrollStateKey, JSON.stringify(payload));
    }

    function restoreScrollState() {
        const rawState = sessionStorage.getItem(scrollStateKey);

        if (!rawState) {
            return;
        }

        let state;

        try {
            state = JSON.parse(rawState);
        } catch (error) {
            sessionStorage.removeItem(scrollStateKey);
            return;
        }

        if ('scrollRestoration' in window.history) {
            window.history.scrollRestoration = 'manual';
        }

        const applyState = () => {
            window.scrollTo(state.windowX ?? 0, state.windowY ?? 0);

            if (Array.isArray(state.containers)) {
                state.containers.forEach((entry) => {
                    if (!entry?.id) {
                        return;
                    }

                    const container = scrollPreservePage.querySelector(`[data-preserve-scroll-container="${entry.id}"]`);

                    if (!container) {
                        return;
                    }

                    container.scrollLeft = entry.left ?? 0;
                    container.scrollTop = entry.top ?? 0;
                });
            }
        };

        window.requestAnimationFrame(() => {
            applyState();
            window.setTimeout(applyState, 120);
            window.setTimeout(() => {
                sessionStorage.removeItem(scrollStateKey);
            }, 240);
        });
    }
}

const flowLayoutEditor = document.querySelector('[data-flow-layout-editor]');

if (flowLayoutEditor) {
    const saveButton = document.querySelector('[data-layout-save]');
    const resetButton = document.querySelector('[data-layout-reset]');
    const stage = flowLayoutEditor.querySelector('[data-layout-stage]');
    const linesSvg = flowLayoutEditor.querySelector('[data-layout-lines]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const nodeSize = { width: 160, height: 88 };
    const gridSnap = { x: 20, y: 20 };
    const stageMargin = 20;
    const edgeOverlap = 0;
    const rawSteps = JSON.parse(flowLayoutEditor.dataset.steps ?? '[]');
    const rawConnections = JSON.parse(flowLayoutEditor.dataset.connections ?? '[]');
    const steps = rawSteps.map((step) => ({
        ...step,
        id: Number(step.id),
        original_x: Number(step.position_x),
        original_y: Number(step.position_y),
        position_x: Number(step.position_x),
        position_y: Number(step.position_y),
    }));
    const connections = rawConnections.map((connection) => ({
        ...connection,
        id: Number(connection.id),
        from_id: Number(connection.from_id),
        to_id: Number(connection.to_id),
        start_x: connection.start_x === null ? null : Number(connection.start_x),
        start_y: connection.start_y === null ? null : Number(connection.start_y),
        bend_x: connection.bend_x === null ? null : Number(connection.bend_x),
        bend_y: connection.bend_y === null ? null : Number(connection.bend_y),
        mid2_x: connection.mid2_x === null ? null : Number(connection.mid2_x),
        mid2_y: connection.mid2_y === null ? null : Number(connection.mid2_y),
        end_x: connection.end_x === null ? null : Number(connection.end_x),
        end_y: connection.end_y === null ? null : Number(connection.end_y),
        original_start_x: connection.start_x === null ? null : Number(connection.start_x),
        original_start_y: connection.start_y === null ? null : Number(connection.start_y),
        original_bend_x: connection.bend_x === null ? null : Number(connection.bend_x),
        original_bend_y: connection.bend_y === null ? null : Number(connection.bend_y),
        original_mid2_x: connection.mid2_x === null ? null : Number(connection.mid2_x),
        original_mid2_y: connection.mid2_y === null ? null : Number(connection.mid2_y),
        original_end_x: connection.end_x === null ? null : Number(connection.end_x),
        original_end_y: connection.end_y === null ? null : Number(connection.end_y),
    }));
    const nodeMap = new Map();
    const handleMap = new Map();

    function getBounds() {
        return {
            width: stage.clientWidth || 1200,
            height: stage.clientHeight || 760,
        };
    }

    function syncStageGeometry() {
        const bounds = getBounds();
        linesSvg.setAttribute('viewBox', `0 0 ${bounds.width} ${bounds.height}`);
    }

    function getEditorLimits() {
        const bounds = getBounds();

        return {
            minX: pixelToPercentX(stageMargin + (nodeSize.width / 2)),
            maxX: pixelToPercentX(bounds.width - stageMargin - (nodeSize.width / 2)),
            minY: pixelToPercentY(stageMargin),
            maxY: pixelToPercentY(bounds.height - stageMargin - nodeSize.height),
        };
    }

    renderNodes();
    syncStageGeometry();
    renderLines();

    window.addEventListener('resize', () => {
        steps.forEach(clampStepPosition);
        syncStageGeometry();
        steps.forEach(syncNodePosition);
        renderLines();
    });

    saveButton?.addEventListener('click', async () => {
        saveButton.disabled = true;
        saveButton.textContent = 'Menyimpan...';

        try {
            const response = await fetch(flowLayoutEditor.dataset.saveUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ?? '',
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    steps: steps.map((step) => ({
                        id: step.id,
                        position_x: Number(step.position_x.toFixed(1)),
                        position_y: Number(step.position_y.toFixed(1)),
                    })),
                    connections: connections.map((connection) => ({
                        id: connection.id,
                        start_x: connection.start_x === null ? null : Number(connection.start_x.toFixed(1)),
                        start_y: connection.start_y === null ? null : Number(connection.start_y.toFixed(1)),
                        bend_x: connection.bend_x === null ? null : Number(connection.bend_x.toFixed(1)),
                        bend_y: connection.bend_y === null ? null : Number(connection.bend_y.toFixed(1)),
                        mid2_x: connection.mid2_x === null ? null : Number(connection.mid2_x.toFixed(1)),
                        mid2_y: connection.mid2_y === null ? null : Number(connection.mid2_y.toFixed(1)),
                        end_x: connection.end_x === null ? null : Number(connection.end_x.toFixed(1)),
                        end_y: connection.end_y === null ? null : Number(connection.end_y.toFixed(1)),
                    })),
                }),
            });

            if (!response.ok) {
                let message = 'Layout master flow belum berhasil disimpan.';

                try {
                    const payload = await response.json();

                    if (payload?.message) {
                        message = payload.message;
                    }

                    const firstError = Object.values(payload?.errors ?? {})[0]?.[0];
                    if (firstError) {
                        message = firstError;
                    }
                } catch (error) {
                    // ignore JSON parse issue and use generic message
                }

                throw new Error(message);
            }

            steps.forEach((step) => {
                step.original_x = step.position_x;
                step.original_y = step.position_y;
            });
            connections.forEach((connection) => {
                connection.original_start_x = connection.start_x;
                connection.original_start_y = connection.start_y;
                connection.original_bend_x = connection.bend_x;
                connection.original_bend_y = connection.bend_y;
                connection.original_mid2_x = connection.mid2_x;
                connection.original_mid2_y = connection.mid2_y;
                connection.original_end_x = connection.end_x;
                connection.original_end_y = connection.end_y;
            });

            saveButton.textContent = 'Tersimpan';
            window.setTimeout(() => {
                saveButton.textContent = 'Simpan Layout';
            }, 1200);
        } catch (error) {
            saveButton.textContent = 'Coba Lagi';
            window.alert(error.message || 'Layout master flow belum berhasil disimpan.');
        } finally {
            window.setTimeout(() => {
                saveButton.disabled = false;
                if (saveButton.textContent !== 'Simpan Layout') {
                    saveButton.textContent = 'Simpan Layout';
                }
            }, 1200);
        }
    });

    resetButton?.addEventListener('click', () => {
        steps.forEach((step) => {
            step.position_x = step.original_x;
            step.position_y = step.original_y;
            syncNodePosition(step);
        });
        connections.forEach((connection) => {
            connection.start_x = connection.original_start_x;
            connection.start_y = connection.original_start_y;
            connection.bend_x = connection.original_bend_x;
            connection.bend_y = connection.original_bend_y;
            connection.mid2_x = connection.original_mid2_x;
            connection.mid2_y = connection.original_mid2_y;
            connection.end_x = connection.original_end_x;
            connection.end_y = connection.original_end_y;
        });
        renderLines();
    });

    function renderNodes() {
        steps.forEach((step) => {
            clampStepPosition(step);

            const node = document.createElement('button');
            node.type = 'button';
            node.className = `flow-node flow-node-${step.status} flow-node-editor`;
            node.dataset.stepId = String(step.id);
            node.innerHTML = `
                <span class="flow-node-badge">STEP</span>
                <strong>${step.name}</strong>
                <small>X: <span data-x>${step.position_x.toFixed(1)}</span>% | Y: <span data-y>${step.position_y.toFixed(1)}</span>%</small>
                <span class="flow-node-status-icon flow-node-status-icon-${step.status}">${getStatusIcon(step.status)}</span>
            `;

            makeDraggable(node, step);
            stage.appendChild(node);
            nodeMap.set(step.id, node);
            syncNodePosition(step);
        });
    }

    function makeDraggable(node, step) {
        let dragging = false;

        node.addEventListener('pointerdown', (event) => {
            dragging = true;
            node.setPointerCapture(event.pointerId);
            node.classList.add('is-dragging');
        });

        node.addEventListener('pointermove', (event) => {
            if (!dragging) {
                return;
            }

            const rect = stage.getBoundingClientRect();
            const rawX = event.clientX - rect.left;
            const rawY = event.clientY - rect.top;
            const snappedX = snapToGrid(rawX, gridSnap.x);
            const snappedY = snapToGrid(rawY, gridSnap.y);
            const limits = getEditorLimits();

            step.position_x = clamp(pixelToPercentX(snappedX), limits.minX, limits.maxX);
            step.position_y = clamp(pixelToPercentY(snappedY), limits.minY, limits.maxY);

            syncNodePosition(step);
            renderLines();
        });

        const stopDrag = (event) => {
            if (!dragging) {
                return;
            }

            dragging = false;
            node.classList.remove('is-dragging');

            if (event.pointerId !== undefined) {
                node.releasePointerCapture(event.pointerId);
            }
        };

        node.addEventListener('pointerup', stopDrag);
        node.addEventListener('pointercancel', stopDrag);
    }

    function syncNodePosition(step) {
        clampStepPosition(step);

        const node = nodeMap.get(step.id);
        if (!node) {
            return;
        }

        node.style.left = `${step.position_x}%`;
        node.style.top = `${step.position_y}%`;
        node.querySelector('[data-x]').textContent = step.position_x.toFixed(1);
        node.querySelector('[data-y]').textContent = step.position_y.toFixed(1);
    }

    function clampStepPosition(step) {
        const limits = getEditorLimits();
        step.position_x = clamp(step.position_x, limits.minX, limits.maxX);
        step.position_y = clamp(step.position_y, limits.minY, limits.maxY);
    }

    function renderLines() {
        linesSvg.querySelectorAll('[data-connection]').forEach((line) => line.remove());

        connections.forEach((connection) => {
            const fromStep = steps.find((step) => step.id === connection.from_id);
            const toStep = steps.find((step) => step.id === connection.to_id);

            if (!fromStep || !toStep) {
                return;
            }

            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('class', 'flow-line flow-line-editor');
            path.setAttribute('marker-end', 'url(#editor-arrow)');
            path.setAttribute('fill', 'none');
            path.setAttribute('data-connection', `${connection.id}`);
            path.setAttribute('d', buildConnectorPath(connection, fromStep, toStep));
            linesSvg.appendChild(path);

            renderConnectionHandles(connection, fromStep, toStep);
        });
    }

    function buildConnectorPath(connection, fromStep, toStep) {
        const fromBox = getNodeBox(fromStep);
        const toBox = getNodeBox(toStep);
        const dx = toBox.cx - fromBox.cx;
        const dy = toBox.cy - fromBox.cy;
        const isVerticalPriority = Math.abs(dx) <= 110 || Math.abs(dy) > Math.abs(dx);

        if (hasManualPoints(connection)) {
            const points = getConnectionPoints(connection, fromBox, toBox);

            return `M ${points.start.x} ${points.start.y} L ${points.middle1.x} ${points.middle1.y} L ${points.middle2.x} ${points.middle2.y} L ${points.end.x} ${points.end.y}`;
        }

        if (isVerticalPriority) {
            if (dy >= 0) {
                const startX = fromBox.cx;
                const startY = fromBox.bottom;
                const endX = toBox.cx;
                const endY = toBox.top;
                const midY = startY + (endY - startY) / 2;

                return `M ${startX} ${startY} L ${startX} ${midY} L ${endX} ${midY} L ${endX} ${endY}`;
            }

            const startX = fromBox.cx;
            const startY = fromBox.top;
            const endX = toBox.cx;
            const endY = toBox.bottom;
            const midY = endY + (startY - endY) / 2;

            return `M ${startX} ${startY} L ${startX} ${midY} L ${endX} ${midY} L ${endX} ${endY}`;
        }

        if (dx >= 0) {
            const startX = fromBox.right;
            const startY = fromBox.cy;
            const endX = toBox.left;
            const endY = toBox.cy;
            const midX = startX + (endX - startX) / 2;

            return `M ${startX} ${startY} L ${midX} ${startY} L ${midX} ${endY} L ${endX} ${endY}`;
        }

        const startX = fromBox.left;
        const startY = fromBox.cy;
        const endX = toBox.right;
        const endY = toBox.cy;
        const midX = endX + (startX - endX) / 2;

        return `M ${startX} ${startY} L ${midX} ${startY} L ${midX} ${endY} L ${endX} ${endY}`;
    }

    function getNodeBox(step) {
        const bounds = getBounds();
        const left = (step.position_x / 100) * bounds.width - nodeSize.width / 2;
        const top = (step.position_y / 100) * bounds.height;

        return {
            left,
            top,
            right: left + nodeSize.width,
            bottom: top + nodeSize.height,
            cx: left + nodeSize.width / 2,
            cy: top + nodeSize.height / 2,
        };
    }

    function renderConnectionHandles(connection, fromStep, toStep) {
        const pointSet = getConnectionHandleSet(connection, fromStep, toStep);
        const fromBox = getNodeBox(fromStep);
        const toBox = getNodeBox(toStep);

        ['start', 'middle1', 'middle2', 'end'].forEach((type) => {
            const key = `${connection.id}-${type}`;
            let handle = handleMap.get(key);
            const point = pointSet[type];

            if (!handle) {
                handle = document.createElement('button');
                handle.type = 'button';
                handle.className = `flow-connector-handle flow-connector-handle-${type}`;
                handle.dataset.connectionId = String(connection.id);
                handle.dataset.handleType = type;
                makeHandleDraggable(handle, connection, type);
                stage.appendChild(handle);
                handleMap.set(key, handle);
            }

            handle.style.left = `${point.percentX}%`;
            handle.style.top = `${point.percentY}%`;
            handle.title = `Atur titik ${type} garis ${fromStep.name} ke ${toStep.name}`;

            if (type === 'start') {
                handle.dataset.edge = detectHandleEdge(point, fromBox);
            } else if (type === 'end') {
                handle.dataset.edge = detectHandleEdge(point, toBox);
            } else {
                handle.dataset.edge = 'free';
            }
        });
    }

    function getConnectionHandleSet(connection, fromStep, toStep) {
        const fromBox = getNodeBox(fromStep);
        const toBox = getNodeBox(toStep);
        const defaults = getDefaultConnectionPoints(fromBox, toBox);

        return {
            start: {
                percentX: connection.start_x ?? pixelToPercentX(defaults.start.x),
                percentY: connection.start_y ?? pixelToPercentY(defaults.start.y),
            },
            middle1: {
                percentX: connection.bend_x ?? pixelToPercentX(defaults.middle.x),
                percentY: connection.bend_y ?? pixelToPercentY(defaults.middle.y),
            },
            middle2: {
                percentX: connection.mid2_x ?? pixelToPercentX(defaults.middle2.x),
                percentY: connection.mid2_y ?? pixelToPercentY(defaults.middle2.y),
            },
            end: {
                percentX: connection.end_x ?? pixelToPercentX(defaults.end.x),
                percentY: connection.end_y ?? pixelToPercentY(defaults.end.y),
            },
        };
    }

    function makeHandleDraggable(handle, connection, type) {
        let dragging = false;

        handle.addEventListener('dblclick', () => {
            if (type === 'start') {
                connection.start_x = null;
                connection.start_y = null;
            } else if (type === 'middle1') {
                connection.bend_x = null;
                connection.bend_y = null;
            } else if (type === 'middle2') {
                connection.mid2_x = null;
                connection.mid2_y = null;
            } else {
                connection.end_x = null;
                connection.end_y = null;
            }
            renderLines();
        });

        handle.addEventListener('pointerdown', (event) => {
            dragging = true;
            handle.setPointerCapture(event.pointerId);
            handle.classList.add('is-dragging');
        });

        handle.addEventListener('pointermove', (event) => {
            if (!dragging) {
                return;
            }

            const rect = stage.getBoundingClientRect();
            const rawX = event.clientX - rect.left;
            const rawY = event.clientY - rect.top;
            const snappedX = pixelToPercentX(snapToGrid(rawX, gridSnap.x));
            const snappedY = pixelToPercentY(snapToGrid(rawY, gridSnap.y));

            if (type === 'start') {
                const fromStep = steps.find((step) => step.id === connection.from_id);

                if (!fromStep) {
                    return;
                }

                const snappedPoint = snapPointToNodeEdge(snappedX, snappedY, getNodeBox(fromStep));
                connection.start_x = snappedPoint.x;
                connection.start_y = snappedPoint.y;
            } else if (type === 'middle1') {
                connection.bend_x = clamp(snappedX, 2, 98);
                connection.bend_y = clamp(snappedY, 2, 98);
            } else if (type === 'middle2') {
                connection.mid2_x = clamp(snappedX, 2, 98);
                connection.mid2_y = clamp(snappedY, 2, 98);
            } else {
                const toStep = steps.find((step) => step.id === connection.to_id);

                if (!toStep) {
                    return;
                }

                const snappedPoint = snapPointToNodeEdge(snappedX, snappedY, getNodeBox(toStep));
                connection.end_x = snappedPoint.x;
                connection.end_y = snappedPoint.y;
            }
            renderLines();
        });

        const stopDrag = (event) => {
            if (!dragging) {
                return;
            }

            dragging = false;
            handle.classList.remove('is-dragging');

            if (event.pointerId !== undefined) {
                handle.releasePointerCapture(event.pointerId);
            }
        };

        handle.addEventListener('pointerup', stopDrag);
        handle.addEventListener('pointercancel', stopDrag);
    }

    function getConnectionPoints(connection, fromBox, toBox) {
        const defaults = getDefaultConnectionPoints(fromBox, toBox);

        return {
            start: connection.start_x !== null && connection.start_y !== null
                ? percentPointToPixel(connection.start_x, connection.start_y)
                : defaults.start,
            middle1: connection.bend_x !== null && connection.bend_y !== null
                ? percentPointToPixel(connection.bend_x, connection.bend_y)
                : defaults.middle,
            middle2: connection.mid2_x !== null && connection.mid2_y !== null
                ? percentPointToPixel(connection.mid2_x, connection.mid2_y)
                : defaults.middle2,
            end: connection.end_x !== null && connection.end_y !== null
                ? percentPointToPixel(connection.end_x, connection.end_y)
                : defaults.end,
        };
    }

    function getDefaultConnectionPoints(fromBox, toBox) {
        const dx = toBox.cx - fromBox.cx;
        const dy = toBox.cy - fromBox.cy;
        const isVerticalPriority = Math.abs(dx) <= 110 || Math.abs(dy) > Math.abs(dx);

        if (isVerticalPriority) {
            if (dy >= 0) {
                const start = { x: fromBox.cx, y: fromBox.bottom - edgeOverlap };
                const end = { x: toBox.cx, y: toBox.top + edgeOverlap };
                const middle = { x: fromBox.cx, y: start.y + ((end.y - start.y) / 2) };

                const middle2 = { x: toBox.cx, y: middle.y };

                return { start, middle, middle2, end };
            }

            const start = { x: fromBox.cx, y: fromBox.top + edgeOverlap };
            const end = { x: toBox.cx, y: toBox.bottom - edgeOverlap };
            const middle = { x: fromBox.cx, y: end.y + ((start.y - end.y) / 2) };
            const middle2 = { x: toBox.cx, y: middle.y };

            return { start, middle, middle2, end };
        }

        if (dx >= 0) {
            const start = { x: fromBox.right - edgeOverlap, y: fromBox.cy };
            const end = { x: toBox.left + edgeOverlap, y: toBox.cy };
            const middle = { x: start.x + ((end.x - start.x) / 2), y: start.y };
            const middle2 = { x: middle.x, y: end.y };

            return { start, middle, middle2, end };
        }

        const start = { x: fromBox.left + edgeOverlap, y: fromBox.cy };
        const end = { x: toBox.right - edgeOverlap, y: toBox.cy };
        const middle = { x: end.x + ((start.x - end.x) / 2), y: start.y };
        const middle2 = { x: middle.x, y: end.y };

        return { start, middle, middle2, end };
    }

    function hasManualPoints(connection) {
        return (
            (connection.start_x !== null && connection.start_y !== null) ||
            (connection.bend_x !== null && connection.bend_y !== null) ||
            (connection.mid2_x !== null && connection.mid2_y !== null) ||
            (connection.end_x !== null && connection.end_y !== null)
        );
    }

    function percentPointToPixel(percentX, percentY) {
        const bounds = getBounds();
        return {
            x: (percentX / 100) * bounds.width,
            y: (percentY / 100) * bounds.height,
        };
    }

    function getStatusIcon(status) {
        if (status === 'close') {
            return `
                <svg viewBox="0 0 64 64" aria-hidden="true">
                    <circle cx="32" cy="32" r="29"></circle>
                    <rect x="18" y="18" width="24" height="28" rx="4"></rect>
                    <path d="M24 33l6 6 12-14"></path>
                </svg>
            `;
        }

        if (status === 'proses') {
            return `
                <svg viewBox="0 0 64 64" aria-hidden="true">
                    <circle cx="32" cy="32" r="29"></circle>
                    <path d="M28 13h8l1 5a17 17 0 0 1 5 2l4-3 6 6-3 4a17 17 0 0 1 2 5l5 1v8l-5 1a17 17 0 0 1-2 5l3 4-6 6-4-3a17 17 0 0 1-5 2l-1 5h-8l-1-5a17 17 0 0 1-5-2l-4 3-6-6 3-4a17 17 0 0 1-2-5l-5-1v-8l5-1a17 17 0 0 1 2-5l-3-4 6-6 4 3a17 17 0 0 1 5-2z"></path>
                    <circle cx="32" cy="37" r="7"></circle>
                    <path d="M46 39h3l1 3 3 1v3l-3 1-1 3h-3l-1-3-3-1v-3l3-1z"></path>
                    <circle cx="47.5" cy="44.5" r="2.5"></circle>
                </svg>
            `;
        }

        return `
            <svg viewBox="0 0 64 64" aria-hidden="true">
                <circle cx="32" cy="32" r="29"></circle>
                <path d="M22 22l20 20"></path>
                <path d="M42 22L22 42"></path>
            </svg>
        `;
    }

    function snapPointToNodeEdge(percentX, percentY, nodeBox) {
        const point = percentPointToPixel(clamp(percentX, 0, 100), clamp(percentY, 0, 100));
        const distances = {
            left: Math.abs(point.x - nodeBox.left),
            right: Math.abs(point.x - nodeBox.right),
            top: Math.abs(point.y - nodeBox.top),
            bottom: Math.abs(point.y - nodeBox.bottom),
        };
        const nearestSide = Object.entries(distances).sort((a, b) => a[1] - b[1])[0][0];

        if (nearestSide === 'left') {
            return {
                x: pixelToPercentX(nodeBox.left + edgeOverlap),
                y: pixelToPercentY(snapToGrid(clamp(point.y, nodeBox.top + 4, nodeBox.bottom - 4), gridSnap.y)),
            };
        }

        if (nearestSide === 'right') {
            return {
                x: pixelToPercentX(nodeBox.right - edgeOverlap),
                y: pixelToPercentY(snapToGrid(clamp(point.y, nodeBox.top + 4, nodeBox.bottom - 4), gridSnap.y)),
            };
        }

        if (nearestSide === 'top') {
            return {
                x: pixelToPercentX(snapToGrid(clamp(point.x, nodeBox.left + 4, nodeBox.right - 4), gridSnap.x)),
                y: pixelToPercentY(nodeBox.top + edgeOverlap),
            };
        }

        return {
            x: pixelToPercentX(snapToGrid(clamp(point.x, nodeBox.left + 4, nodeBox.right - 4), gridSnap.x)),
            y: pixelToPercentY(nodeBox.bottom - edgeOverlap),
        };
    }

    function detectHandleEdge(point, nodeBox) {
        const pixelPoint = percentPointToPixel(point.percentX, point.percentY);
        const distances = {
            left: Math.abs(pixelPoint.x - nodeBox.left),
            right: Math.abs(pixelPoint.x - nodeBox.right),
            top: Math.abs(pixelPoint.y - nodeBox.top),
            bottom: Math.abs(pixelPoint.y - nodeBox.bottom),
        };

        return Object.entries(distances).sort((a, b) => a[1] - b[1])[0][0];
    }

    function pixelToPercentX(pixel) {
        const bounds = getBounds();
        return (pixel / bounds.width) * 100;
    }

    function pixelToPercentY(pixel) {
        const bounds = getBounds();
        return (pixel / bounds.height) * 100;
    }

    function clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }

    function snapToGrid(value, size) {
        return Math.round(value / size) * size;
    }
}
