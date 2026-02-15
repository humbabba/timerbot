/**
 * Wave Runner - Multi-step execution with all forms shown upfront
 */

import { analyzeKimStyle, renderStylePanel, initStyleCheckerGlobals } from './kim-style-checker.js';

document.addEventListener('DOMContentLoaded', function() {
    initStyleCheckerGlobals();
    // Check if we're on the wave execution page
    if (typeof waveNodes === 'undefined' || typeof runStepUrl === 'undefined') {
        return;
    }

    const allFormsContainer = document.getElementById('all-forms-container');
    const runWaveContainer = document.getElementById('run-wave-container');
    const errorContainer = document.getElementById('error-container');
    const resultsContainer = document.getElementById('results-container');
    const finalResults = document.getElementById('final-results');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');

    let executionState = {};
    let finalResultsData = [];
    let isRunning = false;

    function formatImageMeta(result) {
        let label = 'AI Generated Image';
        const parts = [];
        if (result.image_width && result.image_height) {
            parts.push(result.image_width + ' x ' + result.image_height + ' px');
        }
        if (result.image_filesize) {
            const kb = result.image_filesize / 1024;
            parts.push(kb >= 1024 ? (kb / 1024).toFixed(1) + ' MB' : Math.round(kb) + ' KB');
        }
        if (parts.length) {
            label += ' \u2014 ' + parts.join(', ');
        }
        return label;
    }

    // Initialize - render all forms
    renderAllForms();

    // Determine which inputs are mapped for a given node
    function getMappedInputs(node) {
        const mapped = {};
        if (!node.mappings) return mapped;

        for (const [targetField, mapping] of Object.entries(node.mappings)) {
            if (mapping.type) {
                mapped[targetField] = mapping;
            }
        }
        return mapped;
    }

    // Check if a node needs any manual input
    function nodeNeedsManualInput(node) {
        if (!node.inputs || node.inputs.length === 0) return false;

        const mappedInputs = getMappedInputs(node);

        for (const input of node.inputs) {
            if (!mappedInputs[input.name]) {
                return true; // This input is not mapped, needs manual entry
            }
        }
        return false;
    }

    // Render all node forms
    function renderAllForms() {
        let html = '';

        waveNodes.forEach((node, index) => {
            const needsInput = nodeNeedsManualInput(node);
            const mappedInputs = getMappedInputs(node);
            const hasInputs = node.inputs && node.inputs.length > 0;
            const isCollapsible = !needsInput; // Collapse if no manual input needed

            html += `
                <div class="node-form-block bg-cortex-panel-light rounded-xl ${isCollapsible ? 'p-4' : 'p-6'}" data-position="${node.position}" id="node-form-${node.position}" data-collapsible="${isCollapsible ? '1' : '0'}">
                    <div class="flex items-center gap-3 ${isCollapsible ? '' : 'mb-4'}">
                        <span class="w-8 h-8 rounded-full bg-cortex-orange text-cortex-black flex items-center justify-center text-sm font-bold">${node.position + 1}</span>
                        <h2 class="text-cortex-cyan text-xl font-semibold">${escapeHtml(node.name)}</h2>
                        <span id="node-status-${node.position}" class="text-sm text-text-muted"></span>
                        ${isCollapsible ? `
                            <span class="text-cortex-green text-xs ml-2">(auto)</span>
                            <button type="button" onclick="toggleNodeExpand(${node.position})" class="ml-auto px-3 py-1 rounded-full bg-cortex-panel text-text-muted hover:text-cortex-cyan transition-all text-xs uppercase tracking-wider flex items-center gap-1" style="font-family: var(--font-display);" id="expand-btn-${node.position}">
                                <span class="expand-icon">▶</span> Show
                            </button>
                        ` : ''}
                    </div>
            `;

            // Collapsible content wrapper
            if (isCollapsible) {
                html += `<div id="node-content-${node.position}" class="hidden mt-4">`;
            }

            if (!hasInputs) {
                html += `<p class="text-text-muted">This node has no inputs - it will run automatically.</p>`;
            } else {
                html += `<div class="space-y-4">`;

                node.inputs.forEach(input => {
                    const isMapped = !!mappedInputs[input.name];
                    const mapping = mappedInputs[input.name];
                    const isRequired = input.required ? 'required' : '';
                    const requiredStar = input.required ? '<span class="text-cortex-red">*</span>' : '';

                    // Check if this field should have the URL fetch button after it
                    const isUrlSourceField = node.url_source_field && node.url_target_field && input.name === node.url_source_field;
                    const showUrlButton = isUrlSourceField && !isMapped;

                    let mappingNote = '';
                    if (isMapped) {
                        if (mapping.type === 'output') {
                            const sourceNode = waveNodes.find(n => n.position === parseInt(mapping.source_position));
                            mappingNote = `<span class="text-cortex-green text-xs ml-2">(auto-filled from Step ${parseInt(mapping.source_position) + 1} output)</span>`;
                        } else if (mapping.type === 'input') {
                            mappingNote = `<span class="text-cortex-green text-xs ml-2">(auto-filled from Step ${parseInt(mapping.source_position) + 1} input)</span>`;
                        }
                    }

                    html += `
                        <div class="input-field" data-input-name="${input.name}" data-mapped="${isMapped ? '1' : '0'}">
                            <label class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">
                                ${input.label} ${requiredStar} ${mappingNote}
                            </label>
                    `;

                    const disabledAttr = isMapped ? 'disabled' : '';
                    const disabledClass = isMapped ? 'opacity-50 cursor-not-allowed' : '';

                    const defaultValue = input.default || '';

                    switch (input.type) {
                        case 'textarea':
                            html += `
                                <textarea
                                    id="input_${node.position}_${input.name}"
                                    name="node_${node.position}_${input.name}"
                                    rows="4"
                                    ${isRequired}
                                    ${disabledAttr}
                                    placeholder="${isMapped ? 'Will be filled automatically...' : ''}"
                                    class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan ${disabledClass}"
                                >${isMapped ? '' : escapeHtml(defaultValue)}</textarea>
                            `;
                            break;

                        case 'select':
                            const selectOptions = (input.options || '').split(',').map(o => o.trim()).filter(o => o);
                            html += `
                                <select
                                    id="input_${node.position}_${input.name}"
                                    name="node_${node.position}_${input.name}"
                                    ${isRequired}
                                    ${disabledAttr}
                                    class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan ${disabledClass}"
                                >
                                    <option value="">${isMapped ? 'Will be filled automatically...' : 'Select an option'}</option>
                                    ${selectOptions.map(opt => `<option value="${escapeHtml(opt)}" ${!isMapped && defaultValue === opt ? 'selected' : ''}>${escapeHtml(opt)}</option>`).join('')}
                                </select>
                            `;
                            break;

                        case 'checkbox':
                            const cbOptions = (input.options || '').split(',').map(o => o.trim()).filter(o => o);
                            const cbDefaults = (defaultValue || '').split(',').map(o => o.trim());
                            html += `<div class="space-y-2">`;
                            cbOptions.forEach(opt => {
                                const isChecked = !isMapped && cbDefaults.includes(opt) ? 'checked' : '';
                                html += `
                                    <label class="flex items-center gap-3 p-3 bg-cortex-panel rounded-lg cursor-pointer hover:bg-gray transition-colors ${disabledClass}">
                                        <input type="checkbox" name="node_${node.position}_${input.name}[]" value="${escapeHtml(opt)}" ${isChecked} ${disabledAttr} class="w-5 h-5 rounded border-gray bg-cortex-dark text-cortex-orange">
                                        <span>${escapeHtml(opt)}</span>
                                    </label>`;
                            });
                            html += `</div>`;
                            break;

                        default:
                            html += `
                                <input
                                    type="${input.type}"
                                    id="input_${node.position}_${input.name}"
                                    name="node_${node.position}_${input.name}"
                                    ${isRequired}
                                    ${disabledAttr}
                                    placeholder="${isMapped ? 'Will be filled automatically...' : ''}"
                                    value="${isMapped ? '' : escapeHtml(defaultValue)}"
                                    class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan ${disabledClass}"
                                >
                            `;
                    }

                    // Add URL fetch button right after the source field
                    if (showUrlButton) {
                        html += `
                            <button type="button" onclick="fetchUrlForNode(${node.position}, ${node.id}, '${node.url_source_field}', '${node.url_target_field}')" class="mt-2 btn btn-secondary">
                                Pull Data from URL
                            </button>
                        `;
                    }

                    html += `</div>`;
                });

                html += `</div>`;
            }

            // Result area (hidden initially)
            html += `
                    <div id="node-result-${node.position}" class="hidden mt-4">
                        <div class="border-t border-gray pt-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-cortex-green text-sm font-semibold uppercase tracking-wider">Result</span>
                                <button onclick="copyNodeResult(${node.position})" class="px-3 py-1 rounded-full bg-cortex-panel text-cortex-cyan hover:bg-cortex-cyan hover:text-cortex-black transition-all text-xs uppercase tracking-wider" style="font-family: var(--font-display);">
                                    Copy
                                </button>
                            </div>
                            <iframe id="result-iframe-${node.position}" class="result-iframe w-full bg-white rounded-lg" style="min-height: 150px; border: none;"></iframe>
                            <div id="style-check-inline-${node.position}"></div>
                        </div>
                    </div>
            `;

            // Close collapsible content wrapper if needed
            if (isCollapsible) {
                html += `</div>`; // Close node-content-{position}
            }

            html += `</div>`; // Close node-form-block
        });

        allFormsContainer.innerHTML = html;
    }

    // Run the entire wave
    window.runWave = async function() {
        if (isRunning) return;
        isRunning = true;

        // Scroll to top so progress bar is visible
        const mainEl = document.querySelector('main');
        if (mainEl) {
            mainEl.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        const runButton = document.getElementById('run-wave-button');
        runButton.disabled = true;
        runButton.textContent = 'Running...';

        errorContainer.classList.add('hidden');
        executionState = {};
        finalResultsData = [];

        const totalSteps = waveNodes.length;

        for (let i = 0; i < waveNodes.length; i++) {
            const node = waveNodes[i];
            const position = node.position;

            // Update progress
            progressBar.style.width = `${((i) / totalSteps) * 100}%`;
            progressText.textContent = `Processing Step ${i + 1} of ${totalSteps}...`;

            // Update step indicator
            const indicator = document.getElementById(`step-indicator-${position}`);
            if (indicator) {
                indicator.className = 'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold bg-cortex-orange text-cortex-black animate-pulse';
            }

            // Update node status
            const statusEl = document.getElementById(`node-status-${position}`);
            if (statusEl) {
                statusEl.textContent = 'Processing...';
                statusEl.className = 'text-sm text-cortex-orange';
            }

            // Collect inputs for this node
            const inputs = {};
            const mappedInputs = getMappedInputs(node);

            if (node.inputs) {
                for (const input of node.inputs) {
                    if (mappedInputs[input.name]) {
                        // Get value from mapping
                        const mapping = mappedInputs[input.name];
                        const sourcePosition = parseInt(mapping.source_position);
                        const sourceResult = finalResultsData.find(r => r.position === sourcePosition);

                        if (sourceResult) {
                            if (mapping.type === 'output') {
                                const temp = document.createElement('div');
                                temp.innerHTML = sourceResult.output_html;
                                inputs[input.name] = temp.innerText || temp.textContent;
                            } else if (mapping.type === 'input' && mapping.source_field) {
                                inputs[input.name] = sourceResult.inputs?.[mapping.source_field] || '';
                            }
                        }
                    } else {
                        // Get value from form
                        const field = document.getElementById(`input_${position}_${input.name}`);
                        if (field) {
                            inputs[input.name] = field.value;
                        }
                    }
                }
            }

            // Submit this step
            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('position', position);
            formData.append('execution_state', JSON.stringify(executionState));

            for (const [key, value] of Object.entries(inputs)) {
                formData.append(`inputs[${key}]`, value);
            }

            try {
                const response = await fetch(runStepUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.debug_prompt) {
                    console.log(`[Step ${i + 1}] API Prompt:`, data.debug_prompt);
                }

                if (!response.ok || !data.success) {
                    showError(`Step ${i + 1} failed: ${data.error || data.message || 'Unknown error'}`);
                    if (statusEl) {
                        statusEl.textContent = 'Error';
                        statusEl.className = 'text-sm text-cortex-red';
                    }
                    if (indicator) {
                        indicator.className = 'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold bg-cortex-red text-white';
                    }
                    runButton.disabled = false;
                    runButton.textContent = 'Run Wave';
                    isRunning = false;
                    return;
                }

                executionState = data.execution_state;

                // Store result
                const result = {
                    position: position,
                    node_name: node.name,
                    node_id: node.id,
                    inputs: inputs,
                    output_html: executionState[position]?.output_html || '',
                    is_image: executionState[position]?.is_image || false,
                    image_url: executionState[position]?.image_url || null,
                    image_width: executionState[position]?.image_width || null,
                    image_height: executionState[position]?.image_height || null,
                    image_filesize: executionState[position]?.image_filesize || null
                };
                finalResultsData.push(result);

                // Show result inline
                showNodeResult(position, result.output_html);

                // Run Kim style check on inline result
                runStyleCheckOnIframe(`result-iframe-${position}`, `style-check-inline-${position}`, node);

                // Update status
                if (statusEl) {
                    statusEl.textContent = 'Complete';
                    statusEl.className = 'text-sm text-cortex-green';
                }
                if (indicator) {
                    indicator.className = 'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold bg-cortex-green text-cortex-black';
                }

            } catch (error) {
                showError(`Step ${i + 1} failed: Network error - ${error.message}`);
                if (statusEl) {
                    statusEl.textContent = 'Error';
                    statusEl.className = 'text-sm text-cortex-red';
                }
                runButton.disabled = false;
                runButton.textContent = 'Run Wave';
                isRunning = false;
                return;
            }
        }

        // All done
        progressBar.style.width = '100%';
        progressText.textContent = 'Complete';

        // Show final results section
        showFinalResults();

        runButton.disabled = false;
        runButton.textContent = 'Run Wave';
        isRunning = false;
    };

    // Style checker helper - runs Kim style check on an iframe's text content
    function runStyleCheckOnIframe(iframeId, containerId, nodeConfig) {
        if (!nodeConfig || !nodeConfig.style_check) return;
        const iframe = document.getElementById(iframeId);
        if (!iframe) return;

        const runCheck = () => {
            const doc = iframe.contentDocument || iframe.contentWindow.document;
            const text = doc.body?.innerText || doc.body?.textContent || '';
            if (text.trim()) {
                const analysis = analyzeKimStyle(text, nodeConfig.system_text || '');
                renderStylePanel(containerId, analysis);
            }
        };

        // If iframe is already loaded, run immediately; otherwise wait
        if (iframe.contentDocument && iframe.contentDocument.body && iframe.contentDocument.body.innerText) {
            runCheck();
        } else {
            const existingOnload = iframe.onload;
            iframe.onload = function() {
                if (existingOnload) existingOnload.call(this);
                runCheck();
            };
        }
    }

    function showNodeResult(position, outputHtml) {
        const resultContainer = document.getElementById(`node-result-${position}`);
        const iframe = document.getElementById(`result-iframe-${position}`);

        if (resultContainer && iframe) {
            iframe.srcdoc = wrapHtmlContent(outputHtml);
            iframe.onload = function() {
                const doc = iframe.contentDocument || iframe.contentWindow.document;
                const height = doc.body.scrollHeight;
                iframe.style.height = Math.max(150, height + 20) + 'px';
            };
            resultContainer.classList.remove('hidden');
        }
    }

    function showFinalResults() {
        // Hide the run button area
        runWaveContainer.classList.add('hidden');

        // Show results container with copy all and rerun options
        let html = '';
        finalResultsData.forEach(result => {
            const nodeConfig = waveNodes.find(n => n.position === result.position);
            const includeInOutput = nodeConfig?.include_in_output ?? true;
            const hasInputs = nodeConfig?.inputs && nodeConfig.inputs.length > 0;

            const downloadBtn = result.is_image && result.image_url
                ? `<button onclick="downloadImage(${result.position})" class="px-3 py-1 rounded-full bg-cortex-panel text-cortex-green hover:bg-cortex-green hover:text-cortex-black transition-all text-xs uppercase tracking-wider" style="font-family: var(--font-display);">Download</button>`
                : '';

            html += `
                <div class="result-block" data-position="${result.position}" data-include-in-output="${includeInOutput ? '1' : '0'}" data-is-image="${result.is_image ? '1' : '0'}" data-image-url="${result.image_url || ''}">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-cortex-cyan font-semibold flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-cortex-orange text-cortex-black flex items-center justify-center text-xs font-bold">${result.position + 1}</span>
                            ${escapeHtml(result.node_name)}
                            <span id="word-count-${result.position}" class="text-text-muted text-sm font-normal"></span>
                        </h3>
                        <div class="flex gap-2">
                            ${hasInputs ? `<button onclick="showRerunForm(${result.position})" class="px-3 py-1 rounded-full bg-cortex-panel text-cortex-peach hover:bg-cortex-peach hover:text-cortex-black transition-all text-xs uppercase tracking-wider" style="font-family: var(--font-display);">Rerun</button>` : ''}
                            ${downloadBtn}
                            <button onclick="copyResult(${result.position})" class="px-3 py-1 rounded-full bg-cortex-panel text-cortex-cyan hover:bg-cortex-cyan hover:text-cortex-black transition-all text-xs uppercase tracking-wider" style="font-family: var(--font-display);">
                                Copy
                            </button>
                        </div>
                    </div>
                    <div id="rerun-form-${result.position}" class="hidden mb-4 p-4 bg-cortex-panel rounded-lg border border-cortex-peach/50"></div>
                    <iframe id="final-result-iframe-${result.position}" class="result-iframe w-full bg-white rounded-lg" style="min-height: 200px; border: none;" srcdoc="${escapeAttr(wrapHtmlContent(result.output_html))}"></iframe>
                    <div id="style-check-final-${result.position}"></div>
                </div>
            `;
        });

        finalResults.innerHTML = html;

        // Adjust iframe heights and calculate word counts
        finalResultsData.forEach(result => {
            const iframe = document.getElementById(`final-result-iframe-${result.position}`);
            if (iframe) {
                const updateIframe = () => {
                    const doc = iframe.contentDocument || iframe.contentWindow.document;
                    const height = doc.body.scrollHeight;
                    iframe.style.height = Math.max(200, height + 40) + 'px';

                    const wordCountEl = document.getElementById(`word-count-${result.position}`);
                    if (wordCountEl) {
                        if (result.is_image) {
                            wordCountEl.textContent = formatImageMeta(result);
                        } else {
                            const text = doc.body.innerText || doc.body.textContent || '';
                            const wordCount = text.trim().split(/\s+/).filter(word => word.length > 0).length;
                            wordCountEl.textContent = wordCount.toLocaleString() + ' words';
                        }
                    }
                };
                iframe.addEventListener('load', updateIframe);

                // Run Kim style check on final result
                const nodeConfig = waveNodes.find(n => n.position === result.position);
                iframe.addEventListener('load', function() {
                    runStyleCheckOnIframe(`final-result-iframe-${result.position}`, `style-check-final-${result.position}`, nodeConfig);
                });
            }
        });

        // Hide the forms, show results
        allFormsContainer.classList.add('hidden');
        resultsContainer.classList.remove('hidden');

        // Hide "Copy All" button if there's only one output
        const outputCount = finalResultsData.filter(result => {
            const nodeConfig = waveNodes.find(n => n.position === result.position);
            return nodeConfig?.include_in_output ?? true;
        }).length;
        const copyAllButton = document.getElementById('copy-all-button');
        if (copyAllButton && outputCount <= 1) {
            copyAllButton.classList.add('hidden');
        }
    }

    function showError(message) {
        errorContainer.textContent = message;
        errorContainer.classList.remove('hidden');
    }

    function wrapHtmlContent(html) {
        return '<!DOCTYPE html><html><head><style>body { font-family: sans-serif; font-size: 12pt; padding: 16px; }</style></head><body>' + html + '</body></html>';
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function escapeAttr(text) {
        if (text === null || text === undefined) return '';
        return text.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // Convert images to inline data URIs for clipboard compatibility
    function convertImagesToDataUri(iframeDoc) {
        const images = iframeDoc.body.querySelectorAll('img');
        const originals = [];

        images.forEach(img => {
            originals.push({ img, src: img.src });
            try {
                const canvas = document.createElement('canvas');
                canvas.width = img.naturalWidth;
                canvas.height = img.naturalHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0);
                img.src = canvas.toDataURL('image/jpeg', 0.8);
            } catch (e) {
                console.warn('Could not convert image to data URI:', e);
            }
        });

        return originals;
    }

    function restoreImageSrcs(originals) {
        originals.forEach(({ img, src }) => { img.src = src; });
    }

    // Global functions
    window.toggleNodeExpand = function(position) {
        const content = document.getElementById(`node-content-${position}`);
        const btn = document.getElementById(`expand-btn-${position}`);

        if (!content || !btn) return;

        const isHidden = content.classList.contains('hidden');

        if (isHidden) {
            content.classList.remove('hidden');
            btn.innerHTML = '<span class="expand-icon">▼</span> Hide';
        } else {
            content.classList.add('hidden');
            btn.innerHTML = '<span class="expand-icon">▶</span> Show';
        }
    };

    window.restartWave = function() {
        executionState = {};
        finalResultsData = [];

        // Reset progress
        progressBar.style.width = '0%';
        progressText.textContent = 'Ready';

        // Reset step indicators
        waveNodes.forEach((node, i) => {
            const indicator = document.getElementById(`step-indicator-${node.position}`);
            if (indicator) {
                indicator.className = 'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold bg-cortex-panel text-text-muted';
            }
            const statusEl = document.getElementById(`node-status-${node.position}`);
            if (statusEl) {
                statusEl.textContent = '';
            }
            const resultContainer = document.getElementById(`node-result-${node.position}`);
            if (resultContainer) {
                resultContainer.classList.add('hidden');
            }
        });

        // Show forms, hide results
        allFormsContainer.classList.remove('hidden');
        runWaveContainer.classList.remove('hidden');
        resultsContainer.classList.add('hidden');
        errorContainer.classList.add('hidden');

        // Re-render forms to clear values
        renderAllForms();
    };

    window.fetchUrlForNode = async function(position, nodeId, sourceFieldName, targetFieldName) {
        const sourceField = document.getElementById(`input_${position}_${sourceFieldName}`);
        const targetField = document.getElementById(`input_${position}_${targetFieldName}`);
        const nodeBlock = document.getElementById(`node-form-${position}`);

        if (!sourceField || !targetField) {
            showError('Source or target field not found');
            return;
        }

        const url = sourceField.value.trim();
        if (!url) {
            showError('Please enter a URL in the source field');
            return;
        }

        // Find the button and show loading
        const btn = nodeBlock.querySelector('button[onclick^="fetchUrlForNode"]');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Fetching...';
        errorContainer.classList.add('hidden');

        try {
            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('url', url);

            const response = await fetch(`/nodes/${nodeId}/fetch-url`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                targetField.value = data.content || 'The request was successful, but no content was retrieved.';
            } else {
                showError(data.error || 'Failed to fetch URL');
            }
        } catch (error) {
            showError('Network error: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    };

    window.copyNodeResult = async function(position) {
        const iframe = document.getElementById(`result-iframe-${position}`);
        if (!iframe) return;

        const doc = iframe.contentDocument || iframe.contentWindow.document;
        const originals = convertImagesToDataUri(doc);

        const range = document.createRange();
        range.selectNodeContents(doc.body);
        const selection = iframe.contentWindow.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
        iframe.contentDocument.execCommand('copy');
        selection.removeAllRanges();

        restoreImageSrcs(originals);
    };

    // Download image function via backend proxy - looks up current URL from finalResultsData
    window.downloadImage = function(position) {
        const result = finalResultsData.find(r => r.position === position);
        if (!result || !result.image_url) return;
        const slug = result.node_name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        const timestamp = new Date().toISOString().slice(0, 10);
        const filename = slug + '-' + timestamp + '.jpg';
        const downloadUrl = '/download-image?url=' + encodeURIComponent(result.image_url) + '&filename=' + encodeURIComponent(filename);
        window.location.href = downloadUrl;
    };

    window.copyResult = async function(position) {
        const iframe = document.getElementById(`final-result-iframe-${position}`);
        if (!iframe) return;

        const doc = iframe.contentDocument || iframe.contentWindow.document;
        const originals = convertImagesToDataUri(doc);

        const range = document.createRange();
        range.selectNodeContents(doc.body);
        const selection = iframe.contentWindow.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
        iframe.contentDocument.execCommand('copy');
        selection.removeAllRanges();

        restoreImageSrcs(originals);

        // Feedback
        const btn = iframe.closest('.result-block').querySelector('button[onclick^="copyResult"]');
        if (btn) {
            const originalText = btn.textContent;
            btn.textContent = 'Copied!';
            setTimeout(() => btn.textContent = originalText, 2000);
        }
    };

    window.copyAllResults = function() {
        const resultBlocks = document.querySelectorAll('.result-block');
        const htmlParts = [];
        let firstIframe = null;

        // Convert images to data URIs in each iframe before collecting HTML
        const allOriginals = [];
        resultBlocks.forEach((block) => {
            if (block.dataset.includeInOutput === '0') {
                return;
            }

            const iframe = block.querySelector('.result-iframe');
            if (!firstIframe) firstIframe = iframe;
            const doc = iframe.contentDocument || iframe.contentWindow.document;
            const originals = convertImagesToDataUri(doc);
            allOriginals.push({ doc, originals });
            htmlParts.push(doc.body.innerHTML);
        });

        if (!firstIframe || htmlParts.length === 0) {
            return;
        }

        const trimmedParts = htmlParts.map(html => html.replace(/(<br\s*\/?>|\s)+$/gi, ''));
        const allHtml = trimmedParts.join('<br><br>');

        // Use the first existing iframe - store original content, replace, copy, restore
        const iframeDoc = firstIframe.contentDocument || firstIframe.contentWindow.document;
        const originalContent = iframeDoc.body.innerHTML;

        // Temporarily replace with combined content (already has data URIs)
        iframeDoc.body.innerHTML = allHtml;

        // Select and copy (same as working single copy)
        const range = document.createRange();
        range.selectNodeContents(iframeDoc.body);
        const selection = firstIframe.contentWindow.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);

        let success = false;
        try {
            success = firstIframe.contentDocument.execCommand('copy');
        } catch (err) {
            console.error('Copy failed:', err);
        }

        selection.removeAllRanges();

        // Restore original content
        iframeDoc.body.innerHTML = originalContent;

        // Restore original image sources in all iframes
        allOriginals.forEach(({ doc, originals }) => restoreImageSrcs(originals));

        const btn = document.getElementById('copy-all-button');
        const originalText = btn.textContent;
        btn.textContent = success ? 'Copied!' : 'Failed';
        setTimeout(() => btn.textContent = originalText, 2000);
    };

    window.showRerunForm = function(position) {
        const formContainer = document.getElementById(`rerun-form-${position}`);
        const nodeConfig = waveNodes.find(n => n.position === position);
        const resultData = finalResultsData.find(r => r.position === position);

        if (!nodeConfig || !formContainer) return;

        let html = '<form onsubmit="rerunStep(event, ' + position + ')" class="space-y-4">';

        nodeConfig.inputs.forEach(input => {
            const savedValue = resultData?.inputs?.[input.name] || '';
            const isRequired = input.required ? 'required' : '';
            const requiredStar = input.required ? '<span class="text-cortex-red">*</span>' : '';

            html += `
                <div>
                    <label class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">
                        ${input.label} ${requiredStar}
                    </label>
            `;

            switch (input.type) {
                case 'textarea':
                    html += `<textarea name="rerun_${position}_${input.name}" rows="4" ${isRequired} class="w-full p-3 bg-cortex-dark border border-gray rounded-lg text-text focus:border-cortex-cyan">${escapeHtml(savedValue)}</textarea>`;
                    break;

                case 'select':
                    const selectOptions = (input.options || '').split(',').map(o => o.trim()).filter(o => o);
                    html += `<select name="rerun_${position}_${input.name}" ${isRequired} class="w-full p-3 bg-cortex-dark border border-gray rounded-lg text-text focus:border-cortex-cyan">
                        <option value="">Select an option</option>
                        ${selectOptions.map(opt => `<option value="${escapeHtml(opt)}" ${savedValue === opt ? 'selected' : ''}>${escapeHtml(opt)}</option>`).join('')}
                    </select>`;
                    break;

                case 'checkbox':
                    const rerunCbOptions = (input.options || '').split(',').map(o => o.trim()).filter(o => o);
                    const savedChecked = Array.isArray(savedValue) ? savedValue : (savedValue || '').split(',').map(o => o.trim());
                    html += `<div class="space-y-2">`;
                    rerunCbOptions.forEach(opt => {
                        const isChecked = savedChecked.includes(opt) ? 'checked' : '';
                        html += `
                            <label class="flex items-center gap-3 p-3 bg-cortex-dark rounded-lg cursor-pointer hover:bg-gray transition-colors">
                                <input type="checkbox" name="rerun_${position}_${input.name}[]" value="${escapeHtml(opt)}" ${isChecked} class="w-5 h-5 rounded border-gray bg-cortex-dark text-cortex-orange">
                                <span>${escapeHtml(opt)}</span>
                            </label>`;
                    });
                    html += `</div>`;
                    break;

                default:
                    html += `<input type="${input.type}" name="rerun_${position}_${input.name}" value="${escapeHtml(savedValue)}" ${isRequired} class="w-full p-3 bg-cortex-dark border border-gray rounded-lg text-text focus:border-cortex-cyan">`;
            }

            html += '</div>';
        });

        html += `
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Run</button>
                <button type="button" onclick="hideRerunForm(${position})" class="btn bg-cortex-panel text-text hover:bg-gray">Cancel</button>
            </div>
        </form>`;

        formContainer.innerHTML = html;
        formContainer.classList.remove('hidden');
    };

    window.hideRerunForm = function(position) {
        const formContainer = document.getElementById(`rerun-form-${position}`);
        if (formContainer) {
            formContainer.classList.add('hidden');
            formContainer.innerHTML = '';
        }
    };

    window.rerunStep = async function(event, position) {
        event.preventDefault();

        const nodeConfig = waveNodes.find(n => n.position === position);
        if (!nodeConfig) return;

        const formContainer = document.getElementById(`rerun-form-${position}`);
        const iframe = document.getElementById(`final-result-iframe-${position}`);

        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('position', position);
        formData.append('execution_state', JSON.stringify(executionState));

        const collectedInputs = {};
        nodeConfig.inputs.forEach(input => {
            const field = formContainer.querySelector(`[name="rerun_${position}_${input.name}"]`);
            if (field) {
                formData.append(`inputs[${input.name}]`, field.value);
                collectedInputs[input.name] = field.value;
            }
        });

        formContainer.innerHTML = '<div class="flex items-center gap-3"><div class="w-6 h-6 border-2 border-cortex-panel border-t-cortex-orange rounded-full animate-spin"></div><span class="text-cortex-orange">Processing...</span></div>';

        try {
            const response = await fetch(rerunStepUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.debug_prompt) {
                console.log(`[Rerun Step ${position + 1}] API Prompt:`, data.debug_prompt);
            }

            if (!response.ok || !data.success) {
                formContainer.innerHTML = `<div class="text-cortex-red">${escapeHtml(data.error || data.message || 'An error occurred')}</div><button type="button" onclick="showRerunForm(${position})" class="mt-2 btn btn-secondary">Try Again</button>`;
                return;
            }

            const newResult = data.result;

            // Update executionState with new output
            executionState[position] = {
                node_id: newResult.node_id,
                node_name: newResult.node_name,
                inputs: collectedInputs,
                output: newResult.output,
                output_html: newResult.output_html,
                is_image: newResult.is_image || false,
                image_url: newResult.image_url || null
            };

            const resultIndex = finalResultsData.findIndex(r => r.position === position);
            if (resultIndex !== -1) {
                finalResultsData[resultIndex] = newResult;
            }

            iframe.srcdoc = wrapHtmlContent(newResult.output_html);
            iframe.onload = function() {
                const doc = iframe.contentDocument || iframe.contentWindow.document;
                const height = doc.body.scrollHeight;
                iframe.style.height = Math.max(200, height + 40) + 'px';

                // Update word count or image label
                const wordCountEl = document.getElementById(`word-count-${position}`);
                if (wordCountEl) {
                    if (newResult.is_image) {
                        wordCountEl.textContent = formatImageMeta(newResult);
                    } else {
                        const text = doc.body.innerText || doc.body.textContent || '';
                        const wordCount = text.trim().split(/\s+/).filter(word => word.length > 0).length;
                        wordCountEl.textContent = wordCount.toLocaleString() + ' words';
                    }
                }

                // Re-run Kim style check after rerun
                runStyleCheckOnIframe(`final-result-iframe-${position}`, `style-check-final-${position}`, nodeConfig);
            };

            hideRerunForm(position);

            // Cascade to subsequent nodes
            await cascadeRerun(position);

        } catch (error) {
            formContainer.innerHTML = `<div class="text-cortex-red">Network error: ${escapeHtml(error.message)}</div><button type="button" onclick="showRerunForm(${position})" class="mt-2 btn btn-secondary">Try Again</button>`;
        }
    };

    async function cascadeRerun(startPosition) {
        let currentPos = startPosition + 1;

        while (currentPos < waveNodes.length) {
            const nextNode = waveNodes.find(n => n.position === currentPos);
            if (!nextNode) break;

            const preFilled = applyMappingsFromResults(nextNode.mappings);

            if (!allRequiredInputsFilled(nextNode, preFilled)) {
                break;
            }

            const success = await autoRerunNode(currentPos, preFilled);
            if (!success) break;

            currentPos++;
        }
    }

    function applyMappingsFromResults(mappings) {
        const preFilled = {};
        for (const [targetField, mapping] of Object.entries(mappings || {})) {
            if (!mapping.type) continue;
            const sourcePosition = parseInt(mapping.source_position);
            const sourceResult = finalResultsData.find(r => r.position === sourcePosition);
            if (!sourceResult) continue;

            if (mapping.type === 'output') {
                const temp = document.createElement('div');
                temp.innerHTML = sourceResult.output_html;
                preFilled[targetField] = temp.innerText || temp.textContent;
            } else if (mapping.type === 'input' && mapping.source_field) {
                preFilled[targetField] = sourceResult.inputs?.[mapping.source_field] || '';
            }
        }
        return preFilled;
    }

    function allRequiredInputsFilled(nodeConfig, preFilled) {
        if (!nodeConfig.inputs) return true;
        for (const input of nodeConfig.inputs) {
            if (input.required) {
                const value = preFilled[input.name];
                if (!value || (typeof value === 'string' && value.trim() === '')) {
                    return false;
                }
            }
        }
        return true;
    }

    async function autoRerunNode(position, preFilled) {
        const nodeConfig = waveNodes.find(n => n.position === position);
        if (!nodeConfig) return false;

        const resultBlock = document.querySelector(`.result-block[data-position="${position}"]`);
        const iframe = document.getElementById(`final-result-iframe-${position}`);
        if (!resultBlock || !iframe) return false;

        const header = resultBlock.querySelector('h3');
        header.innerHTML += ' <span class="rerun-indicator text-cortex-orange text-sm">(rerunning...)</span>';

        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('position', position);
        formData.append('execution_state', JSON.stringify(executionState));

        nodeConfig.inputs.forEach(input => {
            formData.append(`inputs[${input.name}]`, preFilled[input.name] || '');
        });

        try {
            const response = await fetch(rerunStepUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.debug_prompt) {
                console.log(`[Auto-rerun Step ${position + 1}] API Prompt:`, data.debug_prompt);
            }

            const indicator = header.querySelector('.rerun-indicator');
            if (indicator) indicator.remove();

            if (!response.ok || !data.success) {
                return false;
            }

            // Update executionState with new output
            executionState[position] = {
                node_id: data.result.node_id,
                node_name: data.result.node_name,
                inputs: preFilled,
                output: data.result.output,
                output_html: data.result.output_html
            };

            const resultIndex = finalResultsData.findIndex(r => r.position === position);
            if (resultIndex !== -1) {
                finalResultsData[resultIndex] = data.result;
            }

            iframe.srcdoc = wrapHtmlContent(data.result.output_html);
            iframe.onload = function() {
                const doc = iframe.contentDocument || iframe.contentWindow.document;
                const height = doc.body.scrollHeight;
                iframe.style.height = Math.max(200, height + 40) + 'px';

                // Update word count or image label
                const wordCountEl = document.getElementById(`word-count-${position}`);
                if (wordCountEl) {
                    if (data.result.is_image) {
                        wordCountEl.textContent = formatImageMeta(data.result);
                    } else {
                        const text = doc.body.innerText || doc.body.textContent || '';
                        const wordCount = text.trim().split(/\s+/).filter(word => word.length > 0).length;
                        wordCountEl.textContent = wordCount.toLocaleString() + ' words';
                    }
                }

                // Re-run Kim style check after cascade rerun
                runStyleCheckOnIframe(`final-result-iframe-${position}`, `style-check-final-${position}`, nodeConfig);
            };

            return true;
        } catch (error) {
            const indicator = header.querySelector('.rerun-indicator');
            if (indicator) indicator.remove();
            return false;
        }
    }
});
