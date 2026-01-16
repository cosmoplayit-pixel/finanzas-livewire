{{-- resources/views/components/ui/modal.blade.php --}}
@props([
    'title' => 'Modal',
    'show' => false,
    'maxWidth' => 'md:max-w-2xl',
    'onClose' => null, // método Livewire: closeFactura, closePago, etc.
])

@php
    $closeJs = $onClose ? "\$wire.{$onClose}()" : null;
@endphp

<div x-data="{
    open: @js($show),
    close() {
        this.open = false;
        @if ($closeJs) {!! $closeJs !!}; @endif
    }
}" x-effect="open = @js($show)" x-show="open" x-cloak
    class="fixed inset-0 z-50" @keydown.escape.window="close()">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 dark:bg-black/70" @click="close()"></div>

    {{-- Wrapper (click fuera del panel = cerrar) --}}
    <div class="relative h-full w-full flex items-end sm:items-center justify-center p-0 sm:p-4" @click.self="close()">
        {{-- Panel --}}
        <div @click.stop
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

                <button type="button" @click="close()"
                    class="inline-flex items-center justify-center size-9 rounded-md
                           text-gray-500 hover:text-gray-900 hover:bg-gray-200
                           dark:text-neutral-400 dark:hover:text-white dark:hover:bg-neutral-800
                           transition">
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
