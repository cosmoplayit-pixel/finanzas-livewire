    {{-- ===================== MODAL ZOOM IMAGEN ===================== --}}
    <div x-data="{ imgUrl: null, open: false }" @open-image-modal.window="imgUrl = $event.detail; open = true">
        <div x-show="open" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 p-4"
            @click="open = false" @keydown.escape.window="open = false">
            <button class="absolute top-4 right-4 text-white hover:text-gray-300" @click="open = false">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <img :src="imgUrl" class="max-w-full max-h-[90vh] object-contain rounded-xl shadow-2xl"
                @click.stop>
        </div>
    </div>

