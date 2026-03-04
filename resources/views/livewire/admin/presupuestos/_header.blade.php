{{-- HEADER (RESPONSIVE) --}}
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100 flex items-center gap-2">
            <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                </path>
            </svg>
            Presupuestos y Rendiciones
        </h1>
        <p class="text-sm text-gray-500 mt-1 dark:text-neutral-400">
            Resumen general por agente (presupuesto / rendido / saldo por rendir). El detalle está en el Panel.
        </p>
    </div>

    <div class="flex gap-2">
        @can('agente_presupuestos.create')
            <button wire:click="openCreate" wire:loading.attr="disabled" wire:target="openCreate"
                class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span wire:loading.remove wire:target="openCreate">Nuevo Presupuesto</span>
                <span wire:loading wire:target="openCreate">Abriendo…</span>
            </button>
        @endcan
    </div>
</div>
