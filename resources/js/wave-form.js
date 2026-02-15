/**
 * Wave form handling - create and edit forms for waves
 * Handles node selection, reordering, and field mapping configuration
 */

document.addEventListener('DOMContentLoaded', function() {
    // Bail if we're not on a wave form page
    if (typeof allNodes === 'undefined') {
        return;
    }

    // Initialize mappings for existing nodes (edit page)
    document.querySelectorAll('.node-row').forEach((row, index) => {
        if (index > 0) {
            const select = row.querySelector('select[name$="[id]"]');
            if (select && select.value) {
                updateMappingOptions(select);
            }
        }
    });
});

function getNodeById(id) {
    return allNodes.find(n => n.id == id);
}

function updateNodePositions() {
    document.querySelectorAll('.node-row').forEach((row, index) => {
        row.dataset.position = index;
        row.querySelector('.node-position').textContent = index + 1;
        const mappingsContainer = row.querySelector('.mappings-container');
        if (index === 0) {
            mappingsContainer.classList.add('hidden');
            mappingsContainer.innerHTML = '';
        } else {
            mappingsContainer.classList.remove('hidden');
            updateMappingOptions(row.querySelector('select[name$="[id]"]'));
        }
    });
    reindexFormElements();
}

function reindexFormElements() {
    document.querySelectorAll('.node-row').forEach((row, index) => {
        row.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/nodes\[\d+\]/, `nodes[${index}]`);
        });
    });
}

function moveNodeUp(btn) {
    const row = btn.closest('.node-row');
    const prev = row.previousElementSibling;
    if (prev && prev.classList.contains('node-row')) {
        row.parentNode.insertBefore(row, prev);
        updateNodePositions();
    }
}

function moveNodeDown(btn) {
    const row = btn.closest('.node-row');
    const next = row.nextElementSibling;
    if (next && next.classList.contains('node-row')) {
        row.parentNode.insertBefore(next, row);
        updateNodePositions();
    }
}

function getPreviousNodesData() {
    const rows = document.querySelectorAll('.node-row');
    const previousNodes = [];
    rows.forEach((row, index) => {
        const select = row.querySelector('select[name$="[id]"]');
        if (select && select.value) {
            const node = getNodeById(select.value);
            if (node) {
                previousNodes.push({
                    position: index,
                    node: node
                });
            }
        }
    });
    return previousNodes;
}

function updateMappingOptions(select, initialMappings = null) {
    const row = select.closest('.node-row');
    const position = parseInt(row.dataset.position);
    const mappingsContainer = row.querySelector('.mappings-container');

    if (initialMappings === null) {
        try {
            initialMappings = JSON.parse(mappingsContainer.dataset.initialMappings || '{}');
        } catch (e) {
            initialMappings = {};
        }
    }

    if (position === 0) {
        mappingsContainer.innerHTML = '';
        return;
    }

    const selectedNode = getNodeById(select.value);
    if (!selectedNode || !Array.isArray(selectedNode.inputs) || selectedNode.inputs.length === 0) {
        mappingsContainer.innerHTML = '<p class="text-text-muted text-sm">This node has no inputs to map.</p>';
        return;
    }

    const previousNodes = getPreviousNodesData().filter(p => p.position < position);

    let html = '<div class="space-y-3">';
    html += '<p class="text-cortex-cyan text-sm font-medium mb-2">Field Mappings</p>';

    selectedNode.inputs.forEach(input => {
        const mapping = initialMappings[input.name] || {};
        const mappingType = mapping.type || '';
        const sourcePosition = mapping.source_position !== undefined ? mapping.source_position : '';
        const sourceField = mapping.source_field || '';

        html += `
            <div class="flex flex-wrap items-center gap-2 p-2 bg-cortex-dark rounded-lg">
                <span class="text-sm text-text min-w-32">${input.label}</span>
                <select name="nodes[${position}][mappings][${input.name}][type]" onchange="toggleSourceField(this)" class="p-2 bg-cortex-panel border border-gray rounded-lg text-text text-sm">
                    <option value="" ${mappingType === '' ? 'selected' : ''}>No mapping</option>
                    <option value="output" ${mappingType === 'output' ? 'selected' : ''}>Previous output</option>
                    <option value="input" ${mappingType === 'input' ? 'selected' : ''}>Previous input field</option>
                </select>
                <select name="nodes[${position}][mappings][${input.name}][source_position]" class="source-position p-2 bg-cortex-panel border border-gray rounded-lg text-text text-sm ${mappingType ? '' : 'hidden'}">
                    ${previousNodes.map(p => `<option value="${p.position}" ${sourcePosition == p.position ? 'selected' : ''}>Step ${p.position + 1}: ${p.node.name}</option>`).join('')}
                </select>
                <select name="nodes[${position}][mappings][${input.name}][source_field]" class="source-field p-2 bg-cortex-panel border border-gray rounded-lg text-text text-sm ${mappingType === 'input' ? '' : 'hidden'}">
                    <option value="">Select field...</option>
                </select>
            </div>
        `;
    });

    html += '</div>';
    mappingsContainer.innerHTML = html;

    // Populate source fields for 'input' type mappings
    selectedNode.inputs.forEach(input => {
        const mapping = initialMappings[input.name] || {};
        if (mapping.type === 'input') {
            const container = mappingsContainer.querySelector(`select[name$="[${input.name}][type]"]`).closest('.flex');
            const positionSelect = container.querySelector('.source-position');
            const fieldSelect = container.querySelector('.source-field');

            const sourcePos = parseInt(positionSelect.value);
            const sourceNodeData = previousNodes.find(p => p.position === sourcePos);

            if (sourceNodeData && Array.isArray(sourceNodeData.node.inputs)) {
                let fieldHtml = '<option value="">Select field...</option>';
                sourceNodeData.node.inputs.forEach(srcInput => {
                    fieldHtml += `<option value="${srcInput.name}" ${mapping.source_field === srcInput.name ? 'selected' : ''}>${srcInput.label}</option>`;
                });
                fieldSelect.innerHTML = fieldHtml;
            }
        }
    });

    // Clear initial mappings after first use
    mappingsContainer.dataset.initialMappings = '{}';
}

