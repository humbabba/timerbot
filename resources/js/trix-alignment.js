/**
 * Trix Editor Alignment Extension
 *
 * Adds center and right alignment buttons to the Trix toolbar.
 * Must be imported BEFORE 'trix' so the event listeners are ready.
 */

// Register custom block attributes before Trix initializes each editor
document.addEventListener('trix-before-initialize', () => {
    const Trix = window.Trix;
    if (!Trix) return;

    Trix.config.blockAttributes.alignCenter = {
        tagName: 'align-center',
        exclusive: true,
    };

    Trix.config.blockAttributes.alignRight = {
        tagName: 'align-right',
        exclusive: true,
    };
});

// Add alignment buttons to the toolbar after each editor initializes
document.addEventListener('trix-initialize', (e) => {
    const toolbar = e.target.toolbarElement;
    if (!toolbar || toolbar.querySelector('.trix-button-group--alignment')) return;

    const buttonRow = toolbar.querySelector('.trix-button-row');
    if (!buttonRow) return;

    const group = document.createElement('span');
    group.className = 'trix-button-group trix-button-group--alignment';
    group.dataset.trixButtonGroup = 'alignment-tools';

    group.innerHTML = `
        <button type="button" class="trix-button trix-button--icon trix-button--align-center"
                data-trix-attribute="alignCenter" title="Align Center" tabindex="-1"></button>
        <button type="button" class="trix-button trix-button--icon trix-button--align-right"
                data-trix-attribute="alignRight" title="Align Right" tabindex="-1"></button>
    `;

    buttonRow.appendChild(group);
});
