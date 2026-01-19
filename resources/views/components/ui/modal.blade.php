{{-- resources/views/components/ui/modal.blade.php --}}
@props([
    'title' => 'Modal',
    'model' => null,
    'maxWidth' => 'md:max-w-2xl',
    'onClose' => null,

    // ✅ NUEVO
    'closeOnBackdrop' => true, // permitir o no cerrar al hacer click afuera
    'busy' => false, // si está true, bloquea cierre (útil mientras guarda Livewire)
])

@php
    $closeJs = $onClose ? "\$wire.{$onClose}()" : null;
@endphp

<div x-data="{
    open: @if ($model) @entangle($model).live @else false @endif,

    close() {
        if (@js($busy)) return; // no cerrar si está busy
        this.open = false;
        @if ($closeJs) {!! $closeJs !!}; @endif
    },

    setBodyLock(value) {
        // bloquea el scroll del documento (mejor en mobile)
        const el = document.documentElement;
        if (value) el.classList.add('overflow-hidden');
        else el.classList.remove('overflow-hidden');
    },
}" x-init="$watch('open', (v) => setBodyLock(v))" x-show="open" x-cloak class="fixed inset-0 z-50"
    @keydown.escape.window="close()">
    {{-- Backdrop (fade) --}}
    <div class="absolute inset-0 bg-black/50 dark:bg-black/70" x-show="open" x-transition.opacity></div>

    {{-- Wrapper --}}
    <div class="relative h-full w-full flex items-end sm:items-center justify-center p-0 sm:p-4"
        @mousedown="{{ $closeOnBackdrop ? 'close()' : '' }}">
        {{-- Panel (scale + slide) --}}
        <div @mousedown.stop @click.stop x-show="open" x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-120"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 sm:translate-y-0 sm:scale-95"
            class="w-full
                   h-[100dvh] sm:h-auto
                   sm:max-h-[90vh]
                   sm:max-w-xl {{ $maxWidth }}
                   bg-white dark:bg-neutral-900
                   text-gray-700 dark:text-neutral-200
                   border border-gray-200 dark:border-neutral-800
                   rounded-none sm:rounded-xl
                   overflow-hidden shadow-2xl">
            {{-- Header --}}
            <div
                class="sticky top-0 z-10 px-5 py-4 flex justify-between items-center
                       bg-gray-50 dark:bg-neutral-900
                       border-b border-gray-200 dark:border-neutral-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">
                    {{ $title }}
                </h2>

                <button type="button" @click="close()" :disabled="@js($busy)"
                    class="cursor-pointer inline-flex items-center justify-center size-9 rounded-md
                           text-gray-500 hover:text-gray-900 hover:bg-gray-200
                           dark:text-neutral-400 dark:hover:text-white dark:hover:bg-neutral-800
                           transition
                           disabled:opacity-50 disabled:cursor-not-allowed"
                    title="Cerrar">
                    ✕
                </button>
            </div>

            {{-- Body --}}
            <div
                class="p-5 space-y-4 overflow-y-auto
                       h-[calc(100dvh-64px-76px)] sm:h-auto
                       sm:max-h-[calc(90vh-64px-76px)]">
                {{ $slot }}
            </div>

            {{-- Footer --}}
            @isset($footer)
                <div
                    class="sticky bottom-0 px-5 py-4 flex justify-end gap-2
                           bg-gray-50 dark:bg-neutral-900
                           border-t border-gray-200 dark:border-neutral-800">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
