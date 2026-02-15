/**
 * AJAX Save Component for Alpine.js
 * Provides form change detection, keyboard shortcuts, and AJAX submission.
 */
document.addEventListener('alpine:init', () => {
    Alpine.data('ajaxSave', () => ({
        form: null,
        isDirty: false,
        isSaving: false,
        justSaved: false,
        hasError: false,
        formExists: false,
        initialFormData: null,

        init() {
            this.form = document.querySelector('form[data-ajax-save]');
            this.formExists = !!this.form;

            if (!this.form) return;

            // Store initial form state
            this.initialFormData = new FormData(this.form);

            // Listen for form changes
            this.form.addEventListener('input', () => this.markDirty());
            this.form.addEventListener('change', () => this.markDirty());

            // Listen for dynamically added/removed inputs (e.g., wave inputs)
            // Delay observer to allow initial JS setup to complete without triggering dirty state
            setTimeout(() => {
                const observer = new MutationObserver(() => this.markDirty());
                observer.observe(this.form, { childList: true, subtree: true });
            }, 500);
        },

        markDirty() {
            this.isDirty = true;
            this.justSaved = false;
            this.hasError = false;
        },

        get statusText() {
            if (this.isSaving) return 'Saving...';
            if (this.justSaved) return 'Saved!';
            if (this.isDirty) return 'Unsaved changes';
            return '';
        },

        async save() {
            if (!this.form || !this.isDirty || this.isSaving) return;

            this.isSaving = true;
            this.hasError = false;

            const formData = new FormData(this.form);
            const action = this.form.getAttribute('action');
            const method = this.form.querySelector('input[name="_method"]')?.value || 'POST';

            try {
                const response = await fetch(action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.isDirty = false;
                    this.justSaved = true;
                    this.initialFormData = new FormData(this.form);

                    // Clear any existing error messages
                    this.clearErrors();

                    // Auto-hide success after 2 seconds
                    setTimeout(() => {
                        this.justSaved = false;
                    }, 2000);
                } else {
                    this.hasError = true;
                    // Display validation errors
                    if (data.errors) {
                        this.displayErrors(data.errors);
                    } else if (data.message) {
                        this.displayErrors({ general: [data.message] });
                    }
                }
            } catch (error) {
                this.hasError = true;
                this.displayErrors({ general: ['An error occurred while saving.'] });
            } finally {
                this.isSaving = false;
            }
        },

        clearErrors() {
            const errorContainer = this.form.closest('div').querySelector('.ajax-error-container');
            if (errorContainer) {
                errorContainer.remove();
            }
            // Also clear any existing server-rendered error block
            const existingErrors = this.form.closest('div').querySelector('.mb-6.p-4.bg-cortex-red\\/20');
            if (existingErrors) {
                existingErrors.remove();
            }
        },

        displayErrors(errors) {
            this.clearErrors();

            // Find the form's parent container and insert errors before the form
            const formParent = this.form.parentElement;
            const errorHtml = document.createElement('div');
            errorHtml.className = 'ajax-error-container mb-6 p-4 bg-cortex-red/20 border border-cortex-red/50 text-cortex-red rounded-lg';

            const messages = Object.values(errors).flat();
            errorHtml.innerHTML = messages.map(msg => `<p>${this.escapeHtml(msg)}</p>`).join('');

            formParent.insertBefore(errorHtml, this.form);

            // Scroll errors into view
            errorHtml.scrollIntoView({ behavior: 'smooth', block: 'center' });
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }));
});
