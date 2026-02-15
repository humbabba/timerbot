<div
    x-data="{
        open: false,
        title: '',
        message: '',
        formId: '',
        show(title, message, formId) {
            this.title = title;
            this.message = message;
            this.formId = formId;
            this.open = true;
        },
        confirm() {
            document.getElementById(this.formId).submit();
            this.open = false;
        }
    }"
    x-on:confirm-delete.window="show($event.detail.title, $event.detail.message, $event.detail.formId)"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
>
    <div class="flex items-center justify-center min-h-screen px-4">
        <!-- Backdrop -->
        <div
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-cortex-black/80"
            x-on:click.prevent.stop="open = false"
        ></div>

        <!-- Modal -->
        <div
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative bg-cortex-panel border border-gray rounded-2xl shadow-2xl max-w-md w-full overflow-hidden"
            x-on:click.stop
        >
            <!-- Header bar -->
            <div class="h-2 bg-gradient-to-r from-cortex-red via-cortex-orange to-cortex-peach"></div>

            <div class="p-6">
                <h3 class="text-xl font-semibold mb-2 text-cortex-red" style="font-family: var(--font-display);" x-text="title"></h3>
                <p class="text-text-muted mb-6" x-text="message"></p>

                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        x-on:click.prevent.stop="open = false"
                        class="px-5 py-2 rounded-full bg-cortex-panel-light text-text hover:bg-cortex-lavender hover:text-cortex-black transition-all duration-200 uppercase text-sm tracking-wider"
                        style="font-family: var(--font-display);"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        x-on:click.prevent.stop="confirm()"
                        class="px-5 py-2 rounded-full bg-cortex-red text-white hover:shadow-lg hover:shadow-cortex-red/30 transition-all duration-200 uppercase text-sm tracking-wider"
                        style="font-family: var(--font-display);"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
