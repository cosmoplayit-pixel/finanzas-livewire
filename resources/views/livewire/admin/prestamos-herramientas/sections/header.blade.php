<section class="section">
    @section('title', 'Salidas y Retornos')

    {{-- HEADER (RESPONSIVE) --}}
    <div class="mb-3 sm:mb-6 flex flex-row items-center justify-between gap-2">
        <div class="min-w-0">
            <h1
                class="text-lg sm:text-2xl font-bold text-gray-900 dark:text-neutral-100 flex items-center gap-2 truncate">
                <div
                    class="p-1.5 sm:p-2 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 shrink-0">
                    <svg class="size-4 sm:size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <span class="hidden sm:inline">Salidas y Retornos</span>
                <span class="sm:hidden">Préstamos</span>
            </h1>
            <p class="hidden sm:block text-sm text-gray-500 dark:text-neutral-400 mt-1">Gestión de equipos en obra y
                registro de retornos.</p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            @can('prestamos.create')
                <button wire:click="openCreate" wire:loading.attr="disabled" @disabled($anyModalOpen)
                    class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 sm:px-5 sm:py-2.5 rounded-xl bg-black text-white hover:bg-neutral-800 transition shadow-sm font-bold text-sm cursor-pointer disabled:bg-gray-400 disabled:opacity-50 {{ $anyModalOpen ? 'grayscale' : '' }}">
                    <svg wire:loading.remove wire:target="openCreate" class="w-4 h-4 shrink-0" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <svg wire:loading wire:target="openCreate" class="w-4 h-4 animate-spin shrink-0" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span class="hidden sm:inline" wire:loading.remove wire:target="openCreate">Nueva Salida</span>
                    <span class="sm:hidden" wire:loading.remove wire:target="openCreate">Nueva</span>
                </button>
            @endcan
        </div>
    </div>

    {{-- ALERTA VENCIDOS --}}
    @if ($countVencidos > 0)
        <div
            class="mb-4 sm:mb-6 p-3 sm:p-4 rounded-xl bg-red-50 border border-red-100 dark:bg-red-500/10 dark:border-red-500/20 flex items-center gap-3 sm:gap-4">
            <div
                class="size-8 sm:size-10 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center text-red-600 shrink-0">
                <svg class="size-4 sm:size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-red-800 dark:text-red-400">Hay {{ $countVencidos }} préstamo(s) fuera
                    de fecha</p>
                <p class="text-xs text-red-600 dark:text-red-500/80 italic">Contactar a los responsables para coordinar
                    el retorno.</p>
            </div>
        </div>
    @endif
