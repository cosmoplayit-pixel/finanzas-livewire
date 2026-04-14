{{-- ===================== HEADER (Plantilla estilo Agente Presupuesto) ===================== --}}
<div
    class="mb-3 sm:mb-6 flex flex-row items-center justify-between gap-2 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0">
        <h1 class="text-lg sm:text-2xl font-bold text-gray-900 dark:text-neutral-100 flex items-center gap-2 truncate">
            <svg class="h-5 w-5 sm:h-6 sm:w-6 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6">
                </path>
            </svg>
            Creditos e Inversiones
        </h1>
        <p class="hidden sm:block text-sm text-gray-500 mt-1 dark:text-neutral-400">
            Gestión de inversiones (creación, movimientos de capital y pago de intereses) con historial por registro.
        </p>
    </div>

    <div class="flex gap-2 shrink-0">
        @can('inversiones.create')
            <button wire:click="$dispatch('openCreateInversion')" wire:loading.attr="disabled" wire:target="openCreate"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-4 sm:py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Nueva Inversión</span>
            </button>
        @endcan
    </div>
</div>
