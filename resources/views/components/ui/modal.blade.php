@props([
    'title' => 'Modal',
    'model' => null,
    'maxWidth' => 'md:max-w-2xl',
    'maxHeight' => 'sm:max-h-[92vh]',
    'onClose' => null,
    'closeOnBackdrop' => true,
    'busy' => false,
])

@php
    $closeJs = $onClose ? "\$wire.{$onClose}()" : null;
@endphp

<div x-data="{
    open: @if ($model) @entangle($model).live @else false @endif,

    /* ── Drag state ── */
    dragging: false,
    dragged: false,
    /* true once the user has moved the panel at least once */
    dragOffsetX: 0,
    dragOffsetY: 0,

    close() {
        if (@js($busy)) return;
        this.open = false;
        this.dragged = false;
        @if ($closeJs) {!! $closeJs !!}; @endif
    },

    setBodyLock(v) {
        const el = document.documentElement;
        if (v) el.classList.add('overflow-hidden');
        else el.classList.remove('overflow-hidden');
    },

    /* Called on mousedown on the header */
    startDrag(event, panelEl, wrapperEl) {
        /* Ignore clicks on the close button */
        if (event.target.closest('button')) return;

        const rect = panelEl.getBoundingClientRect();

        /* First drag: switch panel to absolute so it can travel freely */
        if (!this.dragged) {
            panelEl.style.position = 'absolute';
            panelEl.style.left = rect.left + 'px';
            panelEl.style.top = rect.top + 'px';
            panelEl.style.margin = '0';
            /* Tell the wrapper not to flex-center any more */
            wrapperEl.style.alignItems = 'flex-start';
            wrapperEl.style.justifyContent = 'flex-start';
        }

        this.dragging = true;
        this.dragged = true;
        this.dragOffsetX = event.clientX - panelEl.getBoundingClientRect().left;
        this.dragOffsetY = event.clientY - panelEl.getBoundingClientRect().top;

        const onMove = (e) => {
            if (!this.dragging) return;
            const vw = window.innerWidth;
            const vh = window.innerHeight;
            let newLeft = e.clientX - this.dragOffsetX;
            let newTop = e.clientY - this.dragOffsetY;
            /* Keep panel inside viewport */
            newLeft = Math.max(0, Math.min(newLeft, vw - panelEl.offsetWidth));
            newTop = Math.max(0, Math.min(newTop, vh - panelEl.offsetHeight));
            panelEl.style.left = newLeft + 'px';
            panelEl.style.top = newTop + 'px';
        };

        const onUp = () => {
            this.dragging = false;
            window.removeEventListener('mousemove', onMove);
            window.removeEventListener('mouseup', onUp);
        };

        window.addEventListener('mousemove', onMove);
        window.addEventListener('mouseup', onUp);
    },
}" x-init="$watch('open', (v) => { setBodyLock(v); if (!v) { dragged = false; } })" x-show="open" x-cloak class="fixed inset-0 z-50"
    @keydown.escape.window="close()">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50 dark:bg-black/70" x-show="open" x-transition.opacity></div>

    {{-- Wrapper --}}
    <div class="fixed inset-0 flex items-stretch sm:items-center justify-center p-0 sm:p-4" x-ref="wrapper"
        @mousedown="{{ $closeOnBackdrop ? 'close()' : '' }}">
        {{-- Panel --}}
        <div @mousedown.stop @click.stop x-show="open" x-ref="panel"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-120"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 sm:translate-y-0 sm:scale-95"
            class="w-full
                h-[100dvh] sm:h-auto
                {{ $maxHeight }}
                sm:max-w-xl {{ $maxWidth }}
                bg-white dark:bg-neutral-900
                text-gray-700 dark:text-neutral-200
                border border-gray-200 dark:border-neutral-800
                rounded-none sm:rounded-xl
                overflow-hidden shadow-2xl
                flex flex-col">

            {{-- Header (drag handle) --}}
            <div x-ref="header" @mousedown="startDrag($event, $refs.panel, $refs.wrapper)"
                :class="dragging ? 'cursor-grabbing' : 'cursor-grab sm:cursor-grab'"
                class="shrink-0 select-none px-4 sm:px-5 py-3 sm:py-4
                       flex justify-between items-center
                       bg-gray-50 dark:bg-neutral-900
                       border-b border-gray-200 dark:border-neutral-800
                       transition-colors duration-150"
                :style="dragging ? 'background:rgb(59 130 246 / .12)' : ''">

                <div class="flex items-center gap-2">
                    {{-- Move icon: changes when dragging --}}
                    <span class="text-gray-400 dark:text-neutral-500 transition-all duration-150 text-sm leading-none"
                        :title="dragging ? 'Moviendo…' : 'Arrastrar para mover'">
                        <svg x-show="!dragging" xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 9l4-4 4 4M8 15l4 4 4-4M4 12h16" />
                        </svg>
                        <svg x-show="dragging" xmlns="http://www.w3.org/2000/svg" class="size-4 text-blue-500"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M5 9l-3 3 3 3M9 5l3-3 3 3M15 19l-3 3-3-3M19 9l3 3-3 3M12 3v18M3 12h18" />
                        </svg>
                    </span>

                    <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-neutral-100">
                        {{ $title }}
                    </h2>
                </div>

                <button type="button" @click.stop="close()" :disabled="@js($busy)" @mousedown.stop
                    class="cursor-pointer inline-flex items-center justify-center size-9 rounded-md
                           text-gray-500 hover:text-gray-900 hover:bg-gray-200
                           dark:text-neutral-400 dark:hover:text-white dark:hover:bg-neutral-800
                           transition disabled:opacity-50 disabled:cursor-not-allowed"
                    title="Cerrar">
                    ✕
                </button>
            </div>

            {{-- Body: ÚNICO SCROLL DEL MODAL --}}
            <div class="flex-1 min-h-0 overflow-y-auto p-4 sm:p-5">
                {{ $slot }}
            </div>

            {{-- Footer --}}
            @isset($footer)
                <div
                    class="shrink-0 text-right px-4 sm:px-5 py-3 sm:py-4
                           bg-gray-50 dark:bg-neutral-900
                           border-t border-gray-200 dark:border-neutral-800">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
