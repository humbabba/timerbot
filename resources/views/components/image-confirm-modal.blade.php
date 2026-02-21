<div
    x-data="{
        open: false,
        callback: null,
        show(callback) {
            this.callback = callback;
            this.open = true;
        },
        confirm() {
            if (this.callback) {
                this.callback();
            }
            this.open = false;
        }
    }"
    x-on:confirm-image-generation.window="show($event.detail.callback)"
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
            class="fixed inset-0 bg-timerbot-black/80"
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
            class="relative bg-timerbot-panel border border-dark-green rounded-sm shadow-2xl max-w-md w-full overflow-hidden"
            x-on:click.stop
        >
            <!-- Header bar -->
            <div class="h-2 bg-timerbot-green"></div>

            <div class="p-6">
                <h3 class="text-xl font-semibold mb-2 text-timerbot-mint" style="font-family: var(--font-display);">Generate Image?</h3>
                <p class="text-text-muted mb-4">This node is configured to generate an image using an AI image API.</p>
                <p class="text-text-muted mb-6">Proceeding will make an API call, which may incur costs. Do you want to continue?</p>

                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        x-on:click.prevent.stop="open = false"
                        class="px-5 py-2 rounded-none bg-timerbot-panel-light text-text hover:bg-timerbot-mint hover:text-timerbot-black transition-all duration-200 uppercase text-sm tracking-wider"
                        style="font-family: var(--font-display);"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        x-on:click.prevent.stop="confirm()"
                        class="px-5 py-2 rounded-none bg-timerbot-mint text-timerbot-black hover:shadow-lg hover:shadow-timerbot-mint/30 transition-all duration-200 uppercase text-sm tracking-wider"
                        style="font-family: var(--font-display);"
                    >
                        Generate Image
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
