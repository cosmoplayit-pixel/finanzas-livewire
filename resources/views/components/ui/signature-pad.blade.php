@props(['model', 'label' => 'Firma Digital'])

<div x-data="signaturePad('{{ $model }}')" class="space-y-2">

    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">
        {{ $label }}
    </label>

    {{-- wire:ignore evita que Livewire haga morph del canvas al re-renderizar --}}
    <div wire:ignore
        class="relative w-full rounded-lg border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900"
        style="height: 200px;">

        <canvas x-ref="canvas" class="w-full h-full block touch-none" style="cursor: crosshair;"></canvas>

        {{-- Botón limpiar --}}
        <button type="button" @click="clearPad()"
            class="absolute top-2 right-2 z-10 p-1.5 bg-white/80 dark:bg-neutral-800/80 hover:bg-white dark:hover:bg-neutral-800 rounded-md border border-gray-200 dark:border-neutral-700 text-gray-500 hover:text-red-600 shadow-sm transition-colors"
            title="Limpiar firma">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </button>

        {{-- Placeholder (fuera del wire:ignore porque necesita reactividad) --}}
        <div x-show="!hasSigned"
            class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none select-none gap-1">
            <svg class="w-6 h-6 text-gray-300 dark:text-neutral-600" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>
            <span class="text-[10px] uppercase tracking-widest font-semibold text-gray-300 dark:text-neutral-600">Firmar
                aquí</span>
        </div>
    </div>

    @error($model)
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror

</div>

@once
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

    <script>
        function signaturePad(modelName) {
            return {
                modelName,
                pad: null,
                hasSigned: false,
                _ro: null,
                _lastW: 0,
                _lastH: 0,

                init() {
                    const canvas = this.$refs.canvas;

                    const boot = () => {
                        // Redimensionar el canvas a las dimensiones reales del contenedor
                        this._doResize(canvas);

                        this.pad = new SignaturePad(canvas, {
                            penColor: '#1a202c',
                            minWidth: 1,
                            maxWidth: 3,
                            throttle: 0,
                        });

                        // Guardamos cuando el usuario termina de trazar
                        this.pad.addEventListener('endStroke', () => {
                            if (!this.pad.isEmpty()) {
                                this.hasSigned = true;
                                this.$wire.set(this.modelName, this.pad.toDataURL('image/png'));
                            }
                        });

                        // ResizeObserver solo actúa si las dimensiones cambian realmente
                        // (evita dispararse por el re-render de Livewire)
                        this._ro = new ResizeObserver(() => {
                            const w = canvas.parentElement.offsetWidth;
                            const h = canvas.parentElement.offsetHeight;
                            if (w !== this._lastW || h !== this._lastH) {
                                this._doResize(canvas);
                                if (this.pad) this.pad.clear();
                                if (this.hasSigned) {
                                    this.hasSigned = false;
                                    this.$wire.set(this.modelName, null);
                                }
                            }
                        });
                        this._ro.observe(canvas.parentElement);
                    };

                    if (typeof SignaturePad !== 'undefined') {
                        boot();
                    } else {
                        const check = setInterval(() => {
                            if (typeof SignaturePad !== 'undefined') {
                                clearInterval(check);
                                boot();
                            }
                        }, 50);
                    }

                },

                destroy() {
                    if (this._ro) this._ro.disconnect();
                    if (this.pad) this.pad.off();
                },

                _doResize(canvas) {
                    const ratio = window.devicePixelRatio || 1;
                    const parent = canvas.parentElement;
                    const w = parent.offsetWidth;
                    const h = parent.offsetHeight;

                    if (!w || !h) return;

                    this._lastW = w;
                    this._lastH = h;

                    canvas.width = w * ratio;
                    canvas.height = h * ratio;
                    canvas.getContext('2d').scale(ratio, ratio);
                },

                clearPad() {
                    if (this.pad) {
                        this.pad.clear();
                    }
                    this.hasSigned = false;
                    this.$wire.set(this.modelName, null);
                },
            };
        }
    </script>
@endonce
