    {{-- ===================== HEADER ===================== --}}
    <div
        class="mb-3 sm:mb-6 flex flex-row items-center justify-between gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h1
                class="text-lg sm:text-2xl font-bold text-gray-900 dark:text-neutral-100 flex items-center gap-2 truncate">
                <svg class="h-5 w-5 sm:h-6 sm:w-6 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="hidden sm:inline">Inventario de Recursos</span>
                <span class="sm:hidden">Inventario</span>
            </h1>
            <p class="hidden sm:block text-sm text-gray-500 mt-1 dark:text-neutral-400">
                Administración y control de herramientas, activos fijos y materiales en el almacén.
            </p>
        </div>

        <div class="flex gap-2 shrink-0">
            @can('herramientas.export')
                <button wire:click="export" wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center gap-1.5 px-2 py-1.5 sm:px-3 sm:py-2 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200 hover:bg-gray-50 dark:hover:bg-neutral-700 text-sm font-medium rounded-lg shadow-sm transition-colors cursor-pointer disabled:opacity-50">
                    <svg wire:loading.remove wire:target="export" class="w-4 h-4 text-emerald-600 shrink-0" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                    </svg>
                    <svg wire:loading wire:target="export" class="w-4 h-4 animate-spin shrink-0" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span class="hidden sm:inline" wire:loading.remove wire:target="export">Excel</span>
                </button>
            @endcan

            @can('herramientas.historial_bajas')
                <button wire:click="openBajasHistorial" wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center gap-1.5 px-2 py-1.5 sm:px-3 sm:py-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 text-sm font-medium rounded-lg shadow-sm transition-colors cursor-pointer disabled:opacity-50">
                    <svg wire:loading.remove wire:target="openBajasHistorial" class="w-4 h-4 shrink-0" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg wire:loading wire:target="openBajasHistorial" class="w-4 h-4 animate-spin shrink-0" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span class="hidden sm:inline">Bajas</span>
                </button>
            @endcan

            @can('herramientas.create')
                @php
                    $anyModalOpen =
                        $openModal ||
                        $detailModal ||
                        $openAddStockModal ||
                        $openBajaStockModal ||
                        ($bajasModal ?? false);
                @endphp
                <button wire:click="openCreate" wire:loading.attr="disabled" @disabled($anyModalOpen)
                    class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 sm:px-4 sm:py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors cursor-pointer disabled:opacity-50 disabled:bg-gray-400 disabled:cursor-not-allowed {{ $anyModalOpen ? 'grayscale opacity-50' : '' }}">
                    <svg wire:loading.remove wire:target="openCreate" class="w-4 h-4 shrink-0" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <svg wire:loading wire:target="openCreate" class="hidden sm:block w-4 h-4 animate-spin shrink-0"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span class="hidden sm:inline" wire:loading.remove wire:target="openCreate">Nuevo Recurso</span>
                    <span class="sm:hidden" wire:loading.remove wire:target="openCreate">Nuevo</span>
                    <span class="hidden sm:inline" wire:loading wire:target="openCreate">Abriendo...</span>
                </button>
            @endcan
        </div>
    </div>
