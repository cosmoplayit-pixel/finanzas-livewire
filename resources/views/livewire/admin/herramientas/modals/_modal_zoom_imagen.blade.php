    {{-- ===================== MODAL ZOOM IMAGEN ===================== --}}
    <div x-data="{ imgUrl: null, open: false, isPdf: false }"
        @open-image-modal.window="imgUrl = $event.detail; isPdf = imgUrl.toLowerCase().endsWith('.pdf') || imgUrl.startsWith('blob:'); open = true">
        <div x-show="open" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 p-4"
            @click="open = false" @keydown.escape.window="open = false">
            <button class="absolute top-4 right-4 text-white hover:text-gray-300 z-10" @click="open = false">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="relative w-full max-w-5xl h-full flex items-center justify-center" @click.stop>
                <template x-if="!isPdf">
                    <img :src="imgUrl" class="max-w-full max-h-[90vh] object-contain rounded-xl shadow-2xl">
                </template>
                <template x-if="isPdf">
                    <iframe :src="imgUrl" class="w-full h-[90vh] rounded-xl shadow-2xl bg-white"
                        frameborder="0"></iframe>
                </template>
            </div>
        </div>
    </div>
