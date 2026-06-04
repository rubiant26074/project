const themeSwitcher = document.querySelector('[data-theme-switcher]');

if (themeSwitcher) {
    const body = document.body;
    const storageKey = 'project-control-theme';
    const supportedThemes = ['industrial-clean', 'dark-steel', 'control-room'];
    const initialTheme = supportedThemes.includes(localStorage.getItem(storageKey))
        ? localStorage.getItem(storageKey)
        : body.dataset.theme;

    setTheme(initialTheme);

    themeSwitcher.querySelectorAll('[data-theme-option]').forEach((button) => {
        button.addEventListener('click', () => {
            setTheme(button.dataset.themeOption);
            localStorage.setItem(storageKey, button.dataset.themeOption);
        });
    });

    function setTheme(theme) {
        body.dataset.theme = theme;

        themeSwitcher.querySelectorAll('[data-theme-option]').forEach((button) => {
            button.classList.toggle('is-active', button.dataset.themeOption === theme);
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
    const bounds = { width: 1200, height: 760 };
    const nodeSize = { width: 170, height: 86 };
    const rawSteps = JSON.parse(flowLayoutEditor.dataset.steps ?? '[]');
    const rawConnections = JSON.parse(flowLayoutEditor.dataset.connections ?? '[]');
    const steps = rawSteps.map((step) => ({
        ...step,
        original_x: Number(step.position_x),
        original_y: Number(step.position_y),
        position_x: Number(step.position_x),
        position_y: Number(step.position_y),
    }));
    const connections = rawConnections.map((connection) => ({
        ...connection,
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

    renderNodes();
    renderLines();

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
                throw new Error('Gagal menyimpan layout');
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
            window.alert('Layout master flow belum berhasil disimpan.');
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
            const node = document.createElement('button');
            node.type = 'button';
            node.className = 'flow-node flow-node-open flow-node-editor';
            node.dataset.stepId = String(step.id);
            node.innerHTML = `
                <span class="flow-node-badge">STEP</span>
                <strong>${step.name}</strong>
                <small>X: <span data-x>${step.position_x.toFixed(1)}</span>% | Y: <span data-y>${step.position_y.toFixed(1)}</span>%</small>
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
            const rawX = ((event.clientX - rect.left) / rect.width) * 100;
            const rawY = ((event.clientY - rect.top) / rect.height) * 100;

            step.position_x = clamp(rawX, 7, 93);
            step.position_y = clamp(rawY, 4, 90);

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
        const node = nodeMap.get(step.id);
        if (!node) {
            return;
        }

        node.style.left = `${step.position_x}%`;
        node.style.top = `${step.position_y}%`;
        node.querySelector('[data-x]').textContent = step.position_x.toFixed(1);
        node.querySelector('[data-y]').textContent = step.position_y.toFixed(1);
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
            const rawX = ((event.clientX - rect.left) / rect.width) * 100;
            const rawY = ((event.clientY - rect.top) / rect.height) * 100;

            if (type === 'start') {
                connection.start_x = clamp(rawX, 2, 98);
                connection.start_y = clamp(rawY, 2, 98);
            } else if (type === 'middle1') {
                connection.bend_x = clamp(rawX, 2, 98);
                connection.bend_y = clamp(rawY, 2, 98);
            } else if (type === 'middle2') {
                connection.mid2_x = clamp(rawX, 2, 98);
                connection.mid2_y = clamp(rawY, 2, 98);
            } else {
                connection.end_x = clamp(rawX, 2, 98);
                connection.end_y = clamp(rawY, 2, 98);
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
                const start = { x: fromBox.cx, y: fromBox.bottom };
                const end = { x: toBox.cx, y: toBox.top };
                const middle = { x: fromBox.cx, y: start.y + ((end.y - start.y) / 2) };

                const middle2 = { x: toBox.cx, y: middle.y };

                return { start, middle, middle2, end };
            }

            const start = { x: fromBox.cx, y: fromBox.top };
            const end = { x: toBox.cx, y: toBox.bottom };
            const middle = { x: fromBox.cx, y: end.y + ((start.y - end.y) / 2) };
            const middle2 = { x: toBox.cx, y: middle.y };

            return { start, middle, middle2, end };
        }

        if (dx >= 0) {
            const start = { x: fromBox.right, y: fromBox.cy };
            const end = { x: toBox.left, y: toBox.cy };
            const middle = { x: start.x + ((end.x - start.x) / 2), y: start.y };
            const middle2 = { x: middle.x, y: end.y };

            return { start, middle, middle2, end };
        }

        const start = { x: fromBox.left, y: fromBox.cy };
        const end = { x: toBox.right, y: toBox.cy };
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
        return {
            x: (percentX / 100) * bounds.width,
            y: (percentY / 100) * bounds.height,
        };
    }

    function pixelToPercentX(pixel) {
        return (pixel / bounds.width) * 100;
    }

    function pixelToPercentY(pixel) {
        return (pixel / bounds.height) * 100;
    }

    function clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }
}
