@props([
    'title' => 'Modal',
    'model' => null,
    'maxWidth' => 'md:max-w-2xl',
    'onClose' => null,
    'closeOnBackdrop' => true,
    'busy' => false,
])

@php
    $closeJs = $onClose ? "\$wire.{$onClose}()" : null;
@endphp

<div x-data="{
    open: @if ($model) @entangle($model).live @else false @endif,

    close() {
        if (@js($busy)) return;
        this.open = false;
        @if ($closeJs) {!! $closeJs !!}; @endif
    },

    setBodyLock(v) {
        const el = document.documentElement;
        if (v) el.classList.add('overflow-hidden');
        else el.classList.remove('overflow-hidden');
    },
}" x-init="$watch('open', (v) => setBodyLock(v))" x-show="open" x-cloak class="fixed inset-0 z-50"
    @keydown.escape.window="close()">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50 dark:bg-black/70" x-show="open" x-transition.opacity></div>

    {{-- Wrapper --}}
    <div class="fixed inset-0 flex items-stretch sm:items-center justify-center p-0 sm:p-4"
        @mousedown="{{ $closeOnBackdrop ? 'close()' : '' }}">
        {{-- Panel --}}
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
                   overflow-hidden shadow-2xl
                   flex flex-col">
            {{-- Header --}}
            <div
                class="shrink-0 px-4 sm:px-5 py-3 sm:py-4
                       flex justify-between items-center
                       bg-gray-50 dark:bg-neutral-900
                       border-b border-gray-200 dark:border-neutral-800">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-neutral-100">
                    {{ $title }}
                </h2>

                <button type="button" @click="close()" :disabled="@js($busy)"
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
