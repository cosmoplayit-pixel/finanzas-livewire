<section class="section">
    @section('title', 'Salidas y Retornos')

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100 flex items-center gap-3">
                <div class="p-2 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                Salidas y Retornos
            </h1>
            <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">Gestión de equipos en obra y registro de
                retornos.</p>
        </div>
        <div class="flex items-center gap-3">
            @can('prestamos.create')
                @php
                    $anyModalOpen = $openModalPrestamo || $openModalDevolucion || $openModalBaja || $openModalVer;
                @endphp
                <button wire:click="openCreate" wire:loading.attr="disabled" @disabled($anyModalOpen)
                    class="flex-1 md:flex-none cursor-pointer inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-black text-white hover:bg-neutral-800 transition shadow-sm font-bold text-sm min-h-[46px] disabled:bg-gray-400 disabled:opacity-50 {{ $anyModalOpen ? 'grayscale' : '' }}">
                    <svg wire:loading.remove wire:target="openCreate" class="w-4 h-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <svg wire:loading wire:target="openCreate" class="w-4 h-4 animate-spin" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span>Nueva Salida</span>
                </button>
            @endcan
        </div>
    </div>

    {{-- ALERTA VENCIDOS --}}
    @if ($countVencidos > 0)
        <div
            class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 dark:bg-red-500/10 dark:border-red-500/20 flex items-center gap-4">
            <div
                class="size-10 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center text-red-600 shrink-0">
                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
