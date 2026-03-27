@props([
    'open' => false,
    'url' => null,
    'onClose' => 'closeFoto',
])

@if ($open)
    <div x-data="{
        zoom: 1,
        hovering: false,
        setOrigin(e) {
            const rect = e.target.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            e.target.style.transformOrigin = `${x}% ${y}%`;
        },
        wheelZoom(e) {
            const step = 0.2;
            const next = this.zoom + (e.deltaY < 0 ? step : -step);
            this.zoom = Math.max(1, Math.min(5, next));
            this.hovering = this.zoom > 1;
            this.setOrigin(e);
        }
    }" @keydown.escape.window="$wire.{{ $onClose }}()"
        class="fixed inset-0 z-[100] flex items-center justify-center p-2 sm:p-5">

        {{-- Backdrop mejorado --}}
        <div class="absolute inset-0 bg-black/90 backdrop-blur-sm transition-opacity duration-300"
            wire:click="{{ $onClose }}"></div>

        {{-- Botón de cerrar elegante --}}
        <button type="button" wire:click="{{ $onClose }}"
            class="absolute top-4 right-4 sm:top-8 sm:right-8 z-[110] p-3 text-white/40 hover:text-white transition-all hover:scale-110 group">
            <svg class="w-8 h-8 sm:w-10 sm:h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span class="sr-only">Cerrar</span>
        </button>

        {{-- Contenedor de Imagen Central --}}
        <div class="relative z-[105] max-w-full max-h-full flex items-center justify-center overflow-hidden">
            @if ($url)
                <img src="{{ $url }}" alt="Visor de comprobante"
                    class="max-w-full max-h-[92vh] rounded shadow-2xl transition-transform duration-200 ease-out will-change-transform cursor-zoom-in"
                    :style="hovering ? `transform: scale(${zoom});` : 'transform: scale(1); transform-origin: center center;'"
                    @mouseenter="hovering = true" @mouseleave="hovering = false; zoom = 1"
                    @mousemove="setOrigin($event)" @wheel.prevent="wheelZoom($event)" draggable="false" />
            @else
                <div class="text-white/50 font-medium">No se encontró la imagen.</div>
            @endif
        </div>

        {{-- Indicador de Zoom sutil --}}
        <div x-show="zoom > 1" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            class="absolute bottom-10 left-1/2 -translate-x-1/2 z-[110] px-4 py-2 rounded-full bg-white/10 text-white/90 text-xs font-semibold backdrop-blur-xl border border-white/20 select-none pointer-events-none">
            Zoom: <span x-text="zoom.toFixed(1) + 'x'"></span>
        </div>

        {{-- Ayuda dinámica --}}
        <div x-show="!hovering" x-transition
            class="absolute bottom-10 left-1/2 -translate-x-1/2 z-[102] text-white/30 text-[10px] uppercase tracking-widest font-medium pointer-events-none select-none">
            Desplaza para zoom • click fuera para cerrar
        </div>

    </div>
@endif
