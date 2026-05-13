@props([
    'model',
    'label' => 'Comprobante',
    'file' => null,
    'accept' => '.jpg,.jpeg,.png,.pdf',
    'existingUrl' => null,
    'existingName' => null,
    'deleteModel' => null,
])


<div x-data="documentScanner('{{ $model }}')" @keydown.escape.window="closeAll()" @mouseenter="hovered = true" @mouseleave="hovered = false"
    @paste.window="if (hovered) handlePaste($event)" class="relative">

    {{-- ── Input área ─────────────────────────────────────── --}}
    <label class="block text-sm mb-1">{{ $label }} <span class="text-red-500">*</span></label>
    <div :class="(isUploading || removing) ? 'opacity-50 pointer-events-none' : (hovered ?
        'border-indigo-300 dark:border-indigo-700 bg-indigo-50/30 dark:bg-indigo-900/10' : '')"
        class="group h-11 flex items-center justify-between w-full rounded-lg border border-dashed border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-4 py-2 hover:bg-gray-50 dark:hover:bg-neutral-800 transition">
        <label class="flex items-center gap-3 min-w-0 flex-1 cursor-pointer">
            <div
                class="w-7 h-7 rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-gray-600 dark:text-neutral-200" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="17 8 12 3 7 8" />
                    <line x1="12" y1="3" x2="12" y2="15" />
                </svg>
            </div>
            <div class="min-w-0">
                <div class="text-sm font-medium text-gray-800 dark:text-neutral-100">Adjuntar archivo</div>
                <div class="text-xs text-gray-500 dark:text-neutral-400 truncate">
                    @if ($file)
                        {{ method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : 'Archivo seleccionado' }}
                    @elseif ($existingUrl)
                        {{ $existingName ?? basename($existingUrl) }}
                    @else
                        JPG, PNG o PDF (máx. 5 MB)
                    @endif
                </div>
            </div>
            <input type="file" accept="{{ $accept }}" class="hidden" :disabled="isUploading || removing"
                @change="uploadFile($event.target.files[0])" />
        </label>
        <button type="button" @click="startCamera()" title="Escanear con cámara"
            class="md:hidden shrink-0 p-1.5 rounded-md text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 transition-colors cursor-pointer border border-transparent hover:border-indigo-200">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z" />
                <circle cx="12" cy="13" r="3" />
            </svg>
        </button>
        <input type="file" x-ref="cameraInput" @change="handleFileCapture($event)" capture="environment"
            accept="image/*" class="hidden" />
    </div>
    @error($model)
        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
    @enderror
    @if (!$file && $existingUrl)
        @php
            $isExistingPdf = $existingUrl && \Illuminate\Support\Str::contains(strtolower($existingUrl), '.pdf');
        @endphp
        <div class="mt-1 text-xs flex items-center justify-end gap-3 h-5">
            <button type="button"
                onclick="window.dispatchEvent(new CustomEvent('open-image-modal', { detail: '{{ $existingUrl }}' }))"
                class="cursor-pointer inline-flex items-center gap-1 text-indigo-500 hover:text-indigo-600 font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                {{ $isExistingPdf ? 'Ver PDF' : 'Ver imagen' }}
            </button>
            @if ($deleteModel)
                <div class="flex items-center">
                    <div wire:loading wire:target="$set('{{ $deleteModel }}', true)"
                        class="text-red-500 font-bold animate-pulse">
                        Quitando…
                    </div>
                    <button type="button" wire:click="$set('{{ $deleteModel }}', true)" wire:loading.remove
                        wire:target="$set('{{ $deleteModel }}', true)"
                        class="cursor-pointer text-red-500 hover:text-red-600 font-medium">
                        Quitar archivo
                    </button>
                </div>
            @endif
        </div>
    @endif
    @if ($file)
        @php
            $isPdf =
                method_exists($file, 'getClientOriginalExtension') &&
                strtolower($file->getClientOriginalExtension()) === 'pdf';
            // Evitamos llamar a temporaryUrl() para PDFs para no causar el error de Livewire
            $previewUrl = !$isPdf && method_exists($file, 'temporaryUrl') ? $file->temporaryUrl() : null;
        @endphp
        <div>
            <div class="mt-1 text-xs flex items-center justify-end gap-3 h-5">
                {{-- Cargando --}}
                <div wire:loading wire:target="{{ $model }}"
                    class="text-indigo-500 font-bold animate-pulse flex items-center gap-1">
                    <span x-show="removing">Quitando…</span>
                    <span x-show="!removing">Subiendo…</span>
                </div>

                {{-- Acciones --}}
                <div wire:loading.remove wire:target="{{ $model }}" x-show="!removing && !isUploading"
                    class="flex items-center gap-3" x-data="{
                        get isPdf() {
                            const name = '{{ method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : '' }}';
                            return name.toLowerCase().endsWith('.pdf') || !!this.localPdfUrl;
                        }
                    }">
                    <template x-if="localPdfUrl">
                        <button type="button"
                            @click="window.dispatchEvent(new CustomEvent('open-image-modal', { detail: localPdfUrl }))"
                            class="cursor-pointer inline-flex items-center gap-1 text-indigo-500 hover:text-indigo-600 font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            Ver PDF
                        </button>
                    </template>
                    <template x-if="!localPdfUrl && @js($previewUrl)">
                        <button type="button"
                            @click="window.dispatchEvent(new CustomEvent('open-image-modal', { detail: @js($previewUrl) }))"
                            class="cursor-pointer inline-flex items-center gap-1 text-indigo-500 hover:text-indigo-600 font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            Ver imagen
                        </button>
                    </template>
                    <button type="button"
                        @click="removing = true; if(localPdfUrl) URL.revokeObjectURL(localPdfUrl); localPdfUrl = ''; $wire.set('{{ $model }}', null).then(() => { removing = false; });"
                        class="cursor-pointer text-red-500 hover:text-red-600 font-medium">
                        Quitar archivo
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Cargando cuando no hay archivo seleccionado aún (subida inicial) --}}
    @if (!$file)
        <div class="mt-1 text-xs flex justify-end">
            <div wire:loading wire:target="{{ $model }}" class="text-indigo-500 font-bold animate-pulse">
                Subiendo…
            </div>
        </div>
    @endif

    {{-- ── Overlay de procesamiento ────────────────────────── --}}
    <template x-if="isProcessing">
        <div class="fixed inset-0 z-[10001] bg-black/80 backdrop-blur-sm flex items-center justify-center">
            <div
                class="flex flex-col items-center gap-4 bg-neutral-900 rounded-2xl px-10 py-8 border border-white/10 shadow-2xl">
                <div class="w-10 h-10 border-2 border-white/10 border-t-indigo-400 rounded-full animate-spin"></div>
                <span class="text-white text-sm font-medium" x-text="processingMsg"></span>
            </div>
        </div>
    </template>

    {{-- ════════════════════════════════════════════════
         FASE: CÁMARA
    ════════════════════════════════════════════════ --}}
    <template x-if="phase === 'camera'">
        <div class="fixed inset-0 z-[9999] bg-black/90 flex items-center justify-center p-3 backdrop-blur-md">
            <div
                class="relative w-full max-w-6xl h-[90vh] bg-neutral-950 rounded-3xl overflow-hidden shadow-2xl border border-white/10 flex flex-col">
                <div class="relative flex-1 bg-black overflow-hidden flex items-center justify-center"
                    :class="(useDigitalZoom && zoomValue > 1) ? 'cursor-grab active:cursor-grabbing' : 'cursor-crosshair'"
                    @wheel.prevent="handleWheel($event)" @mousedown="startPan($event)"
                    @touchstart.prevent="startPan($event)">
                    <video x-ref="video" autoplay playsinline muted
                        class="w-full h-full object-contain transition-transform duration-75"
                        :style="'transform-origin: ' + zoomPosX + '% ' + zoomPosY + '%; transform:rotate(' + rotation +
                            'deg) scale(' + (getRotationScale() * (useDigitalZoom ? zoomValue : 1)) +
                            '); filter:brightness(' + brightness + '%) contrast(' + contrast + '%)'">
                    </video>
                    <div class="absolute inset-0 pointer-events-none grid grid-cols-3 grid-rows-3 opacity-20">
                        <div class="border-r border-b border-white/50"></div>
                        <div class="border-r border-b border-white/50"></div>
                        <div class="border-b border-white/50"></div>
                        <div class="border-r border-b border-white/50"></div>
                        <div class="border-r border-b border-white/50"></div>
                        <div class="border-b border-white/50"></div>
                        <div class="border-r border-white/50"></div>
                        <div class="border-r border-white/50"></div>
                        <div></div>
                    </div>
                    <div x-show="isShuttering" class="absolute inset-0 bg-white z-50 pointer-events-none"></div>
                    <div
                        class="absolute top-4 left-4 bg-black/60 backdrop-blur px-3 py-1.5 rounded-lg border border-white/10 text-[10px] text-white/80 font-bold flex gap-3">
                        <span x-text="parseFloat(zoomValue).toFixed(1)+'×'"></span>
                        <span class="border-l border-white/20 pl-3" x-text="rotation+'°'"></span>
                    </div>
                    <template x-if="canZoom">
                        <div class="absolute bottom-5 left-1/2 -translate-x-1/2 bg-black/60 backdrop-blur px-6 py-3 rounded-2xl border border-white/10"
                            style="width:min(70%,400px)" @click.stop>
                            <input type="range" x-model="zoomValue" :min="minZoom" :max="maxZoom"
                                :step="stepZoom" @input="applyOpticalZoom()"
                                class="w-full h-1 accent-indigo-500 bg-white/20 rounded appearance-none cursor-pointer">
                        </div>
                    </template>
                </div>
                <div class="px-6 py-5 flex items-center justify-between bg-neutral-950 border-t border-white/5">
                    <button type="button" @click="closeAll()"
                        class="text-neutral-400 hover:text-white text-sm font-medium transition">Cancelar</button>
                    <button type="button" @click="capture()"
                        class="w-16 h-16 rounded-full border-4 border-white/80 flex items-center justify-center bg-white/5 hover:bg-white/15 hover:scale-105 transition-transform active:scale-95">
                        <div class="w-11 h-11 rounded-full bg-white shadow-lg"></div>
                    </button>
                    <div class="flex gap-2">
                        <button type="button" @click="rotateLeft()"
                            class="p-3 rounded-xl bg-white/5 hover:bg-white/15 text-white border border-white/5 transition">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M2.5 2v6h6M2.66 15.57a10 10 0 1 0 .57-8.38" />
                            </svg>
                        </button>
                        <button type="button" @click="rotateRight()"
                            class="p-3 rounded-xl bg-white/5 hover:bg-white/15 text-white border border-white/5 transition">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- ════════════════════════════════════════════════
         FASE: EDITOR UNIFICADO — sidebar izquierdo
    ════════════════════════════════════════════════ --}}
    <template x-if="phase === 'editor'">
        <div class="fixed inset-0 z-[9999] bg-neutral-950 flex flex-col">

            {{-- ── Header ─────────────────────────────── --}}
            <div class="flex items-center justify-between px-4 py-2.5 bg-black/50 border-b border-white/5 shrink-0">
                <button type="button" @click="closeAll()"
                    class="flex items-center gap-1 text-neutral-400 hover:text-white text-sm transition">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                    Cancelar
                </button>
                <span class="text-white/60 text-sm font-medium">Editar documento</span>
                <button type="button" @click="confirmAll()"
                    class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm transition active:scale-95">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    Confirmar
                </button>
            </div>

            {{-- ── Cuerpo: sidebar + imagen ────────────── --}}
            {{-- Desktop: flex-row | Mobile: flex-col (imagen arriba, controles abajo) --}}
            <div class="flex flex-1 overflow-hidden min-h-0 flex-col md:flex-row">

                {{-- SIDEBAR IZQUIERDO (solo desktop) --}}
                <div class="hidden md:flex w-56 shrink-0 bg-black/40 border-r border-white/5 flex-col overflow-y-auto">

                    {{-- Botones de pestaña --}}
                    <div class="p-3 flex flex-col gap-1">
                        <button type="button" @click="switchTab('persp')"
                            class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-left transition w-full"
                            :class="editorTab === 'persp' ? 'bg-white/10 text-white' :
                                'text-white/40 hover:text-white hover:bg-white/5'">
                            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M21 3L3 9l7 3 3 9 8-18z" />
                            </svg>
                            Perspectiva
                        </button>
                        <button type="button" @click="switchTab('crop')"
                            class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-left transition w-full"
                            :class="editorTab === 'crop' ? 'bg-white/10 text-white' :
                                'text-white/40 hover:text-white hover:bg-white/5'">
                            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <polyline points="6 2 6 6 2 6" />
                                <polyline points="18 2 18 6 22 6" />
                                <polyline points="6 22 6 18 2 18" />
                                <polyline points="18 22 18 18 22 18" />
                            </svg>
                            Recortar
                        </button>
                        <button type="button" @click="switchTab('adjust')"
                            class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-left transition w-full"
                            :class="editorTab === 'adjust' ? 'bg-white/10 text-white' :
                                'text-white/40 hover:text-white hover:bg-white/5'">
                            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <line x1="4" y1="21" x2="4" y2="14" />
                                <line x1="4" y1="10" x2="4" y2="3" />
                                <line x1="12" y1="21" x2="12" y2="12" />
                                <line x1="12" y1="8" x2="12" y2="3" />
                                <line x1="20" y1="21" x2="20" y2="16" />
                                <line x1="20" y1="12" x2="20" y2="3" />
                                <line x1="1" y1="14" x2="7" y2="14" />
                                <line x1="9" y1="8" x2="15" y2="8" />
                                <line x1="17" y1="16" x2="23" y2="16" />
                            </svg>
                            Ajustes
                        </button>
                    </div>

                    {{-- Separador --}}
                    <div class="border-t border-white/5 mx-3"></div>

                    {{-- Perspectiva --}}
                    <div x-show="editorTab === 'persp'" class="p-4 flex flex-col gap-4">
                        <p class="text-white/35 text-xs leading-relaxed">Arrastrá las 4 esquinas (puntos de colores) al
                            borde exacto del documento.</p>
                        <button type="button"
                            @click="handles=[{x:0,y:0},{x:100,y:0},{x:100,y:100},{x:0,y:100}]; _warpDirty=true"
                            class="w-full text-xs text-white/40 hover:text-white border border-white/10 hover:border-white/20 py-2 rounded-xl transition font-medium">
                            Resetear esquinas
                        </button>
                    </div>

                    {{-- Recortar --}}
                    <div x-show="editorTab === 'crop'" class="p-4 flex flex-col gap-4">
                        <p class="text-white/35 text-xs leading-relaxed">Arrastrá los bordes y esquinas del recuadro
                            blanco para ajustar el recorte.</p>
                        <button type="button" @click="crop={x:0,y:0,w:100,h:100}"
                            class="w-full text-xs text-white/40 hover:text-white border border-white/10 hover:border-white/20 py-2 rounded-xl transition font-medium">
                            Resetear recorte
                        </button>
                    </div>

                    {{-- Ajustes --}}
                    <div x-show="editorTab === 'adjust'" class="p-4 flex flex-col gap-5">
                        <div>
                            <div class="flex justify-between mb-2">
                                <span
                                    class="text-[10px] text-white/50 uppercase font-black tracking-widest">Brillo</span>
                                <span class="text-[10px] text-indigo-400 font-mono" x-text="adjBrightness+'%'"></span>
                            </div>
                            <input type="range" x-model="adjBrightness" min="50" max="200"
                                class="w-full h-1.5 accent-indigo-500 bg-white/10 rounded-full appearance-none cursor-pointer">
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span
                                    class="text-[10px] text-white/50 uppercase font-black tracking-widest">Contraste</span>
                                <span class="text-[10px] text-indigo-400 font-mono" x-text="adjContrast+'%'"></span>
                            </div>
                            <input type="range" x-model="adjContrast" min="50" max="300"
                                class="w-full h-1.5 accent-indigo-500 bg-white/10 rounded-full appearance-none cursor-pointer">
                        </div>
                        <div class="border-t border-white/5 pt-3">
                            <button type="button" @click="adjBrightness=100;adjContrast=100"
                                class="w-full text-xs text-white/40 hover:text-white border border-white/10 hover:border-white/20 py-2 rounded-xl transition font-medium uppercase tracking-wider">
                                Reset ajustes
                            </button>
                        </div>
                    </div>

                </div>

                {{-- ÁREA DE IMAGEN --}}
                <div class="flex-1 bg-[#0d0d0d] flex items-center justify-center overflow-hidden p-4 min-h-0">

                    {{-- Perspectiva: imagen original con handles --}}
                    <div x-show="editorTab === 'persp'" class="relative"
                        style="display:inline-block;max-width:100%;max-height:100%">
                        <img x-ref="perspImg" :src="srcDataURL" class="block select-none rounded-lg shadow-2xl"
                            style="max-width:100%;max-height:calc(100vh - 56px);width:auto;height:auto;object-fit:contain"
                            draggable="false" />

                        <svg class="absolute inset-0 w-full h-full pointer-events-none" viewBox="0 0 100 100"
                            preserveAspectRatio="none">
                            <polygon :points="polyPoints" fill="rgba(99,102,241,0.08)"
                                stroke="rgba(99,102,241,0.85)" stroke-width="0.6" />
                            <line :x1="handles[0].x" :y1="handles[0].y" :x2="handles[2].x"
                                :y2="handles[2].y" stroke="rgba(255,255,255,0.12)" stroke-width="0.3" />
                            <line :x1="handles[1].x" :y1="handles[1].y" :x2="handles[3].x"
                                :y2="handles[3].y" stroke="rgba(255,255,255,0.12)" stroke-width="0.3" />
                        </svg>

                        <template x-for="(h, i) in handles" :key="i">
                            <div class="absolute touch-none z-10 cursor-grab active:cursor-grabbing"
                                :style="'left:' + h.x + '%;top:' + h.y + '%;transform:translate(-50%,-50%)'"
                                @mousedown.prevent="startDragHandle($event, i)"
                                @touchstart.prevent="startDragHandle($event, i)">
                                <div class="w-8 h-8 rounded-full border-[3px] border-white shadow-xl flex items-center justify-center"
                                    :class="(i === 0 || i === 2) ? 'bg-indigo-500' : 'bg-violet-500'">
                                    <div class="w-1.5 h-1.5 rounded-full bg-white opacity-90"></div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Recortar / Ajustes: imagen warpeada --}}
                    <div x-show="editorTab !== 'persp'" class="relative"
                        style="display:inline-block;max-width:100%;max-height:100%">
                        <img x-ref="warpedImg" :src="warpedDataURL" class="block select-none rounded-lg shadow-2xl"
                            :style="'max-width:100%;max-height:calc(100vh - 56px);width:auto;height:auto;filter:brightness(' +
                            adjBrightness + '%) contrast(' + adjContrast + '%)'"
                            draggable="false" />

                        {{-- Overlay de recorte --}}
                        <div x-show="editorTab === 'crop'" class="absolute inset-0">
                            <div class="absolute inset-0 pointer-events-none">
                                <svg class="w-full h-full">
                                    <defs>
                                        <mask id="cmask_{{ md5($model) }}">
                                            <rect width="100%" height="100%" fill="white" />
                                            <rect :x="crop.x + '%'" :y="crop.y + '%'"
                                                :width="crop.w + '%'" :height="crop.h + '%'" fill="black" />
                                        </mask>
                                    </defs>
                                    <rect width="100%" height="100%" fill="rgba(0,0,0,0.58)"
                                        mask="url(#cmask_{{ md5($model) }})" />
                                    <rect :x="crop.x + '%'" :y="crop.y + '%'" :width="crop.w + '%'"
                                        :height="crop.h + '%'" fill="none" stroke="white" stroke-width="1.5" />
                                    <line :x1="(crop.x + crop.w / 3) + '%'" :y1="crop.y + '%'"
                                        :x2="(crop.x + crop.w / 3) + '%'" :y2="(crop.y + crop.h) + '%'"
                                        stroke="white" stroke-width="0.4" opacity="0.22" />
                                    <line :x1="(crop.x + crop.w * 2 / 3) + '%'" :y1="crop.y + '%'"
                                        :x2="(crop.x + crop.w * 2 / 3) + '%'" :y2="(crop.y + crop.h) + '%'"
                                        stroke="white" stroke-width="0.4" opacity="0.22" />
                                    <line :x1="crop.x + '%'" :y1="(crop.y + crop.h / 3) + '%'"
                                        :x2="(crop.x + crop.w) + '%'" :y2="(crop.y + crop.h / 3) + '%'"
                                        stroke="white" stroke-width="0.4" opacity="0.22" />
                                    <line :x1="crop.x + '%'" :y1="(crop.y + crop.h * 2 / 3) + '%'"
                                        :x2="(crop.x + crop.w) + '%'" :y2="(crop.y + crop.h * 2 / 3) + '%'"
                                        stroke="white" stroke-width="0.4" opacity="0.22" />
                                </svg>
                            </div>
                            <div class="absolute cursor-move"
                                :style="'left:' + crop.x + '%;top:' + crop.y + '%;width:' + crop.w + '%;height:' + crop.h + '%'"
                                @mousedown.self="startDragCrop($event,'move')"
                                @touchstart.self.prevent="startDragCrop($event,'move')">
                                <div class="absolute -top-2.5 -left-2.5 w-5 h-5 bg-white rounded-sm shadow cursor-nwse-resize z-10"
                                    @mousedown.stop="startDragCrop($event,'tl')"
                                    @touchstart.stop.prevent="startDragCrop($event,'tl')"></div>
                                <div class="absolute -top-2.5 -right-2.5 w-5 h-5 bg-white rounded-sm shadow cursor-nesw-resize z-10"
                                    @mousedown.stop="startDragCrop($event,'tr')"
                                    @touchstart.stop.prevent="startDragCrop($event,'tr')"></div>
                                <div class="absolute -bottom-2.5 -left-2.5 w-5 h-5 bg-white rounded-sm shadow cursor-nesw-resize z-10"
                                    @mousedown.stop="startDragCrop($event,'bl')"
                                    @touchstart.stop.prevent="startDragCrop($event,'bl')"></div>
                                <div class="absolute -bottom-2.5 -right-2.5 w-5 h-5 bg-white rounded-sm shadow cursor-nwse-resize z-10"
                                    @mousedown.stop="startDragCrop($event,'br')"
                                    @touchstart.stop.prevent="startDragCrop($event,'br')"></div>
                                <div class="absolute cursor-ns-resize z-10 flex justify-center"
                                    style="top:-13px;left:25%;width:50%;height:13px"
                                    @mousedown.stop="startDragCrop($event,'t')"
                                    @touchstart.stop.prevent="startDragCrop($event,'t')">
                                    <div class="w-8 h-1.5 bg-white rounded-full mt-1.5 shadow"></div>
                                </div>
                                <div class="absolute cursor-ns-resize z-10 flex justify-center items-end"
                                    style="bottom:-13px;left:25%;width:50%;height:13px"
                                    @mousedown.stop="startDragCrop($event,'b')"
                                    @touchstart.stop.prevent="startDragCrop($event,'b')">
                                    <div class="w-8 h-1.5 bg-white rounded-full mb-1.5 shadow"></div>
                                </div>
                                <div class="absolute cursor-ew-resize z-10 flex items-center"
                                    style="left:-13px;top:25%;width:13px;height:50%"
                                    @mousedown.stop="startDragCrop($event,'l')"
                                    @touchstart.stop.prevent="startDragCrop($event,'l')">
                                    <div class="w-1.5 h-8 bg-white rounded-full ml-1.5 shadow"></div>
                                </div>
                                <div class="absolute cursor-ew-resize z-10 flex items-center justify-end"
                                    style="right:-13px;top:25%;width:13px;height:50%"
                                    @mousedown.stop="startDragCrop($event,'r')"
                                    @touchstart.stop.prevent="startDragCrop($event,'r')">
                                    <div class="w-1.5 h-8 bg-white rounded-full mr-1.5 shadow"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- BARRA INFERIOR (solo mobile) --}}
                <div class="md:hidden shrink-0 bg-black/70 border-t border-white/10">

                    {{-- Tabs horizontales --}}
                    <div class="flex border-b border-white/5">
                        <button type="button" @click="switchTab('persp')"
                            class="flex-1 flex flex-col items-center gap-0.5 py-2.5 text-[11px] font-medium transition"
                            :class="editorTab === 'persp' ? 'text-white border-b-2 border-indigo-500' : 'text-white/40'">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M21 3L3 9l7 3 3 9 8-18z" />
                            </svg>
                            Perspectiva
                        </button>
                        <button type="button" @click="switchTab('crop')"
                            class="flex-1 flex flex-col items-center gap-0.5 py-2.5 text-[11px] font-medium transition"
                            :class="editorTab === 'crop' ? 'text-white border-b-2 border-indigo-500' : 'text-white/40'">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <polyline points="6 2 6 6 2 6" />
                                <polyline points="18 2 18 6 22 6" />
                                <polyline points="6 22 6 18 2 18" />
                                <polyline points="18 22 18 18 22 18" />
                            </svg>
                            Recortar
                        </button>
                        <button type="button" @click="switchTab('adjust')"
                            class="flex-1 flex flex-col items-center gap-0.5 py-2.5 text-[11px] font-medium transition"
                            :class="editorTab === 'adjust' ? 'text-white border-b-2 border-indigo-500' : 'text-white/40'">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <line x1="4" y1="21" x2="4" y2="14" />
                                <line x1="4" y1="10" x2="4" y2="3" />
                                <line x1="12" y1="21" x2="12" y2="12" />
                                <line x1="12" y1="8" x2="12" y2="3" />
                                <line x1="20" y1="21" x2="20" y2="16" />
                                <line x1="20" y1="12" x2="20" y2="3" />
                                <line x1="1" y1="14" x2="7" y2="14" />
                                <line x1="9" y1="8" x2="15" y2="8" />
                                <line x1="17" y1="16" x2="23" y2="16" />
                            </svg>
                            Ajustes
                        </button>
                    </div>

                    {{-- Contenido compacto según tab --}}
                    <div class="px-4 py-3">

                        {{-- Perspectiva --}}
                        <div x-show="editorTab === 'persp'" class="flex items-center justify-between gap-3">
                            <p class="text-white/40 text-xs">Arrastrá las 4 esquinas al borde del documento.</p>
                            <button type="button"
                                @click="handles=[{x:0,y:0},{x:100,y:0},{x:100,y:100},{x:0,y:100}]; _warpDirty=true"
                                class="shrink-0 text-xs text-white/50 hover:text-white border border-white/10 hover:border-white/20 px-3 py-1.5 rounded-lg transition font-medium">
                                Resetear
                            </button>
                        </div>

                        {{-- Recortar --}}
                        <div x-show="editorTab === 'crop'" class="flex items-center justify-between gap-3">
                            <p class="text-white/40 text-xs">Arrastrá los bordes para ajustar el recorte.</p>
                            <button type="button" @click="crop={x:0,y:0,w:100,h:100}"
                                class="shrink-0 text-xs text-white/50 hover:text-white border border-white/10 hover:border-white/20 px-3 py-1.5 rounded-lg transition font-medium">
                                Resetear
                            </button>
                        </div>

                        {{-- Ajustes --}}
                        <div x-show="editorTab === 'adjust'" class="space-y-2.5">
                            <div class="flex items-center gap-3">
                                <span
                                    class="text-[10px] text-white/50 uppercase font-black tracking-widest w-16 shrink-0">Brillo</span>
                                <input type="range" x-model="adjBrightness" min="50" max="200"
                                    class="flex-1 h-1.5 accent-indigo-500 bg-white/10 rounded-full appearance-none cursor-pointer">
                                <span class="text-[10px] text-indigo-400 font-mono w-9 text-right shrink-0"
                                    x-text="adjBrightness+'%'"></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span
                                    class="text-[10px] text-white/50 uppercase font-black tracking-widest w-16 shrink-0">Contraste</span>
                                <input type="range" x-model="adjContrast" min="50" max="300"
                                    class="flex-1 h-1.5 accent-indigo-500 bg-white/10 rounded-full appearance-none cursor-pointer">
                                <span class="text-[10px] text-indigo-400 font-mono w-9 text-right shrink-0"
                                    x-text="adjContrast+'%'"></span>
                            </div>
                            <div class="flex justify-end pt-0.5">
                                <button type="button" @click="adjBrightness=100;adjContrast=100"
                                    class="text-xs text-white/40 hover:text-white border border-white/10 hover:border-white/20 px-3 py-1.5 rounded-lg transition font-medium">
                                    Reset
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </template>

    {{-- ════════════════════════════════════════════════
         FASE: VISTA PREVIA FINAL
    ════════════════════════════════════════════════ --}}
    <template x-if="phase === 'preview'">
        <div class="fixed inset-0 z-[9999] bg-black/95 flex flex-col backdrop-blur-2xl">
            <div class="relative w-full h-full bg-neutral-950 flex flex-col"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                <div class="px-5 py-3 border-b border-white/5 text-center shrink-0">
                    <h3 class="text-white font-semibold">Vista previa final</h3>
                    <p class="text-white/40 text-xs mt-0.5">Verificá que el documento se vea correctamente antes de
                        subir</p>
                </div>
                <div class="flex-1 bg-[#0d0d0d] flex items-center justify-center p-4 overflow-hidden min-h-0">
                    <img :src="finalURL" class="max-h-full max-w-full object-contain rounded shadow-2xl" />
                </div>
                <div class="px-6 py-5 flex items-center justify-between bg-neutral-950 border-t border-white/5">
                    <button type="button" @click="phase='editor'"
                        class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-neutral-400 hover:text-white border border-white/10 hover:border-white/20 text-sm transition">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <polyline points="15 18 9 12 15 6" />
                        </svg>
                        Editar
                    </button>
                    <button type="button" @click="uploadFinal()"
                        class="flex items-center gap-2 px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-500 transition shadow-lg active:scale-95 text-sm">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                        Subir Documento
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>
