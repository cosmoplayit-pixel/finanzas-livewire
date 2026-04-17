    {{-- ===================== VISOR DE FOTOS ===================== --}}
    <div x-data="{
        open: false,
        photos: [],
        currentIndex: 0,
        title: '',
        getPdfUrl(url) {
            if (url && url.startsWith('data:application/pdf;base64,')) {
                try {
                    const b64 = url.split(',')[1];
                    const binary = atob(b64);
                    const array = new Uint8Array(binary.length);
                    for (let i = 0; i < binary.length; i++) {
                        array[i] = binary.charCodeAt(i);
                    }
                    const blob = new Blob([array], { type: 'application/pdf' });
                    return URL.createObjectURL(blob);
                } catch (e) {
                    console.error('Error generating PDF blob URL', e);
                }
            }
            return url;
        }
    }"
        @open-viewer.window="photos = $event.detail.photos; title = $event.detail.title; currentIndex = 0; open = true"
        @keydown.escape.window="open = false" @keydown.arrow-left.window="if(open && currentIndex > 0) currentIndex--"
        @keydown.arrow-right.window="if(open && currentIndex < photos.length - 1) currentIndex++" class="relative z-[100]"
        x-cloak>

        <div x-show="open" class="fixed inset-0 bg-neutral-900/90 backdrop-blur-sm transition-opacity"></div>

        <div x-show="open" class="fixed inset-0 z-10 overflow-hidden">
            <div class="flex h-screen items-center justify-center p-2 sm:p-4 text-center">
                <div @click.away="open = false"
                    class="relative transform overflow-hidden rounded-xl w-full max-w-6xl h-full sm:h-[96vh] text-left shadow-2xl transition-all flex flex-col">

                    {{-- Toolbar --}}
                    <div
                        class="flex-shrink-0 relative bg-neutral-900 p-4 flex justify-between items-center z-20 border-b border-neutral-800">
                        <span class="text-white font-bold tracking-wide"
                            x-text="title + ' (' + (currentIndex + 1) + '/' + photos.length + ')'"></span>
                        <button @click="open = false"
                            class="text-white hover:text-red-400 transition bg-black/40 rounded-full p-2 cursor-pointer">
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Controles Paginación --}}
                    <template x-if="photos.length > 1">
                        <button @click="currentIndex > 0 ? currentIndex-- : null"
                            class="absolute left-4 top-1/2 -translate-y-1/2 bg-black/50 text-white p-3 rounded-full hover:bg-black/80 transition z-20 cursor-pointer disabled:opacity-30"
                            :disabled="currentIndex === 0">
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    </template>
                    <template x-if="photos.length > 1">
                        <button @click="currentIndex < photos.length - 1 ? currentIndex++ : null"
                            class="absolute right-4 top-1/2 -translate-y-1/2 bg-black/50 text-white p-3 rounded-full hover:bg-black/80 transition z-20 cursor-pointer disabled:opacity-30"
                            :disabled="currentIndex === photos.length - 1">
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </template>

                    {{-- Contenido (Imagen o PDF) --}}
                    <div class="bg-black flex-1 flex items-center justify-center w-full overflow-hidden">
                        <template
                            x-if="photos[currentIndex] && (photos[currentIndex].toLowerCase().endsWith('.pdf') || photos[currentIndex].toLowerCase().includes('.pdf?') || photos[currentIndex].startsWith('data:application/pdf'))">
                            <iframe :src="getPdfUrl(photos[currentIndex])" class="w-full h-full bg-white border-0"
                                title="PDF Viewer"></iframe>
                        </template>
                        <template
                            x-if="photos[currentIndex] && !photos[currentIndex].toLowerCase().endsWith('.pdf') && !photos[currentIndex].toLowerCase().includes('.pdf?') && !photos[currentIndex].startsWith('data:application/pdf')">
                            <img :src="photos[currentIndex]" class="max-w-full max-h-full object-contain">
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