function toggleSourceField(typeSelect) {
    const container = typeSelect.closest('.flex');
    const positionSelect = container.querySelector('.source-position');
    const fieldSelect = container.querySelector('.source-field');
    const type = typeSelect.value;

    if (type === '') {
        positionSelect.classList.add('hidden');
        fieldSelect.classList.add('hidden');
    } else if (type === 'output') {
        positionSelect.classList.remove('hidden');
        fieldSelect.classList.add('hidden');
    } else if (type === 'input') {
        positionSelect.classList.remove('hidden');
        fieldSelect.classList.remove('hidden');
        updateSourceFieldOptions(positionSelect);
    }
}

function updateSourceFieldOptions(positionSelect) {
    const container = positionSelect.closest('.flex');
    const fieldSelect = container.querySelector('.source-field');
    const sourcePosition = parseInt(positionSelect.value);

    const previousNodes = getPreviousNodesData();
    const sourceNodeData = previousNodes.find(p => p.position === sourcePosition);

    if (!sourceNodeData || !Array.isArray(sourceNodeData.node.inputs) || sourceNodeData.node.inputs.length === 0) {
        fieldSelect.innerHTML = '<option value="">No fields available</option>';
        return;
    }

    let html = '<option value="">Select field...</option>';
    sourceNodeData.node.inputs.forEach(input => {
        html += `<option value="${input.name}">${input.label}</option>`;
    });
    fieldSelect.innerHTML = html;
}

// Event delegation for source position changes
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('source-position')) {
        const container = e.target.closest('.flex');
        const typeSelect = container.querySelector('select[name$="[type]"]');
        if (typeSelect.value === 'input') {
            updateSourceFieldOptions(e.target);
        }
    }
});

function addNode() {
    const container = document.getElementById('nodes-container');
    const position = container.querySelectorAll('.node-row').length;

    const html = `
        <div class="node-row p-4 bg-cortex-panel rounded-lg border border-gray" data-position="${position}">
            <div class="flex gap-4">
                <div class="flex flex-col gap-1 justify-center">
                    <button type="button" onclick="moveNodeUp(this)" class="p-1 rounded bg-cortex-panel-light hover:bg-cortex-blue hover:text-cortex-black transition-colors" title="Move up">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                    </button>
                    <button type="button" onclick="moveNodeDown(this)" class="p-1 rounded bg-cortex-panel-light hover:bg-cortex-blue hover:text-cortex-black transition-colors" title="Move down">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-4 mb-3">
                        <span class="node-position text-cortex-orange font-bold text-lg" style="font-family: var(--font-display);">${position + 1}</span>
                        <select name="nodes[${nodeIndex}][id]" onchange="updateMappingOptions(this)" required class="flex-1 p-2 bg-cortex-dark border border-gray rounded-lg text-text focus:border-cortex-cyan">
                            <option value="">Select a node...</option>
                            ${allNodes.map(node => `<option value="${node.id}" data-inputs='${JSON.stringify(node.inputs || [])}'>${node.name}</option>`).join('')}
                        </select>
                        <button type="button" onclick="this.closest('.node-row').remove(); updateNodePositions();" class="px-3 py-1.5 rounded-full bg-cortex-red text-white text-xs uppercase tracking-wider" style="font-family: var(--font-display);">Remove</button>
                    </div>
                    <div class="mappings-container ${position > 0 ? 'mt-3 p-3 bg-cortex-dark rounded-lg border border-cortex-cyan/30' : 'hidden'}" data-initial-mappings="{}">
                        ${position > 0 ? '<p class="text-text-muted text-sm">Select a node above to configure field mappings from previous steps.</p>' : ''}
                    </div>
                    <label class="include-in-output-label flex items-center gap-2 text-sm text-text-muted cursor-pointer mt-3">
                        <input type="hidden" name="nodes[${nodeIndex}][include_in_output]" value="0">
                        <input type="checkbox" name="nodes[${nodeIndex}][include_in_output]" value="1" checked class="w-4 h-4 rounded border-gray bg-cortex-dark text-cortex-green">
                        <span>Include in copy all</span>
                    </label>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
    nodeIndex++;
}

// Expose functions globally for inline onclick handlers
window.addNode = addNode;
window.moveNodeUp = moveNodeUp;
window.moveNodeDown = moveNodeDown;
window.updateNodePositions = updateNodePositions;
window.updateMappingOptions = updateMappingOptions;
window.toggleSourceField = toggleSourceField;
